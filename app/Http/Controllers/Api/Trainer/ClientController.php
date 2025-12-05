<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\TrainerSubscription;
use App\Mail\ClientInvitation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

/**
 * Trainer Client Management Controller
 * 
 * Handles client operations for trainers via API
 * Matches functionality of web panel ClientController
 */
class ClientController extends Controller
{
    /**
     * Get all clients with search and filtering capabilities
     * 
     * Retrieves clients with optional search functionality
     * Includes client goals, progress, and basic information
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validate trainer authentication
            $trainer = Auth::user();
            if (!$trainer || !$trainer->isTrainerRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only trainers can view clients.'
                ], 403);
            }

            // Validate search parameters
            $validator = Validator::make($request->all(), [
                'search' => 'nullable|string|max:255',
                'fitness_level' => 'nullable|string|in:Beginner,Intermediate,Advanced',
                'sort_by' => 'nullable|string|in:name,email,created_at',
                'sort_order' => 'nullable|string|in:asc,desc',
                'per_page' => 'nullable|integer|min:5|max:100',
                'page' => 'nullable|integer|min:1',
                'updated_after' => 'nullable|date_format:Y-m-d H:i:s'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Build query for clients
            $query = User::where('role', 'client')
                ->whereHas('subscriptionsAsClient', function($q) use ($trainer) {
                    $q->where('trainer_id', $trainer->id);
                })
                ->with([
                    'goals:id,user_id,name,status',
                    'clientSchedules' => function($q) use ($trainer) {
                        $q->where('trainer_id', $trainer->id)
                          ->select('id', 'client_id', 'trainer_id', 'date', 'status');
                    }
                ])
                ->select('id', 'name', 'email', 'phone', 'profile_image', 'created_at', 'updated_at');

            // Filter by updated_at for offline sync
            if ($request->filled('updated_after')) {
                $query->where('updated_at', '>', $request->updated_after);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $searchTerm = trim($request->search);
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
                });
            }

            // Apply sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->get('per_page', 20);
            $clients = $query->paginate($perPage);

            // Transform client data for response
            $transformedClients = $clients->getCollection()->map(function ($client) {
                // Get next session with this trainer
                $nextSession = $client->clientSchedules
                    ->where('date', '>=', now()->format('Y-m-d'))
                    ->where('status', '!=', 'cancelled')
                    ->sortBy('date')
                    ->first();

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'email' => $client->email,
                    'phone' => $client->phone,
                    'profile_image' => $client->profile_image ? asset('storage/' . $client->profile_image) : null,
                    'fitness_goals' => $client->goals->where('status', 1)->pluck('name')->implode(', '),
                    'goals_count' => $client->goals->where('status', 1)->count(),
                    'next_session' => $nextSession ? [
                        'date' => $nextSession->date,
                        'status' => $nextSession->status
                    ] : null,
                    'total_sessions' => $client->clientSchedules->count(),
                    'member_since' => $client->created_at->format('M Y'),
                    'created_at' => $client->created_at->toISOString()
                ];
            });

            // Prepare pagination data
            $paginationData = [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
                'from' => $clients->firstItem(),
                'to' => $clients->lastItem()
            ];

            Log::info('Clients retrieved by trainer', [
                'trainer_id' => $trainer->id,
                'search_params' => $request->only(['search', 'fitness_level', 'sort_by', 'sort_order']),
                'results_count' => $clients->count()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Clients retrieved successfully',
                'data' => $transformedClients,
                'pagination' => $paginationData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve clients via trainer API: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'request_params' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve clients. Please try again.'
            ], 500);
        }
    }

    /**
     * Add a new client by trainer
     * 
     * Creates a new client account with the provided information
     * Only trainers can add clients to the system
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate trainer authentication
            $trainer = Auth::user();
            if (!$trainer || !$trainer->isTrainerRole()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only trainers can add clients.'
                ], 403);
            }

            // Validate request data
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email|max:255',
                'phone' => 'required|string|max:20|unique:users,phone',
                'password' => 'required|string|min:8',
                'fitness_goals' => 'nullable', // Accepts string or array
                'fitness_level' => 'nullable|string|in:Beginner,Intermediate,Advanced',
                'health_considerations' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Create client user account
            $password = $request->password;
            $clientData = [
                'name' => trim($request->first_name . ' ' . $request->last_name),
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => 'client',
                'password' => Hash::make($password),
                'email_verified_at' => now(), // Auto-verify trainer created clients
            ];

            $client = User::create($clientData);

            // Create UserHealthProfile
            if (method_exists($client, 'healthProfile')) {
                $client->healthProfile()->create([
                    'fitness_level' => $request->fitness_level,
                    'chronic_conditions' => $request->health_considerations ? [$request->health_considerations] : [], // Store as array
                    'allergies' => [] // Default empty array
                ]);
            }

            // Create fitness goals if provided
            if ($request->filled('fitness_goals')) {
                $goals = $request->fitness_goals;
                
                // Handle comma-separated string input
                if (is_string($goals)) {
                    $goals = array_map('trim', explode(',', $goals));
                }
                
                if (is_array($goals)) {
                    foreach ($goals as $goalName) {
                        if (!empty($goalName) && method_exists($client, 'goals')) {
                            $client->goals()->create([
                                'name' => $goalName,
                                'status' => 1
                            ]);
                        }
                    }
                }
            }

            // Create Subscription (Link Client to Trainer)
            TrainerSubscription::create([
                'trainer_id' => $trainer->id,
                'client_id' => $client->id,
                'status' => 'active',
                'subscribed_at' => now()
            ]);

            // Send invitation email
            try {
                Mail::to($client->email)->send(new ClientInvitation($client, $trainer, $password));
            } catch (\Exception $e) {
                Log::warning('Failed to send invitation email to new client: ' . $e->getMessage());
                // Continue without failing the request, as the client is created
            }

            DB::commit();

            // Log client creation
            Log::info('New client added by trainer', [
                'trainer_id' => $trainer->id,
                'trainer_name' => $trainer->name,
                'client_id' => $client->id,
                'client_name' => $client->name,
                'client_email' => $client->email
            ]);

            // Prepare response data
            $responseData = [
                'id' => $client->id,
                'name' => $client->name,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $client->email,
                'phone' => $client->phone,
                'fitness_goals' => $request->fitness_goals,
                'fitness_level' => $request->fitness_level,
                'health_considerations' => $request->health_considerations,
                'role' => $client->role,
                'status' => 'active',
                'created_at' => $client->created_at->toISOString(),
                'member_since' => $client->created_at->format('M Y')
            ];

            return response()->json([
                'success' => true,
                'message' => 'Client added successfully',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            // Check for duplicate entry error (SQLSTATE 23000 / Code 1062)
            if ($e instanceof \Illuminate\Database\QueryException && $e->getCode() == 23000) {
                if (str_contains($e->getMessage(), 'users_phone_unique')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => [
                            'phone' => ['The phone has already been taken.']
                        ]
                    ], 422);
                }
                if (str_contains($e->getMessage(), 'users_email_unique')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => [
                            'email' => ['The email has already been taken.']
                        ]
                    ], 422);
                }
            }

            Log::error('Failed to add client via trainer API: ' . $e->getMessage(), [
                'trainer_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add client. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
