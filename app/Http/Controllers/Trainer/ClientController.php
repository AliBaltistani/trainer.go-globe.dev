<?php

namespace App\Http\Controllers\Trainer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerSubscription;
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

        $client = User::with(['goals', 'videoProgress'])->findOrFail($id);

        return view('trainer.clients.show', compact('client'));
    }
}
