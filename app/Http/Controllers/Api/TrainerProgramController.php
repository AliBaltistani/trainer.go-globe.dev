<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Program;
use App\Models\User;
use App\Models\TrainerSubscription;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainerProgramController extends ApiBaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $trainerId = Auth::id();
            
            // Get subscribed client IDs for this trainer
            $subscribedClientIds = TrainerSubscription::where('trainer_id', $trainerId)
                ->where('status', 'active')
                ->pluck('client_id')
                ->toArray();
            
            // Query for both self-created programs and assigned programs
            // Self-created: trainer_id = $trainerId (regardless of client_id)
            // Assigned: trainer_id = $trainerId AND client_id IN (subscribed clients)
            $query = Program::query()
                ->where('trainer_id', $trainerId)
                ->with(['client:id,name,email', 'trainer:id,name,email'])
                ->withCount(['weeks']);

            // Optional: Filter by type (self or assigned)
            if ($request->filled('type')) {
                $type = $request->input('type');
                if ($type === 'self') {
                    // Self-created programs (no client assigned or client is not in subscribed list)
                    $query->where(function ($q) use ($subscribedClientIds) {
                        $q->whereNull('client_id')
                          ->orWhereNotIn('client_id', $subscribedClientIds);
                    });
                } elseif ($type === 'assigned') {
                    // Assigned programs (client_id is in subscribed clients list)
                    $query->whereIn('client_id', $subscribedClientIds);
                }
            }

            if ($request->filled('search')) {
                $s = trim((string) $request->input('search'));
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('description', 'like', "%{$s}%")
                      ->orWhereHas('client', function ($clientQuery) use ($s) {
                          $clientQuery->where('name', 'like', "%{$s}%")
                                     ->orWhere('email', 'like', "%{$s}%");
                      });
                });
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            $perPage = (int) $request->input('per_page', 15);
            $programs = $query->paginate($perPage);

            // Add program type indicator to each program
            $programs->getCollection()->transform(function ($program) use ($subscribedClientIds) {
                $program->program_type = $program->client_id && in_array($program->client_id, $subscribedClientIds) 
                    ? 'assigned' 
                    : 'self';
                return $program;
            });

            return $this->sendResponse([
                'data' => $programs->items(),
                'pagination' => [
                    'total' => $programs->total(),
                    'per_page' => $programs->perPage(),
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage(),
                    'from' => $programs->firstItem(),
                    'to' => $programs->lastItem(),
                    'has_more_pages' => $programs->hasMorePages(),
                ],
                'summary' => [
                    'total_programs' => $programs->total(),
                    'self_created' => Program::where('trainer_id', $trainerId)
                        ->where(function ($q) use ($subscribedClientIds) {
                            $q->whereNull('client_id')
                              ->orWhereNotIn('client_id', $subscribedClientIds);
                        })
                        ->count(),
                    'assigned' => Program::where('trainer_id', $trainerId)
                        ->whereIn('client_id', $subscribedClientIds)
                        ->count(),
                ]
            ], 'Programs retrieved successfully');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@index failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve programs'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'duration' => 'required|integer|min:1|max:52',
                'description' => 'nullable|string',
                'client_id' => 'nullable|integer|exists:users,id',
                'is_active' => 'boolean'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $trainerId = Auth::id();
            $clientId = $request->input('client_id');
            if ($clientId) {
                $client = User::find($clientId);
                if (!$client || $client->role !== 'client') {
                    return $this->sendError('Validation Error', ['client_id' => ['Selected user is not a client']], 422);
                }
                
                // Check if client is subscribed to this trainer
                $subscription = TrainerSubscription::where('trainer_id', $trainerId)
                    ->where('client_id', $clientId)
                    ->where('status', 'active')
                    ->first();
                
                if (!$subscription) {
                    return $this->sendError('Validation Error', ['client_id' => ['Client is not subscribed to you. Only subscribed clients can be assigned programs.']], 422);
                }
            }

            $program = Program::create([
                'trainer_id' => $trainerId,
                'client_id' => $clientId,
                'name' => $request->input('name'),
                'duration' => (int) $request->input('duration'),
                'description' => $request->input('description'),
                'is_active' => (bool) $request->boolean('is_active', true),
            ]);

            if ($clientId) {
                $client = User::find($clientId);
                if ($client) {
                    $this->notificationService->sendNotification(
                        $client,
                        'New Program Assigned',
                        "You have been assigned a new program: {$program->name}",
                        [
                            'type' => 'program_assignment',
                            'program_id' => $program->id,
                            'redirect' => 'ProgramScreen'
                        ]
                    );
                }
            }

            return $this->sendResponse(['program' => $program], 'Program created successfully', 201);
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@store failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Creation Failed', ['error' => 'Unable to create program'], 500);
        }
    }

    public function show(Program $program): JsonResponse
    {
        try {
            if ($program->trainer_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $program->load(['trainer:id,name,email', 'client:id,name,email', 'weeks.days.circuits.programExercises.workout', 'weeks.days.circuits.programExercises.exerciseSets']);

            return $this->sendResponse(['program' => $program], 'Program retrieved successfully');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@show failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve program'], 500);
        }
    }

    public function update(Request $request, Program $program): JsonResponse
    {
        try {
            if ($program->trainer_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'duration' => 'required|integer|min:1|max:52',
                'description' => 'nullable|string',
                'client_id' => 'nullable|integer|exists:users,id',
                'is_active' => 'boolean'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $clientId = $request->input('client_id');
            if ($clientId) {
                $client = User::find($clientId);
                if (!$client || $client->role !== 'client') {
                    return $this->sendError('Validation Error', ['client_id' => ['Selected user is not a client']], 422);
                }
                
                // Check if client is subscribed to this trainer
                $subscription = TrainerSubscription::where('trainer_id', Auth::id())
                    ->where('client_id', $clientId)
                    ->where('status', 'active')
                    ->first();
                
                if (!$subscription) {
                    return $this->sendError('Validation Error', ['client_id' => ['Client is not subscribed to you. Only subscribed clients can be assigned programs.']], 422);
                }
            }

            $program->update([
                'name' => $request->input('name'),
                'duration' => (int) $request->input('duration'),
                'description' => $request->input('description'),
                'client_id' => $clientId,
                'is_active' => (bool) $request->boolean('is_active', $program->is_active),
            ]);

            // Notify new client if client_id changed or just notify current client about update?
            // Let's notify if client_id is set
            if ($clientId) {
                $client = User::find($clientId);
                if ($client) {
                    $this->notificationService->sendNotification(
                        $client,
                        'Program Updated',
                        "Your program '{$program->name}' has been updated.",
                        [
                            'type' => 'program_update',
                            'program_id' => $program->id,
                            'redirect' => 'ProgramScreen'
                        ]
                    );
                }
            }

            return $this->sendResponse(['program' => $program], 'Program updated successfully');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@update failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Update Failed', ['error' => 'Unable to update program'], 500);
        }
    }

    public function destroy(Program $program): JsonResponse
    {
        try {
            if ($program->trainer_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $program->delete();
            return $this->sendResponse(['deleted' => true], 'Program deleted successfully');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@destroy failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Deletion Failed', ['error' => 'Unable to delete program'], 500);
        }
    }

    public function duplicate(Program $program): JsonResponse
    {
        try {
            if ($program->trainer_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            DB::beginTransaction();

            $newProgram = Program::create([
                'trainer_id' => $program->trainer_id,
                'client_id' => null,
                'name' => $program->name . ' (Copy)',
                'duration' => $program->duration,
                'description' => $program->description,
                'is_active' => $program->is_active
            ]);

            $program->load(['weeks.days.circuits.programExercises.exerciseSets']);

            foreach ($program->weeks as $week) {
                $dupWeek = $newProgram->weeks()->create([
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description
                ]);
                foreach ($week->days as $day) {
                    $dupDay = $dupWeek->days()->create([
                        'day_number' => $day->day_number,
                        'title' => $day->title,
                        'description' => $day->description,
                        'cool_down' => $day->cool_down,
                        'custom_rows' => $day->custom_rows,
                    ]);
                    foreach ($day->circuits as $circuit) {
                        $dupCircuit = $dupDay->circuits()->create([
                            'circuit_number' => $circuit->circuit_number,
                            'title' => $circuit->title,
                            'description' => $circuit->description
                        ]);
                        foreach ($circuit->programExercises as $exercise) {
                            $dupExercise = $dupCircuit->programExercises()->create([
                                'workout_id' => $exercise->workout_id,
                                'name' => $exercise->name,
                                'order' => $exercise->order,
                                'tempo' => $exercise->tempo,
                                'rest_interval' => $exercise->rest_interval,
                                'notes' => $exercise->notes
                            ]);
                            foreach ($exercise->exerciseSets as $set) {
                                $dupExercise->exerciseSets()->create([
                                    'set_number' => $set->set_number,
                                    'reps' => $set->reps,
                                    'weight' => $set->weight
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return $this->sendResponse(['program' => $newProgram], 'Program duplicated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TrainerProgramController@duplicate failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Duplication Failed', ['error' => 'Unable to duplicate program'], 500);
        }
    }

    public function assign(Program $program, Request $request): JsonResponse
    {
        try {
            if ($program->trainer_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $validator = Validator::make($request->all(), [
                'client_id' => 'required|integer|exists:users,id'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }
            $client = User::findOrFail($request->input('client_id'));
            if ($client->role !== 'client') {
                return $this->sendError('Validation Error', ['client_id' => ['Selected user is not a client']], 422);
            }

            // Check if client is subscribed to this trainer
            $subscription = \App\Models\TrainerSubscription::where('trainer_id', Auth::id())
                ->where('client_id', $client->id)
                ->where('status', 'active')
                ->first();
            
            if (!$subscription) {
                return $this->sendError('Validation Error', ['client_id' => ['Client is not subscribed to you. Only subscribed clients can be assigned programs.']], 422);
            }

            DB::beginTransaction();

            $assignedProgram = Program::create([
                'trainer_id' => $program->trainer_id,
                'client_id' => $client->id,
                'name' => $program->name . ' - ' . $client->name,
                'duration' => $program->duration,
                'description' => $program->description,
                'is_active' => true
            ]);

            $program->load(['weeks.days.circuits.programExercises.exerciseSets']);

            foreach ($program->weeks as $week) {
                $newWeek = $assignedProgram->weeks()->create([
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description
                ]);
                foreach ($week->days as $day) {
                    $newDay = $newWeek->days()->create([
                        'day_number' => $day->day_number,
                        'title' => $day->title,
                        'description' => $day->description,
                        'cool_down' => $day->cool_down,
                        'custom_rows' => $day->custom_rows,
                    ]);
                    foreach ($day->circuits as $circuit) {
                        $newCircuit = $newDay->circuits()->create([
                            'circuit_number' => $circuit->circuit_number,
                            'title' => $circuit->title,
                            'description' => $circuit->description
                        ]);
                        foreach ($circuit->programExercises as $exercise) {
                            $newExercise = $newCircuit->programExercises()->create([
                                'workout_id' => $exercise->workout_id,
                                'name' => $exercise->name,
                                'order' => $exercise->order,
                                'tempo' => $exercise->tempo,
                                'rest_interval' => $exercise->rest_interval,
                                'notes' => $exercise->notes
                            ]);
                            foreach ($exercise->exerciseSets as $set) {
                                $newExercise->exerciseSets()->create([
                                    'set_number' => $set->set_number,
                                    'reps' => $set->reps,
                                    'weight' => $set->weight
                                ]);
                            }
                        }
                    }
                }
            }

            DB::commit();

            return $this->sendResponse([
                'assigned_program_id' => $assignedProgram->id,
                'client_name' => $client->name
            ], 'Program assigned successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TrainerProgramController@assign failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Assignment Failed', ['error' => 'Unable to assign program'], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $trainerId = Auth::id();
            $total = Program::where('trainer_id', $trainerId)->count();
            $active = Program::where('trainer_id', $trainerId)->where('is_active', true)->count();
            $assigned = Program::where('trainer_id', $trainerId)->whereNotNull('client_id')->count();
            $unassigned = Program::where('trainer_id', $trainerId)->whereNull('client_id')->count();

            return $this->sendResponse([
                'total_programs' => $total,
                'active_programs' => $active,
                'assigned_programs' => $assigned,
                'unassigned_programs' => $unassigned,
            ], 'Program statistics retrieved');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@stats failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to load statistics'], 500);
        }
    }

    public function pdfData(Program $program): JsonResponse
    {
        try {
            // if ($program->trainer_id !== Auth::id()) {
            //     return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            // }

            $service = app(\App\Services\ProgramPdfService::class);
            $result = $service->generate($program);

            return $this->sendResponse([
                'pdf_view_url' => route('api.trainer.programs.pdf-view', ['program' => $program->id]),
                'pdf_download_url' => route('api.trainer.programs.pdf-download', ['program' => $program->id]),
                'file_url' => url($result['url'])
            ], 'PDF generated');
        } catch (\Exception $e) {
            Log::error('TrainerProgramController@pdfData failed: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'program_id' => $program->id,
            ]);
            return $this->sendError('Generation Failed', ['error' => 'Unable to generate PDF'], 500);
        }
    }
    public function pdfView(Program $program)
    {
        if ($program->trainer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->stream($program);
    }

    public function pdfDownload(Program $program)
    {
        if ($program->trainer_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->download($program);
    }
}
