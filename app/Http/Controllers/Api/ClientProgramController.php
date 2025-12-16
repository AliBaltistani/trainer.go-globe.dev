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

class ClientProgramController extends ApiBaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all programs for the authenticated client
     * Includes both assigned programs (from trainers) and self-created programs
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $clientId = Auth::id();
            $query = Program::query()
                ->where('client_id', $clientId)
                ->withCount(['weeks']);

            if ($request->filled('search')) {
                $s = trim((string) $request->input('search'));
                $query->where(function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                      ->orWhere('description', 'like', "%{$s}%");
                });
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Filter by program type: 'assigned' (has trainer_id) or 'self' (trainer_id is null)
            if ($request->filled('type')) {
                $type = $request->input('type');
                if ($type === 'assigned') {
                    $query->whereNotNull('trainer_id');
                } elseif ($type === 'self') {
                    $query->whereNull('trainer_id');
                }
            }

            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $query->orderBy($sortBy, $sortDir);

            $perPage = (int) $request->input('per_page', 15);
            $programs = $query->paginate($perPage);

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
                ]
            ], 'Programs retrieved successfully');
        } catch (\Exception $e) {
            Log::error('ClientProgramController@index failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve programs'], 500);
        }
    }

    /**
     * Create a new program (self-created by client)
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'duration' => 'required|integer|min:1|max:52',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $clientId = Auth::id();

            // Clients can create self programs (trainer_id is null)
            $program = Program::create([
                'trainer_id' => null, // Self-created program
                'client_id' => $clientId,
                'name' => $request->input('name'),
                'duration' => (int) $request->input('duration'),
                'description' => $request->input('description'),
                'is_active' => (bool) $request->boolean('is_active', true),
            ]);

            return $this->sendResponse(['program' => $program], 'Program created successfully', 201);
        } catch (\Exception $e) {
            Log::error('ClientProgramController@store failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Creation Failed', ['error' => 'Unable to create program'. $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific program
     */
    public function show(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $program->load(['trainer:id,name,email', 'client:id,name,email', 'weeks.days.circuits.programExercises.workout', 'weeks.days.circuits.programExercises.exerciseSets']);

            return $this->sendResponse(['program' => $program], 'Program retrieved successfully');
        } catch (\Exception $e) {
            Log::error('ClientProgramController@show failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve program'], 500);
        }
    }

    /**
     * Update a program (only self-created programs can be updated)
     */
    public function update(Request $request, Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            // Only allow updating self-created programs (trainer_id is null)
            if ($program->trainer_id !== null) {
                return $this->sendError('Forbidden', ['error' => 'Cannot update trainer-assigned programs'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'duration' => 'required|integer|min:1|max:52',
                'description' => 'nullable|string',
                'is_active' => 'boolean'
            ]);
            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $program->update([
                'name' => $request->input('name'),
                'duration' => (int) $request->input('duration'),
                'description' => $request->input('description'),
                'is_active' => (bool) $request->boolean('is_active', $program->is_active),
            ]);

            return $this->sendResponse(['program' => $program], 'Program updated successfully');
        } catch (\Exception $e) {
            Log::error('ClientProgramController@update failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'program_id' => $program->id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Update Failed', ['error' => 'Unable to update program'], 500);
        }
    }

    /**
     * Delete a program (only self-created programs can be deleted)
     */
    public function destroy(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            // Only allow deleting self-created programs (trainer_id is null)
            if ($program->trainer_id !== null) {
                return $this->sendError('Forbidden', ['error' => 'Cannot delete trainer-assigned programs'], 403);
            }

            $program->delete();
            return $this->sendResponse(['deleted' => true], 'Program deleted successfully');
        } catch (\Exception $e) {
            Log::error('ClientProgramController@destroy failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Deletion Failed', ['error' => 'Unable to delete program'], 500);
        }
    }

    /**
     * Duplicate a program (can duplicate both assigned and self-created programs)
     */
    public function duplicate(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            DB::beginTransaction();

            $clientId = Auth::id();
            $newProgram = Program::create([
                'trainer_id' => null, // Duplicated program becomes self-created
                'client_id' => $clientId,
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
            Log::error('ClientProgramController@duplicate failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'program_id' => $program->id,
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Duplication Failed', ['error' => 'Unable to duplicate program'], 500);
        }
    }

    /**
     * Get program statistics for the client
     */
    public function stats(): JsonResponse
    {
        try {
            $clientId = Auth::id();
            $total = Program::where('client_id', $clientId)->count();
            $active = Program::where('client_id', $clientId)->where('is_active', true)->count();
            $assigned = Program::where('client_id', $clientId)->whereNotNull('trainer_id')->count();
            $selfCreated = Program::where('client_id', $clientId)->whereNull('trainer_id')->count();

            return $this->sendResponse([
                'total_programs' => $total,
                'active_programs' => $active,
                'assigned_programs' => $assigned,
                'self_created_programs' => $selfCreated,
            ], 'Program statistics retrieved');
        } catch (\Exception $e) {
            Log::error('ClientProgramController@stats failed: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to load statistics'], 500);
        }
    }

    /**
     * Get assigned programs (read-only, from trainers)
     */
    public function getAssignedPrograms(Request $request): JsonResponse
    {
        try {
            $clientId = Auth::id();
            $perPage = (int) ($request->input('per_page', 15));
            $programs = Program::where('client_id', $clientId)
                ->whereNotNull('trainer_id')
                ->with(['trainer:id,name', 'weeks.days.circuits.programExercises', 'videos'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $data = $programs->getCollection()->map(function ($program) {
                $weeksCount = $program->weeks->count();
                $daysCount = $program->weeks->sum(fn($w) => $w->days->count());
                $circuitsCount = $program->weeks->sum(fn($w) => $w->days->sum(fn($d) => $d->circuits->count()));
                $exercisesCount = $program->weeks->sum(fn($w) => $w->days->sum(fn($d) => $d->circuits->sum(fn($c) => $c->programExercises->count())));
                return [
                    'id' => $program->id,
                    'name' => $program->name,
                    'description' => $program->description,
                    'duration' => $program->duration,
                    'is_active' => $program->is_active,
                    'trainer' => $program->trainer ? [
                        'id' => $program->trainer->id,
                        'name' => $program->trainer->name,
                    ] : null,
                    'counts' => [
                        'weeks' => $weeksCount,
                        'days' => $daysCount,
                        'circuits' => $circuitsCount,
                        'exercises' => $exercisesCount,
                        'videos' => $program->videos->count(),
                    ],
                    'created_at' => $program->created_at,
                    'updated_at' => $program->updated_at,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'total' => $programs->total(),
                    'per_page' => $programs->perPage(),
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ClientProgramController@getAssignedPrograms failed: ' . $e->getMessage());
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve assigned programs'], 500);
        }
    }

    public function pdfData(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $service = app(\App\Services\ProgramPdfService::class);
            $result = $service->generate($program);
            return $this->sendResponse([
                'pdf_view_url' => route('api.client.programs.pdf-view', ['program' => $program->id]),
                'pdf_download_url' => route('api.client.programs.pdf-download', ['program' => $program->id]),
                'file_url' => url($result['url'])
            ], 'PDF generated');
        } catch (\Exception $e) {
            return $this->sendError('Generation Failed', ['error' => 'Unable to generate PDF'], 500);
        }
    }

    public function pdfView(Program $program)
    {
        if ($program->client_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->stream($program);
    }

    public function pdfDownload(Program $program)
    {
        if ($program->client_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        $service = app(\App\Services\ProgramPdfService::class);
        return $service->download($program);
    }

    public function plan(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $program->load(['weeks.days.circuits.programExercises.exerciseSets', 'videos']);
            return response()->json([
                'success' => true,
                'data' => [
                    'program' => $this->formatProgramResponse($program)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Program not found'], 404);
        }
    }

    public function weeks(Program $program): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $weeks = \App\Models\Week::where('program_id', $program->id)->orderBy('week_number')->get()->map(function ($week) {
                return [
                    'id' => $week->id,
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description,
                ];
            });
            return $this->sendResponse(['weeks' => $weeks, 'total' => $weeks->count()], 'Weeks retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve weeks'], 500);
        }
    }

    public function showWeek(Program $program, \App\Models\Week $week): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $week->load(['days.circuits.programExercises.exerciseSets', 'days.circuits.programExercises.workout']);
            return $this->sendResponse(['week' => $week], 'Week retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve week'], 500);
        }
    }

    public function days(Program $program, \App\Models\Week $week): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $days = \App\Models\Day::where('week_id', $week->id)->orderBy('day_number')->get()->map(function ($day) {
                return [
                    'id' => $day->id,
                    'day_number' => $day->day_number,
                    'title' => $day->title,
                    'description' => $day->description,
                ];
            });
            return $this->sendResponse(['days' => $days, 'total' => $days->count()], 'Days retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve days'], 500);
        }
    }

    public function showDay(Program $program, \App\Models\Week $week, \App\Models\Day $day): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id || $day->week_id !== $week->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $day->load(['circuits.programExercises.exerciseSets', 'circuits.programExercises.workout']);
            return $this->sendResponse(['day' => $day], 'Day retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve day'], 500);
        }
    }

    public function circuits(Program $program, \App\Models\Week $week, \App\Models\Day $day): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id || $day->week_id !== $week->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $circuits = \App\Models\Circuit::where('day_id', $day->id)->orderBy('circuit_number')->get()->map(function ($circuit) {
                return [
                    'id' => $circuit->id,
                    'circuit_number' => $circuit->circuit_number,
                    'title' => $circuit->title,
                    'description' => $circuit->description,
                ];
            });
            return $this->sendResponse(['circuits' => $circuits, 'total' => $circuits->count()], 'Circuits retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve circuits'], 500);
        }
    }

    public function showCircuit(Program $program, \App\Models\Week $week, \App\Models\Day $day, \App\Models\Circuit $circuit): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id || $day->week_id !== $week->id || $circuit->day_id !== $day->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $circuit->load(['programExercises.exerciseSets', 'programExercises.workout']);
            return $this->sendResponse(['circuit' => $circuit], 'Circuit retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve circuit'], 500);
        }
    }

    public function exercises(Program $program, \App\Models\Week $week, \App\Models\Day $day, \App\Models\Circuit $circuit): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id || $day->week_id !== $week->id || $circuit->day_id !== $day->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $exercises = \App\Models\ProgramExercise::where('circuit_id', $circuit->id)->orderBy('order')->get()->map(function ($ex) {
                return [
                    'id' => $ex->id,
                    'name' => $ex->name,
                    'workout_id' => $ex->workout_id,
                    'order' => $ex->order,
                    'tempo' => $ex->tempo,
                    'rest_interval' => $ex->rest_interval,
                    'notes' => $ex->notes,
                ];
            });
            return $this->sendResponse(['exercises' => $exercises, 'total' => $exercises->count()], 'Exercises retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve exercises'], 500);
        }
    }

    public function showExercise(Program $program, \App\Models\Week $week, \App\Models\Day $day, \App\Models\Circuit $circuit, \App\Models\ProgramExercise $exercise): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id() || $week->program_id !== $program->id || $day->week_id !== $week->id || $circuit->day_id !== $day->id || $exercise->circuit_id !== $circuit->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $exercise->load(['exerciseSets', 'workout']);
            return $this->sendResponse(['exercise' => [
                'id' => $exercise->id,
                'name' => $exercise->name,
                'workout_id' => $exercise->workout_id,
                'order' => $exercise->order,
                'tempo' => $exercise->tempo,
                'rest_interval' => $exercise->rest_interval,
                'notes' => $exercise->notes,
                'workout' => $exercise->workout ? [
                    'id' => $exercise->workout->id,
                    'name' => $exercise->workout->name,
                    'title' => $exercise->workout->name,
                ] : null,
                'sets' => $exercise->exerciseSets->map(function ($set) {
                    return [
                        'id' => $set->id,
                        'set_number' => $set->set_number,
                        'reps' => $set->reps,
                        'weight' => $set->weight,
                    ];
                }),
            ]], 'Exercise retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve exercise'], 500);
        }
    }

    public function exerciseSets(Program $program, \App\Models\ProgramExercise $exercise): JsonResponse
    {
        try {
            if ($program->client_id !== Auth::id()) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $belongs = \App\Models\Week::where('program_id', $program->id)->whereHas('days.circuits.programExercises', function ($q) use ($exercise) {
                $q->where('program_exercises.id', $exercise->id);
            })->exists();
            if (!$belongs) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            $sets = \App\Models\ExerciseSet::where('program_exercise_id', $exercise->id)->orderBy('set_number')->get()->map(function ($set) {
                return [
                    'id' => $set->id,
                    'set_number' => $set->set_number,
                    'reps' => $set->reps,
                    'weight' => $set->weight,
                ];
            });
            return $this->sendResponse(['sets' => $sets, 'total' => $sets->count()], 'Sets retrieved');
        } catch (\Exception $e) {
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve sets'], 500);
        }
    }

    private function formatProgramResponse($program)
    {
        return [
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'duration' => $program->duration,
            'trainer_id' => $program->trainer_id,
            'client_id' => $program->client_id,
            'is_active' => $program->is_active,
            'created_at' => $program->created_at,
            'updated_at' => $program->updated_at,
            'program_plans' => [
                'weeks' => $program->weeks->map(fn($week) => [
                    'id' => $week->id,
                    'week_number' => $week->week_number,
                    'title' => $week->title,
                    'description' => $week->description,
                    'days' => $week->days->map(fn($day) => [
                        'id' => $day->id,
                        'day_number' => $day->day_number,
                        'title' => $day->title,
                        'circuits' => $day->circuits->map(fn($circuit) => [
                            'id' => $circuit->id,
                            'circuit_number' => $circuit->circuit_number,
                            'title' => $circuit->title,
                            'description' => $circuit->description,
                            'exercises' => $circuit->programExercises->map(fn($ex) => [
                                'id' => $ex->id,
                                'name' => $ex->name,
                                'workout_id' => $ex->workout_id,
                                'workout' => $ex->workout ? [
                                    'id' => $ex->workout->id,
                                    'name' => $ex->workout->name,
                                    'title' => $ex->workout->name,
                                ] : null,
                                'order' => $ex->order,
                                'notes' => $ex->notes,
                                'sets' => $ex->exerciseSets->map(fn($set) => [
                                    'id' => $set->id,
                                    'set_number' => $set->set_number,
                                    'reps' => $set->reps,
                                    'weight' => $set->weight,
                                ])->toArray(),
                            ])->toArray(),
                        ])->toArray(),
                    ])->toArray(),
                ])->toArray(),
                'videos' => $program->videos->map(fn($v) => [
                    'id' => $v->id,
                    'title' => $v->title,
                    'description' => $v->description,
                    'video_type' => $v->video_type,
                    'video_url' => $v->video_url,
                    'embed_url' => $v->embed_url,
                    'thumbnail' => $v->thumbnail ? asset('storage/' . $v->thumbnail) : null,
                    'duration' => $v->duration,
                    'formatted_duration' => $v->formatted_duration,
                    'order' => $v->order,
                    'is_preview' => $v->is_preview,
                    'created_at' => $v->created_at,
                    'updated_at' => $v->updated_at,
                ])->toArray(),
            ],
        ];
    }
}
