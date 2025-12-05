<?php

namespace App\Http\Controllers\Api\Trainer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClientWeightLog;
use App\Models\ClientActivityLog;
use App\Models\ClientProgress;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientProfileController extends Controller
{
    /**
     * Check access permission
     */
    private function checkAccess($clientId)
    {
        $user = Auth::user();
        if (!$user || !$user->isTrainerRole()) {
            return false;
        }
        
        return $user->hasActiveClient($clientId);
    }

    /**
     * Get Client Basic Info (Header)
     */
    public function getHeader($id)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['message' => 'Unauthorized access to client data'], 403);
        }

        $client = User::where('id', $id)->where('role', 'client')->firstOrFail();
        
        $primaryGoal = $client->goals()->where('status', 1)->first();

        return response()->json([
            'data' => [
                'id' => $client->id,
                'name' => $client->name,
                'title' => $client->designation ?? 'Client', // Fallback title
                'profile_image_url' => $client->profile_image ? asset('storage/' . $client->profile_image) : null,
                'joined_at_human' => $client->created_at->diffForHumans(),
                'primary_goal' => $primaryGoal ? $primaryGoal->name : 'No active goal',
            ]
        ]);
    }

    /**
     * Get Overview Tab Data
     */
    public function getOverview($id)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['message' => 'Unauthorized access to client data'], 403);
        }

        $client = User::findOrFail($id);
        
        // 1. Metrics
        $workoutsCompleted = ClientProgress::where('client_id', $id)
            ->where('status', 'completed')
            ->distinct('completed_at') // Approximation of sessions if date is stored with time
            ->count();

        // Average Rating - Assuming we have a way to rate, using placeholder for now or if Feedback model exists
        $averageRating = 0; // Placeholder, replace with actual logic if Feedback model exists

        $totalSessions = ClientProgress::where('client_id', $id)
            ->distinct(DB::raw('DATE(completed_at)'))
            ->count('completed_at');

        // 2. Charts - Weight Over Time (Last 3 months)
        $threeMonthsAgo = Carbon::now()->subMonths(3);
        $weightLogs = ClientWeightLog::where('user_id', $id)
            ->where('logged_at', '>=', $threeMonthsAgo)
            ->orderBy('logged_at', 'asc')
            ->get();
        
        $weightDataPoints = $weightLogs->map(function ($log) {
            return [
                'date' => $log->logged_at->format('Y-m-d'),
                'value' => $log->weight
            ];
        });
        
        // Calculate change
        $currentWeight = $weightLogs->last()?->weight ?? 0;
        $startWeight = $weightLogs->first()?->weight ?? 0;
        $weightChange = $currentWeight - $startWeight;
        $weightChangePercent = $startWeight > 0 ? ($weightChange / $startWeight) * 100 : 0;

        // 3. Charts - Workout Frequency (Last 3 months)
        $workoutFrequency = ClientProgress::where('client_id', $id)
            ->where('completed_at', '>=', $threeMonthsAgo)
            ->select(DB::raw('MONTH(completed_at) as month'), DB::raw('COUNT(*) as count'))
            ->groupBy('month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => Carbon::create()->month($item->month)->format('M'),
                    'count' => $item->count
                ];
            });

        // 4. Health History
        $healthProfile = $client->healthProfile;

        // 5. Progress Indicators
        $progressIndicators = [
            [
                'label' => 'Workout Completion',
                'value' => "Completed {$workoutsCompleted} workouts",
                'icon' => 'check_circle'
            ],
            [
                'label' => 'Weight Change',
                'value' => ($weightChange > 0 ? '+' : '') . number_format($weightChange, 1) . ' ' . ($weightLogs->last()?->unit ?? 'lbs'),
                'icon' => 'scale'
            ]
        ];

        // 6. Assigned Programs
        // Assuming Program model has relationship with client
        // Or through TrainerSubscription -> Program
        // For now, using simple fetch if Program has client_id
        $assignedPrograms = \App\Models\Program::where('client_id', $id)
            ->select('id', 'name', 'created_at', 'updated_at')
            ->get()
            ->map(function($p) {
                 return [
                     'id' => $p->id,
                     'name' => $p->name,
                     'icon' => 'dumbbell' // Default icon
                 ];
            });

        // 7. Trainer Notes
        $latestNote = $client->clientNotes()->latest()->first();

        return response()->json([
            'data' => [
                'metrics' => [
                    'workouts_completed' => $workoutsCompleted,
                    'average_rating' => $averageRating,
                    'total_sessions' => $totalSessions
                ],
                'charts' => [
                    'weight_over_time' => [
                        'current_change' => ($weightChange > 0 ? '+' : '') . number_format($weightChange, 1) . ' ' . ($weightLogs->last()?->unit ?? 'lbs'),
                        'percentage_change' => round($weightChangePercent, 1),
                        'data_points' => $weightDataPoints
                    ],
                    'workout_frequency' => [
                        'current_change' => '+0%', // Logic to calculate % change from previous period needed
                        'data_points' => $workoutFrequency
                    ]
                ],
                'goals' => $client->goals->map(function ($goal) {
                    return [
                        'id' => $goal->id,
                        'name' => $goal->name,
                        'status' => $goal->status ? 'Active' : 'Completed',
                        'achieved_at' => $goal->achieved_at,
                        'icon' => 'trophy'
                    ];
                }),
                'health_history' => [
                    'allergies' => $healthProfile?->allergies ?? [],
                    'conditions' => $healthProfile?->chronic_conditions ?? []
                ],
                'progress_indicators' => $progressIndicators,
                'assigned_programs' => $assignedPrograms,
                'trainer_notes' => [
                    'latest_note' => $latestNote?->note,
                    'updated_at' => $latestNote?->created_at
                ]
            ]
        ]);
    }

    /**
     * Get Weight Tab Data
     */
    public function getWeight($id)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['message' => 'Unauthorized access to client data'], 403);
        }

        $weightLogs = ClientWeightLog::where('user_id', $id)
            ->orderBy('logged_at', 'asc')
            ->get();

        $startingWeight = $weightLogs->first();
        $currentWeight = $weightLogs->last();

        $totalChange = 0;
        if ($startingWeight && $currentWeight) {
            $totalChange = $currentWeight->weight - $startingWeight->weight;
        }

        // Group by week for graph
        $graphData = $weightLogs->groupBy(function($date) {
            return Carbon::parse($date->logged_at)->format('W');
        })->map(function ($row) {
            return [
                'week' => 'Week ' . Carbon::parse($row->first()->logged_at)->format('W'),
                'value' => $row->avg('weight')
            ];
        })->values();

        // Get goal weight
        $goalWeight = Goal::where('user_id', $id)
            ->whereNotNull('target_value')
            ->whereIn('metric_unit', ['lbs', 'kg']) // Assuming these are weight units
            ->first();

        return response()->json([
            'data' => [
                'stats' => [
                    'starting_weight' => $startingWeight ? $startingWeight->weight . ' ' . $startingWeight->unit : 'N/A',
                    'current_weight' => $currentWeight ? $currentWeight->weight . ' ' . $currentWeight->unit : 'N/A',
                    'total_change' => ($totalChange > 0 ? '+' : '') . number_format($totalChange, 1) . ' ' . ($currentWeight?->unit ?? 'lbs')
                ],
                'graph_data' => [
                    'label' => 'Weight Over Time',
                    'current_value' => $currentWeight ? $currentWeight->weight . ' ' . $currentWeight->unit : '0',
                    'change_percentage' => 0, // Calculate if needed
                    'points' => $graphData
                ],
                'summary' => [
                    'personal_best' => $weightLogs->min('weight') . ' ' . ($currentWeight?->unit ?? 'lbs'),
                    'goal' => $goalWeight ? $goalWeight->target_value . ' ' . $goalWeight->metric_unit : 'N/A'
                ]
            ]
        ]);
    }

    /**
     * Store New Weight Log
     */
    public function storeWeight(Request $request, $id)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['message' => 'Unauthorized access to client data'], 403);
        }

        $request->validate([
            'weight' => 'required|numeric',
            'unit' => 'required|in:lbs,kg',
            'date' => 'required|date'
        ]);

        $log = ClientWeightLog::create([
            'user_id' => $id,
            'weight' => $request->weight,
            'unit' => $request->unit,
            'logged_at' => $request->date,
            'notes' => $request->notes
        ]);

        return response()->json(['message' => 'Weight logged successfully', 'data' => $log]);
    }

    /**
     * Get Time Metrics (Running/Cardio)
     */
    public function getTimeMetrics($id)
    {
        if (!$this->checkAccess($id)) {
            return response()->json(['message' => 'Unauthorized access to client data'], 403);
        }

        // 1. Running
        $runningLogs = ClientActivityLog::where('user_id', $id)
            ->where('activity_type', 'running')
            ->orderBy('performed_at', 'asc')
            ->get();

        $runningGraph = $runningLogs->map(function($log) {
            return [
                'day' => $log->performed_at->format('D'),
                'value' => $log->duration_seconds / 60 // Minutes
            ];
        });

        $runningBest = $runningLogs->max('duration_seconds');
        $runningAvg = $runningLogs->avg('duration_seconds');

        // 2. Swimming
        $swimmingLogs = ClientActivityLog::where('user_id', $id)
            ->where('activity_type', 'swimming')
            ->orderBy('performed_at', 'asc')
            ->get();

         // ... similar logic for swimming

        return response()->json([
            'data' => [
                'running' => [
                    'current_time' => $runningLogs->last() ? round($runningLogs->last()->duration_seconds / 60) . ' min' : '0 min',
                    'change_percentage' => '+0%', // Calculate
                    'graph_data' => $runningGraph,
                    'stats' => [
                        'best_time' => $runningBest ? round($runningBest / 60) . ' min' : '0 min',
                        'average_time' => $runningAvg ? round($runningAvg / 60) . ' min' : '0 min',
                        'trend' => 'Stable'
                    ]
                ],
                // Add swimming, etc.
            ]
        ]);
    }
}
