<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Program;
use App\Models\ProgramVideo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * API Program Video Controller
 * 
 * API endpoints for program videos CRUD operations
 * Supports both trainer and client program videos
 */
class ApiProgramVideoController extends ApiBaseController
{
    /**
     * Check if user owns the program (trainer or client)
     */
    private function ownsProgram(Program $program): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }
        
        // Trainer owns program if trainer_id matches
        if ($user->role === 'trainer') {
            return $program->trainer_id === $user->id;
        }
        
        // Client owns program if client_id matches and trainer_id is null (self-created)
        if ($user->role === 'client') {
            return $program->client_id === $user->id && $program->trainer_id === null;
        }
        
        return false;
    }
    /**
     * Get program with videos (in program_plans structure)
     */
    public function getProgramPlan($programId)
    {
        try {
            $program = Program::with([
                'weeks.days.circuits.programExercises.exerciseSets',
                'videos'
            ])->findOrFail($programId);

            // Build response with program_plans
            $response = [
                'success' => true,
                'data' => [
                    'program' => $this->formatProgramResponse($program)
                ]
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Program not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all programs for authenticated trainer (with videos)
     */
    public function indexWithVideos(Request $request)
    {
        try {
            $trainer = auth('sanctum')->user();
            
            $programs = Program::where('trainer_id', $trainer->id)
                ->with([
                    'weeks.days.circuits.programExercises.exerciseSets',
                    'videos'
                ])
                ->orderBy('created_at', 'desc')
                ->paginate($request->input('per_page', 15));

            $formattedPrograms = $programs->map(fn($prog) => $this->formatProgramResponse($prog));

            return response()->json([
                'success' => true,
                'data' => $formattedPrograms,
                'meta' => [
                    'total' => $programs->total(),
                    'per_page' => $programs->perPage(),
                    'current_page' => $programs->currentPage(),
                    'last_page' => $programs->lastPage()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching programs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get program videos
     */
    public function getVideos(Program $program): JsonResponse
    {
        try {
            // Check access: trainers can view their programs, clients can view assigned or self-created
            $user = Auth::user();
            if ($user->role === 'trainer' && $program->trainer_id !== $user->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            if ($user->role === 'client' && $program->client_id !== $user->id) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }
            
            $videos = $program->videos()
                ->orderBy('order')
                ->get()
                ->map(fn($v) => $this->formatVideoResponse($v));

            return $this->sendResponse([
                'videos' => $videos,
                'count' => $videos->count()
            ], 'Videos retrieved successfully');
        } catch (\Exception $e) {
            Log::error('ApiProgramVideoController@getVideos failed: ' . $e->getMessage());
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve videos'], 500);
        }
    }

    /**
     * Store a newly created video
     */
    public function store(Request $request, Program $program): JsonResponse
    {
        try {
            if (!$this->ownsProgram($program)) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'video_type' => 'required|in:youtube,vimeo,url,file',
                'video_url' => 'required_if:video_type,youtube,vimeo,url|nullable|url',
                'video_file' => 'required_if:video_type,file|nullable|file|mimes:mp4,avi,mov,wmv,flv,webm,mkv|max:102400',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'duration' => 'nullable|integer|min:1',
                'order' => 'nullable|integer|min:0',
                'is_preview' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $validated = $validator->validated();

            // Handle file upload
            if ($request->hasFile('video_file')) {
                $file = $request->file('video_file');
                $videoPath = $file->store('program-videos', 'public');
                $validated['video_url'] = $videoPath;
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                $thumbnail = $request->file('thumbnail');
                $thumbnailPath = $thumbnail->store('program-thumbnails', 'public');
                $validated['thumbnail'] = $thumbnailPath;
            }

            // Auto-order if not provided
            if (!isset($validated['order'])) {
                $validated['order'] = $program->videos()->max('order') ?? 0;
                $validated['order'] += 1;
            }

            $validated['program_id'] = $program->id;

            $video = ProgramVideo::create($validated);

            return $this->sendResponse([
                'video' => $this->formatVideoResponse($video)
            ], 'Video created successfully', 201);
        } catch (\Exception $e) {
            Log::error('ApiProgramVideoController@store failed: ' . $e->getMessage(), [
                'program_id' => $program->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Creation Failed', ['error' => 'Unable to create video'], 500);
        }
    }

    /**
     * Update the specified video
     */
    public function update(Request $request, Program $program, ProgramVideo $video): JsonResponse
    {
        try {
            if (!$this->ownsProgram($program)) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            // Verify video belongs to program
            if ($video->program_id !== $program->id) {
                return $this->sendError('Not Found', ['error' => 'Video not found in this program'], 404);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'video_type' => 'sometimes|required|in:youtube,vimeo,url,file',
                'video_url' => 'required_if:video_type,youtube,vimeo,url|nullable|url',
                'video_file' => 'nullable|file|mimes:mp4,avi,mov,wmv,flv,webm,mkv|max:102400',
                'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'duration' => 'nullable|integer|min:1',
                'order' => 'nullable|integer|min:0',
                'is_preview' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $validated = $validator->validated();

            // Handle file upload
            if ($request->hasFile('video_file')) {
                // Delete old file if exists
                if ($video->video_type === 'file' && $video->video_url && Storage::disk('public')->exists($video->video_url)) {
                    Storage::disk('public')->delete($video->video_url);
                }
                
                $file = $request->file('video_file');
                $videoPath = $file->store('program-videos', 'public');
                $validated['video_url'] = $videoPath;
            }

            // Handle thumbnail upload
            if ($request->hasFile('thumbnail')) {
                // Delete old thumbnail if exists
                if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                    Storage::disk('public')->delete($video->thumbnail);
                }
                
                $thumbnail = $request->file('thumbnail');
                $thumbnailPath = $thumbnail->store('program-thumbnails', 'public');
                $validated['thumbnail'] = $thumbnailPath;
            }

            $video->update($validated);
            $video->refresh();

            return $this->sendResponse([
                'video' => $this->formatVideoResponse($video)
            ], 'Video updated successfully');
        } catch (\Exception $e) {
            Log::error('ApiProgramVideoController@update failed: ' . $e->getMessage(), [
                'program_id' => $program->id,
                'video_id' => $video->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Update Failed', ['error' => 'Unable to update video'], 500);
        }
    }

    /**
     * Delete the specified video
     */
    public function destroy(Program $program, ProgramVideo $video): JsonResponse
    {
        try {
            if (!$this->ownsProgram($program)) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            // Verify video belongs to program
            if ($video->program_id !== $program->id) {
                return $this->sendError('Not Found', ['error' => 'Video not found in this program'], 404);
            }

            // Delete video file if exists
            if ($video->video_type === 'file' && $video->video_url && Storage::disk('public')->exists($video->video_url)) {
                Storage::disk('public')->delete($video->video_url);
            }

            // Delete thumbnail
            if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
                Storage::disk('public')->delete($video->thumbnail);
            }

            $video->delete();

            return $this->sendResponse(['deleted' => true], 'Video deleted successfully');
        } catch (\Exception $e) {
            Log::error('ApiProgramVideoController@destroy failed: ' . $e->getMessage(), [
                'program_id' => $program->id,
                'video_id' => $video->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Deletion Failed', ['error' => 'Unable to delete video'], 500);
        }
    }

    /**
     * Reorder videos
     */
    public function reorder(Request $request, Program $program): JsonResponse
    {
        try {
            if (!$this->ownsProgram($program)) {
                return $this->sendError('Unauthorized', ['error' => 'Access denied'], 403);
            }

            $validator = Validator::make($request->all(), [
                'videos' => 'required|array',
                'videos.*.id' => 'required|exists:program_videos,id',
                'videos.*.order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            DB::beginTransaction();

            foreach ($request->input('videos') as $videoData) {
                ProgramVideo::where('id', $videoData['id'])
                    ->where('program_id', $program->id)
                    ->update(['order' => $videoData['order']]);
            }

            DB::commit();

            return $this->sendResponse(['success' => true], 'Videos reordered successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('ApiProgramVideoController@reorder failed: ' . $e->getMessage(), [
                'program_id' => $program->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Reorder Failed', ['error' => 'Unable to reorder videos'], 500);
        }
    }

    /**
     * Format program response with program_plans structure
     */
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
                                    'title' => $ex->workout->name
                                ] : null,
                                'order' => $ex->order,
                                'notes' => $ex->notes,
                                'sets' => $ex->exerciseSets->map(fn($set) => [
                                    'id' => $set->id,
                                    'set_number' => $set->set_number,
                                    'reps' => $set->reps,
                                    'weight' => $set->weight
                                ])->toArray()
                            ])->toArray()
                        ])->toArray()
                    ])->toArray()
                ])->toArray(),
                'videos' => $program->videos->map(fn($v) => $this->formatVideoResponse($v))->toArray()
            ]
        ];
    }

    /**
     * Format video response
     */
    private function formatVideoResponse($video)
    {
        return [
            'id' => $video->id,
            'title' => $video->title,
            'description' => $video->description,
            'video_type' => $video->video_type,
            'video_url' => $video->video_url,
            'embed_url' => $video->embed_url,
            'thumbnail' => $video->thumbnail ? asset('storage/' . $video->thumbnail) : null,
            'duration' => $video->duration,
            'formatted_duration' => $video->formatted_duration,
            'order' => $video->order,
            'is_preview' => $video->is_preview,
            'created_at' => $video->created_at,
            'updated_at' => $video->updated_at
        ];
    }
}
