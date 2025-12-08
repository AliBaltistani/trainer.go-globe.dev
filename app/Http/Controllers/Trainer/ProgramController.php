<?php

namespace App\Http\Controllers\Trainer;

use App\Models\Program;
use App\Models\ClientProgress;
use App\Models\Day;
use App\Support\UnitConverter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    /**
     * Trainer can only view and manage their own programs
     */
    public function __construct()
    {
        $this->middleware('trainer');
    }

    /**
     * Display a listing of trainer's programs
     */
    public function index(Request $request)
    {
        try {
            // Handle DataTable AJAX request
            if ($request->ajax() || $request->has('draw')) {
                return $this->getDataTableData($request);
            }

            // Return view for web request
            return view('trainer.programs.index');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in ProgramController@index: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while loading programs.');
        }
    }

    /**
     * Get DataTable data for programs listing
     */
    private function getDataTableData(Request $request): JsonResponse
    {
        try {
            $trainerId = Auth::id();
            $draw = $request->get('draw');
            $start = $request->get('start', 0);
            $length = $request->get('length', 10);
            $search = $request->get('search')['value'] ?? '';

            $query = Program::where('trainer_id', $trainerId)->with(['weeks', 'client', 'trainer']);

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($clientQuery) use ($search) {
                          $clientQuery->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $totalRecords = Program::where('trainer_id', $trainerId)->count();
            $filteredRecords = $query->count();

            $programs = $query->skip($start)->take($length)->orderBy('created_at', 'desc')->get();

            $data = $programs->map(function ($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'trainer' => $program->trainer ? $program->trainer->name : '',
                    'client' => $program->client ? $program->client->name : '<span class="badge bg-secondary">Template</span>',
                    'duration' => $program->duration . ' weeks',
                    'weeks_count' => $program->weeks->count(),
                    'status' => $program->is_active ? 
                        '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Active</span>' : 
                        '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Draft</span>',
                    'created_at' => $program->created_at->format('M d, Y'),
                    'actions' => $this->getActionButtons($program)
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in ProgramController@getDataTableData: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load programs data'], 500);
        }
    }

    /**
     * Generate action buttons for DataTable
     */
    private function getActionButtons(Program $program): string
    {
        $buttons = '<div class="btn-group" role="group">';
        
        $buttons .= '<a href="' . route('trainer.programs.show', $program->id) . '" 
                        class="btn btn-sm btn-outline-primary me-1" title="View Program">
                        <i class="ri-eye-line"></i>
                    </a>';
        
        $buttons .= '<a href="' . route('trainer.programs.edit', $program->id) . '" 
                        class="btn btn-sm btn-outline-success me-1" title="Edit Program">
                        <i class="ri-edit-2-line"></i>
                    </a>';

        $buttons .= '<button type="button" class="btn btn-sm btn-outline-secondary me-1 program-pdf-show" 
                        data-program-id="' . $program->id . '" title="Show PDF">
                        <i class="ri-file-pdf-line"></i>
                    </button>';

        $buttons .= '<button type="button" class="btn btn-sm btn-outline-dark me-1 program-pdf-download" 
                        data-program-id="' . $program->id . '" title="Download PDF">
                        <i class="ri-download-2-line"></i>
                    </button>';
        
        $buttons .= '<button onclick="deleteProgram(' . $program->id . ')" 
                        class="btn btn-sm btn-outline-danger" title="Delete Program">
                        <i class="ri-delete-bin-5-line"></i>
                    </button>';
        
        $buttons .= '</div>';
        
        return $buttons;
    }

    /**
     * Generate PDF data for a program
     */
    public function pdfData(Program $program): JsonResponse
    {
        try {
            $this->authorizeTrainer($program);
            $service = app(\App\Services\ProgramPdfService::class);
            $result = $service->generate($program);
            return response()->json([
                'success' => true,
                'data' => [
                    'pdf_view_url' => route('trainer.programs.pdf-view', ['program' => $program->id]),
                    'pdf_download_url' => route('trainer.programs.pdf-download', ['program' => $program->id]),
                    'file_url' => $result['url'],
                ]
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in ProgramController@pdfData: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to generate PDF'], 500);
        }
    }

    public function pdfInline(Program $program)
    {
        $this->authorizeTrainer($program);
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->stream($program);
    }

    public function pdfView(Program $program)
    {
        $this->authorizeTrainer($program);
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->stream($program);
    }

    public function pdfDownload(Program $program)
    {
        $this->authorizeTrainer($program);
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->download($program);
    }

    /**
     * Show the form for creating a new program
     */
    public function create(): View
    {
        $trainer = Auth::user();
        $clients = $trainer->subscriptionsAsTrainer()->with('client')->get()->pluck('client');
        
        return view('trainer.programs.create', [
            'clients' => $clients,
            'workouts' => \App\Models\Workout::all() // for builder reference
        ]);
    }

    /**
     * Store a newly created program
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:52',
            'client_id' => 'nullable|integer',
            'description' => 'nullable|string'
        ]);

        $program = Program::create([
            'trainer_id' => Auth::id(),
            'name' => $validated['name'],
            'duration' => $validated['duration'],
            'client_id' => $validated['client_id'],
            'description' => $validated['description'],
            'is_active' => false
        ]);

        return redirect()->route('trainer.programs.edit', $program)
            ->with('success', 'Program created successfully. Build your program using the builder.');
    }

    /**
     * Display the specified program
     */
    public function show(Program $program): View
    {
        $this->authorizeTrainer($program);
        
        $program->load(['trainer', 'client', 'weeks.days.circuits.programExercises.workout', 'weeks.days.circuits.programExercises.exerciseSets']);
        $totalDays = $program->weeks->sum(function($week){ return $week->days->count(); });
        $totalCircuits = $program->weeks->sum(function($week){ return $week->days->sum(function($day){ return $day->circuits->count(); }); });
        $totalExercises = $program->weeks->sum(function($week){ return $week->days->sum(function($day){ return $day->circuits->sum(function($circuit){ return $circuit->programExercises->count(); }); }); });

        return view('trainer.programs.show', [
            'program' => $program,
            'totalDays' => $totalDays,
            'totalCircuits' => $totalCircuits,
            'totalExercises' => $totalExercises
        ]);
    }

    /**
     * Show the form for editing the program
     */
    public function edit(Program $program): View
    {
        $this->authorizeTrainer($program);
        
        $trainer = Auth::user();
        $clients = $trainer->subscriptionsAsTrainer()->with('client')->get()->pluck('client');

        return view('trainer.programs.edit', [
            'program' => $program->load('weeks'),
            'clients' => $clients,
            'workouts' => \App\Models\Workout::all()
        ]);
    }

    /**
     * Display the program builder
     */
    public function builder(Program $program): View
    {
        $this->authorizeTrainer($program);

        return view('trainer.programs.builder', [
            'program' => $program->load(['trainer', 'client', 'weeks.days.circuits.programExercises.workout', 'weeks.days.circuits.programExercises.exerciseSets']),
            'workouts' => \App\Models\Workout::all()
        ]);
    }

    /**
     * Update the specified program
     */
    public function update(Request $request, Program $program): RedirectResponse
    {
        $this->authorizeTrainer($program);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1|max:52',
            'client_id' => 'nullable|integer',
            'description' => 'nullable|string'
        ]);

        $program->update($validated);

        return redirect()->route('trainer.programs.edit', $program)
            ->with('success', 'Program updated successfully.');
    }

    /**
     * Delete the specified program
     */
    public function destroy(Program $program): JsonResponse
    {
        $this->authorizeTrainer($program);

        $program->delete();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully'
        ]);
    }

    /**
     * Get program statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        try {
            $trainerId = Auth::id();

            $totalPrograms = Program::where('trainer_id', $trainerId)->count();
            $activePrograms = Program::where('trainer_id', $trainerId)
                ->where('is_active', true)->count();
            $assignedPrograms = Program::where('trainer_id', $trainerId)
                ->whereNotNull('client_id')->count();
            $unassignedPrograms = Program::where('trainer_id', $trainerId)
                ->whereNull('client_id')->count();

            return response()->json([
                'total_programs' => $totalPrograms,
                'active_programs' => $activePrograms,
                'assigned_programs' => $assignedPrograms,
                'unassigned_programs' => $unassignedPrograms
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in ProgramController@getStats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load statistics'], 500);
        }
    }

    /**
     * Assign a program template to a client (create a copy)
     */
    public function assign(Request $request, Program $program): RedirectResponse
    {
        $this->authorizeTrainer($program);
        
        $request->validate([
            'client_id' => 'required|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            // Create a duplicate of the program ASSIGNED TO THE CLIENT
            $assignedProgram = Program::create([
                'trainer_id' => Auth::id(),
                'client_id' => $request->client_id,
                'name' => $program->name,
                'duration' => $program->duration,
                'description' => $program->description,
                'is_active' => true // Activate by default when assigning
            ]);

            // Load the program with all its nested relationships
            $program->load([
                'weeks.days.circuits.programExercises.exerciseSets'
            ]);

            // Duplicate all weeks
            foreach ($program->weeks as $week) {
                $duplicatedWeek = $assignedProgram->weeks()->create([
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description
                ]);

                // Duplicate all days for this week
                foreach ($week->days as $day) {
                    $duplicatedDay = $duplicatedWeek->days()->create([
                        'day_number' => $day->day_number,
                        'title' => $day->title,
                        'description' => $day->description
                    ]);

                    // Duplicate all circuits for this day
                    foreach ($day->circuits as $circuit) {
                        $duplicatedCircuit = $duplicatedDay->circuits()->create([
                            'circuit_number' => $circuit->circuit_number,
                            'title' => $circuit->title,
                            'description' => $circuit->description,
                            'rounds' => $circuit->rounds,
                            'rest_between_rounds' => $circuit->rest_between_rounds
                        ]);

                        // Duplicate all program exercises for this circuit
                        foreach ($circuit->programExercises as $programExercise) {
                            $duplicatedProgramExercise = $duplicatedCircuit->programExercises()->create([
                                'workout_id' => $programExercise->workout_id,
                                'name' => $programExercise->name,
                                'order' => $programExercise->order,
                                'tempo' => $programExercise->tempo,
                                'rest_interval' => $programExercise->rest_interval,
                                'notes' => $programExercise->notes
                            ]);

                            // Duplicate all exercise sets for this program exercise
                            foreach ($programExercise->exerciseSets as $exerciseSet) {
                                $duplicatedProgramExercise->exerciseSets()->create([
                                    'set_number' => $exerciseSet->set_number,
                                    'reps' => $exerciseSet->reps,
                                    'weight' => $exerciseSet->weight
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return redirect()->route('trainer.programs.edit', $assignedProgram->id)
                ->with('success', 'Program assigned successfully. You can now customize it for this client.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in ProgramController@assign: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while assigning the program.');
        }
    }

    /**
     * Duplicate a program with all its weeks, days, circuits, exercises and sets
     */
    public function duplicate(Program $program): JsonResponse
    {
        $this->authorizeTrainer($program);

        try {
            DB::beginTransaction();

            // Create a duplicate of the program (without client assignment)
            $duplicatedProgram = Program::create([
                'trainer_id' => Auth::id(),
                'client_id' => null, // Remove client assignment for template
                'name' => $program->name . ' (Copy)',
                'duration' => $program->duration,
                'description' => $program->description,
                'is_active' => $program->is_active
            ]);

            // Load the program with all its nested relationships
            $program->load([
                'weeks.days.circuits.programExercises.exerciseSets'
            ]);

            // Duplicate all weeks
            foreach ($program->weeks as $week) {
                $duplicatedWeek = $duplicatedProgram->weeks()->create([
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description
                ]);

                // Duplicate all days for this week
                foreach ($week->days as $day) {
                    $duplicatedDay = $duplicatedWeek->days()->create([
                        'day_number' => $day->day_number,
                        'title' => $day->title,
                        'description' => $day->description
                    ]);

                    // Duplicate all circuits for this day
                    foreach ($day->circuits as $circuit) {
                        $duplicatedCircuit = $duplicatedDay->circuits()->create([
                            'circuit_number' => $circuit->circuit_number,
                            'title' => $circuit->title,
                            'description' => $circuit->description,
                            'rounds' => $circuit->rounds,
                            'rest_between_rounds' => $circuit->rest_between_rounds
                        ]);

                        // Duplicate all program exercises for this circuit
                        foreach ($circuit->programExercises as $programExercise) {
                            $duplicatedProgramExercise = $duplicatedCircuit->programExercises()->create([
                                'workout_id' => $programExercise->workout_id,
                                'name' => $programExercise->name,
                                'order' => $programExercise->order,
                                'tempo' => $programExercise->tempo,
                                'rest_interval' => $programExercise->rest_interval,
                                'notes' => $programExercise->notes
                            ]);

                            // Duplicate all exercise sets for this program exercise
                            foreach ($programExercise->exerciseSets as $exerciseSet) {
                                $duplicatedProgramExercise->exerciseSets()->create([
                                    'set_number' => $exerciseSet->set_number,
                                    'reps' => $exerciseSet->reps,
                                    'weight' => $exerciseSet->weight
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Program duplicated successfully.',
                'program_id' => $duplicatedProgram->id,
                'redirect_url' => route('trainer.programs.edit', $duplicatedProgram->id)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error in Trainer\ProgramController@duplicate: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while duplicating the program.'
            ], 500);
        }
    }

    /**
     * Show program progress (Workouts Completed)
     */
    public function progress(Program $program): View
    {
        $this->authorizeTrainer($program);

        // Ensure program is assigned to a client
        if (!$program->client_id) {
            abort(404, 'Program is not assigned to any client.');
        }

        // Load full program structure with client progress
        $program->load([
            'weeks.days.circuits.programExercises.workout', 
            'weeks.days.circuits.programExercises.exerciseSets',
            'weeks.days.circuits.programExercises.clientProgress' => function($q) use ($program) {
                $q->where('client_id', $program->client_id)->where('status', 'completed');
            }
        ]);

        // Fetch completed exercises for this program for Stats Calculation
        $rawProgress = ClientProgress::whereHas('programExercise.circuit.day.week', function($q) use ($program) {
                $q->where('program_id', $program->id);
            })
            ->where('status', 'completed')
            ->with(['programExercise.circuit.day.week', 'programExercise.workout'])
            ->orderBy('completed_at', 'desc')
            ->get();

        // Group by date for the list view (Historical Logs)
        $progress = $rawProgress->groupBy(function($item) {
            return $item->completed_at->format('Y-m-d');
        });

        // 1. Calculate Stats
        $totalWorkouts = $progress->count();
        $totalExercisesCompleted = $rawProgress->count();
        
        // Total Volume (Weight * Reps) - Convert weight to Lbs for volume calculation
        $totalVolume = $rawProgress->sum(function($item) {
            $weightInLbs = $item->logged_weight ? UnitConverter::kgToLbs($item->logged_weight) : 0;
            return $weightInLbs * $item->logged_reps;
        });

        // Program Completion Percentage
        $totalProgramDays = 0;
        foreach($program->weeks as $week) {
            $totalProgramDays += $week->days->count();
        }
        $completionPercentage = $totalProgramDays > 0 ? ($totalWorkouts / $totalProgramDays) * 100 : 0;
        $completionPercentage = min(100, round($completionPercentage, 1));

        // 2. Prepare Chart Data (Volume over time)
        // Group by date ascending for the chart
        $chartData = $rawProgress->sortBy('completed_at')->groupBy(function($item) {
            return $item->completed_at->format('Y-m-d');
        })->map(function($dayLogs) {
            return [
                'date' => $dayLogs->first()->completed_at->format('M d'),
                'volume' => $dayLogs->sum(function($item) {
                    $weightInLbs = $item->logged_weight ? UnitConverter::kgToLbs($item->logged_weight) : 0;
                    return $weightInLbs * $item->logged_reps;
                })
            ];
        })->values();

        // 3. Get client details
        $client = $program->client;

        return view('trainer.programs.progress', compact(
            'program', 
            'progress', 
            'client', 
            'totalWorkouts', 
            'totalExercisesCompleted',
            'totalVolume',
            'completionPercentage',
            'totalProgramDays',
            'chartData'
        ));
    }

    /**
     * Mark a specific day as complete for the client
     */
    public function markDayComplete(Request $request, Program $program, Day $day)
    {
        $this->authorizeTrainer($program);
        
        if ($day->week->program_id !== $program->id) {
            abort(404, 'Day does not belong to this program.');
        }

        if (!$program->client_id) {
             return back()->with('error', 'Program is not assigned to any client.');
        }

        // Load necessary relationships
        $day->load('circuits.programExercises.exerciseSets');

        DB::transaction(function () use ($day, $program) {
            $completedAt = now();
            
            // Loop through all exercises in the day
            foreach ($day->circuits as $circuit) {
                foreach ($circuit->programExercises as $exercise) {
                    foreach ($exercise->exerciseSets as $set) {
                        // Check if already completed to avoid duplicates
                        $exists = ClientProgress::where('client_id', $program->client_id)
                            ->where('program_exercise_id', $exercise->id)
                            ->where('set_number', $set->set_number)
                            ->where('status', 'completed')
                            ->exists();

                        if (!$exists) {
                            ClientProgress::create([
                                'client_id' => $program->client_id,
                                'program_exercise_id' => $exercise->id,
                                'set_number' => $set->set_number,
                                'status' => 'completed',
                                'logged_reps' => $set->reps,
                                'logged_weight' => $set->weight,
                                'notes' => 'Marked complete by trainer',
                                'completed_at' => $completedAt,
                            ]);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Day marked as complete successfully.');
    }

    /**
     * Authorize that the trainer owns this program
     */
    private function authorizeTrainer(Program $program): void
    {
        if ($program->trainer_id !== Auth::id()) {
            abort(403, 'Unauthorized to access this program.');
        }
    }
}
