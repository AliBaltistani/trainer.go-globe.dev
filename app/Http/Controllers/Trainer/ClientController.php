<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerSubscription;
use App\Models\ClientWeightLog;
use App\Models\ClientActivityLog;
use App\Models\ClientProgress;
use App\Models\Goal;
use App\Models\Program;
use App\Models\NutritionPlan;
use App\Models\FoodDiary;
use App\Models\ClientNutritionTarget;
use Carbon\Carbon;
use App\Mail\ClientInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * Trainer Client Controller
 * 
 * Handles client management for trainers in the web panel
 * 
 * @package     GoGlobe
 * @subpackage  Controllers\Trainer
 */
class ClientController extends Controller
{
    /**
     * Display a listing of the trainer's clients.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $trainer = Auth::user();
        $search = $request->get('search');
        $status = $request->get('status', 'all');

        $query = User::whereHas('subscriptionsAsClient', function($q) use ($trainer) {
            $q->where('trainer_id', $trainer->id);
        });

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status !== 'all') {
            if ($status === 'active') {
                $query->whereHas('subscriptionsAsClient', function($q) use ($trainer) {
                    $q->where('trainer_id', $trainer->id)
                      ->where('status', 'active');
                });
            } elseif ($status === 'inactive') {
                $query->whereHas('subscriptionsAsClient', function($q) use ($trainer) {
                    $q->where('trainer_id', $trainer->id)
                      ->where('status', '!=', 'active');
                });
            }
        }

        $clients = $query->latest()->paginate(12);

        return view('trainer.clients.index', compact('clients', 'search', 'status'));
    }

    /**
     * Show the form for creating a new client.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('trainer.clients.create');
    }

    /**
     * Store a newly created client in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'fitness_level' => 'nullable|string',
            'fitness_goals' => 'nullable|array',
            'health_considerations' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Create User
            $password = Str::random(12);
            $client = User::create([
                'name' => $request->first_name . ' ' . $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($password),
                'role' => 'client',
                'phone' => $request->phone,
                'email_verified_at' => now(),
            ]);

            // Create UserHealthProfile
            // Assuming UserHealthProfile model exists based on API controller
            // If not, we should verify. But API controller uses $client->healthProfile()->create(...)
            // So the relationship and model must exist.
            
            // Check if healthProfile relationship exists on User model, or use direct model creation if needed.
            // Using the same approach as API controller:
            if (method_exists($client, 'healthProfile')) {
                $client->healthProfile()->create([
                    'fitness_level' => $request->fitness_level,
                    'chronic_conditions' => $request->health_considerations ? [$request->health_considerations] : [],
                    'allergies' => []
                ]);
            }

            // Create fitness goals
            if ($request->filled('fitness_goals')) {
                $goals = $request->fitness_goals;
                if (is_array($goals)) {
                    foreach ($goals as $goalName) {
                         // Using goals relationship as seen in API controller
                         if (method_exists($client, 'goals')) {
                            $client->goals()->create([
                                'name' => $goalName,
                                'status' => 1
                            ]);
                         }
                    }
                }
            }

            // Create Subscription
            TrainerSubscription::create([
                'trainer_id' => Auth::id(),
                'client_id' => $client->id,
                'status' => 'active',
                'start_date' => now(),
                'subscribed_at' => now()
            ]);

            DB::commit();

            // Send Invitation Email
            try {
                Mail::to($client->email)->send(new ClientInvitation($client, Auth::user(), $password));
            } catch (\Exception $e) {
                Log::error('Failed to send invitation email: ' . $e->getMessage());
                return redirect()->route('trainer.clients.index')->with('warning', 'Client created but failed to send invitation email.');
            }

            return redirect()->route('trainer.clients.index')->with('success', 'Client added successfully and invitation sent.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add client: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to add client. Please try again.');
        }
    }

    /**
     * Display the specified client.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $trainer = Auth::user();
        
        if (!$trainer->hasActiveClient($id)) {
             return redirect()->route('trainer.clients.index')->with('error', 'Unauthorized access to client.');
        }

        $client = User::with(['goals', 'videoProgress', 'healthProfile', 'clientNotes'])->findOrFail($id);
        
        // Prepare Overview Data
        $workoutsCompleted = ClientProgress::where('client_id', $id)
            ->where('status', 'completed')
            ->distinct('completed_at')
            ->count();
            
        $totalSessions = ClientProgress::where('client_id', $id)
            ->distinct(DB::raw('DATE(completed_at)'))
            ->count('completed_at');
            
        // Weight Logs for Chart
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $weightLogs = ClientWeightLog::where('user_id', $id)
            ->where('logged_at', '>=', $threeMonthsAgo)
            ->orderBy('logged_at', 'asc')
            ->get();
            
        $currentWeight = $weightLogs->last()?->weight ?? 0;
        $startWeight = $weightLogs->first()?->weight ?? 0;
        $weightChange = $currentWeight - $startWeight;
        
        // Health Profile
        $healthProfile = $client->healthProfile;
        
        // Latest Note
        $latestNote = $client->clientNotes()->latest()->first();
        
        // All Weight Logs for table
        $allWeightLogs = ClientWeightLog::where('user_id', $id)
            ->orderBy('logged_at', 'desc')
            ->paginate(10);
            
        // All Notes
        $allNotes = $client->clientNotes()->with('trainer')->latest()->paginate(5, ['*'], 'notes_page');

        // Client Programs
        $clientPrograms = Program::where('client_id', $id)
            ->where('trainer_id', $trainer->id)
            ->latest()
            ->get();

        // Available Program Templates (for assignment)
        $programTemplates = Program::where('trainer_id', $trainer->id)
            ->whereNull('client_id')
            ->latest()
            ->get();

        return view('trainer.clients.show', compact(
            'client', 
            'workoutsCompleted', 
            'totalSessions', 
            'weightLogs', 
            'currentWeight', 
            'startWeight', 
            'weightChange',
            'healthProfile',
            'latestNote',
            'allWeightLogs',
            'allNotes',
            'clientPrograms',
            'programTemplates'
        ));
    }

    public function storeWeight(Request $request, $id)
    {
        $trainer = Auth::user();
        if (!$trainer->hasActiveClient($id)) {
            abort(403);
        }

        $request->validate([
            'weight' => 'required|numeric',
            'unit' => 'required|in:lbs,kg',
            'logged_at' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        ClientWeightLog::create([
            'user_id' => $id,
            'weight' => $request->weight,
            'unit' => $request->unit,
            'logged_at' => $request->logged_at,
            'notes' => $request->notes
        ]);

        return back()->with('success', 'Weight logged successfully.');
    }

    public function storeNote(Request $request, $id)
    {
        $trainer = Auth::user();
        if (!$trainer->hasActiveClient($id)) {
            abort(403);
        }

        $request->validate([
            'note' => 'required|string'
        ]);

        $client = User::findOrFail($id);
        
        // Ensure relationship exists or create manually if needed, 
        // but based on API controller usage, clientNotes() should be available on User model.
        // If not, we might need to add it or use DB::table.
        // Assuming User model has clientNotes() relationship.
        $client->clientNotes()->create([
            'trainer_id' => $trainer->id,
            'note' => $request->note
        ]);

        return back()->with('success', 'Note added successfully.');
    }

    public function updateHealthProfile(Request $request, $id)
    {
        $trainer = Auth::user();
        if (!$trainer->hasActiveClient($id)) {
            abort(403);
        }

        $client = User::findOrFail($id);
        
        $data = [
            'fitness_level' => $request->fitness_level,
            // Assuming array input for these or comma separated string processing
            'chronic_conditions' => $request->chronic_conditions ?? [],
            'allergies' => $request->allergies ?? [],
        ];
        
        if ($client->healthProfile) {
            $client->healthProfile->update($data);
        } else {
            $client->healthProfile()->create($data);
        }

        return back()->with('success', 'Health profile updated.');
    }

    /**
     * Display client nutrition progress and food diary
     * 
     * @param int $id Client ID
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function nutritionProgress($id, Request $request)
    {
        $trainer = Auth::user();
        
        // Verify trainer has access to this client
        if (!$trainer->hasActiveClient($id)) {
            abort(403, 'Access denied');
        }
        
        $client = User::findOrFail($id);
        
        // Get date range from request (default to last 7 days)
        $startDate = $request->get('start_date', Carbon::now()->subDays(7)->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        
        try {
            $startDateCarbon = Carbon::parse($startDate)->startOfDay();
            $endDateCarbon = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            $startDateCarbon = Carbon::now()->subDays(7)->startOfDay();
            $endDateCarbon = Carbon::now()->endOfDay();
        }
        
        // Get active nutrition plan
        $plan = NutritionPlan::where('client_id', $id)
            ->where('trainer_id', $trainer->id)
            ->where('status', 'active')
            ->with(['recommendations'])
            ->first();
        
        // Get trainer recommendations
        $trainerRecommendations = $plan ? $plan->recommendations : null;
        
        // Get client's own targets
        $clientTargets = ClientNutritionTarget::where('client_id', $id)->first();
        
        // Get food diary entries
        $foodDiaryEntries = FoodDiary::where('client_id', $id)
            ->whereBetween('logged_at', [$startDateCarbon, $endDateCarbon])
            ->orderBy('logged_at', 'desc')
            ->get();
        
        // Group entries by date
        $entriesByDate = $foodDiaryEntries->groupBy(function($entry) {
            return Carbon::parse($entry->logged_at)->format('Y-m-d');
        });
        
        // Calculate daily summaries
        $dailySummaries = [];
        foreach ($entriesByDate as $date => $entries) {
            $dailySummaries[$date] = [
                'date' => Carbon::parse($date)->format('M d, Y'),
                'total_calories' => $entries->sum('calories'),
                'total_protein' => $entries->sum('protein'),
                'total_carbs' => $entries->sum('carbs'),
                'total_fats' => $entries->sum('fats'),
                'entry_count' => $entries->count(),
                'entries' => $entries
            ];
        }
        
        // Calculate overall statistics
        $stats = [
            'total_entries' => $foodDiaryEntries->count(),
            'avg_daily_calories' => $dailySummaries ? round(collect($dailySummaries)->avg('total_calories')) : 0,
            'avg_daily_protein' => $dailySummaries ? round(collect($dailySummaries)->avg('total_protein'), 1) : 0,
            'avg_daily_carbs' => $dailySummaries ? round(collect($dailySummaries)->avg('total_carbs'), 1) : 0,
            'avg_daily_fats' => $dailySummaries ? round(collect($dailySummaries)->avg('total_fats'), 1) : 0,
        ];
        
        return view('trainer.clients.nutrition-progress', compact(
            'client',
            'plan',
            'trainerRecommendations',
            'clientTargets',
            'foodDiaryEntries',
            'entriesByDate',
            'dailySummaries',
            'stats',
            'startDate',
            'endDate'
        ));
    }
}
