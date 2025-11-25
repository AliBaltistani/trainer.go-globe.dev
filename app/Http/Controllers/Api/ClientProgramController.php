<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ClientProgramController extends ApiBaseController
{
    public function getAssignedPrograms(Request $request): JsonResponse
    {
        try {
            $clientId = Auth::id();
            $perPage = (int) ($request->input('per_page', 15));
            $programs = Program::byClient($clientId)
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
