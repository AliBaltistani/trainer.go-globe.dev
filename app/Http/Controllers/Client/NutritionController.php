<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\NutritionPlan;
use App\Models\ClientNutritionTarget;
use App\Models\FoodDiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

/**
 * ClientNutritionController
 * 
 * Handles client web-based nutrition management
 * Clients can view assigned plans, set their own targets, and track food diary
 */
class NutritionController extends Controller
{
    /**
     * Display list of assigned nutrition plans
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $clientId = Auth::id();
            
            $plans = NutritionPlan::where('client_id', $clientId)
                ->where('status', 'active')
                ->with(['trainer:id,name,email,profile_image', 'recommendations', 'dailyMacros', 'meals'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return view('client.nutrition.plans.index', compact('plans'));
            
        } catch (\Exception $e) {
            Log::error('Client failed to load nutrition plans: ' . $e->getMessage(), [
                'client_id' => Auth::id()
            ]);
            
            return redirect()->route('client.dashboard')->with('error', 'Failed to load nutrition plans');
        }
    }

    /**
     * Display a specific nutrition plan
     * 
     * @param int $id Plan ID
     * @return View|RedirectResponse
     */
    public function show(int $id): View|RedirectResponse
    {
        try {
            $clientId = Auth::id();
            
            $plan = NutritionPlan::where('client_id', $clientId)
                ->with([
                    'trainer:id,name,email,profile_image',
                    'meals' => function($query) {
                        $query->orderBy('sort_order');
                    },
                    'recipes' => function($query) {
                        $query->orderBy('sort_order');
                    },
                    'dailyMacros',
                    'restrictions',
                    'recommendations'
                ])
                ->findOrFail($id);
            
            // Get client's own targets
            $clientTargets = ClientNutritionTarget::where('client_id', $clientId)->first();
            
            // Calculate plan statistics
            $stats = [
                'total_meals' => $plan->meals->count(),
                'total_recipes' => $plan->recipes->count(),
                'total_calories' => $plan->meals->sum('calories_per_serving'),
            ];
            
            return view('client.nutrition.plans.show', compact('plan', 'clientTargets', 'stats'));
            
        } catch (\Exception $e) {
            Log::error('Client failed to load nutrition plan: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'plan_id' => $id
            ]);
            
            return redirect()->route('client.nutrition.plans.index')->with('error', 'Nutrition plan not found');
        }
    }

    /**
     * Display client nutrition targets management page
     * 
     * @return View
     */
    public function targets(): View
    {
        try {
            $clientId = Auth::id();
            
            // Get active nutrition plan with trainer recommendations
            $plan = NutritionPlan::where('client_id', $clientId)
                ->where('status', 'active')
                ->with(['recommendations', 'trainer:id,name,email'])
                ->first();
            
            // Get client's own targets
            $clientTargets = ClientNutritionTarget::where('client_id', $clientId)->first();
            
            return view('client.nutrition.targets', compact('plan', 'clientTargets'));
            
        } catch (\Exception $e) {
            Log::error('Client failed to load nutrition targets: ' . $e->getMessage(), [
                'client_id' => Auth::id()
            ]);
            
            return redirect()->route('client.dashboard')->with('error', 'Failed to load nutrition targets');
        }
    }

    /**
     * Update client's nutrition targets
     * 
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function updateTargets(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $clientId = Auth::id();
            
            // Validation rules
            $rules = [
                'target_calories' => 'required|numeric|min:500|max:10000',
                'protein' => 'required|numeric|min:0|max:1000',
                'carbs' => 'required|numeric|min:0|max:1000',
                'fats' => 'required|numeric|min:0|max:1000'
            ];
            
            $validator = Validator::make($request->all(), $rules);
            
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors()
                    ], 422);
                }
                
                return back()->withErrors($validator)->withInput();
            }
            
            // Update or create client targets
            $target = ClientNutritionTarget::updateOrCreate(
                ['client_id' => $clientId],
                [
                    'target_calories' => $request->target_calories,
                    'protein' => $request->protein,
                    'carbs' => $request->carbs,
                    'fats' => $request->fats
                ]
            );
            
            // Log the update
            Log::info('Client updated nutrition targets', [
                'client_id' => $clientId,
                'targets' => $target->toArray()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nutrition targets updated successfully',
                    'data' => [
                        'client_targets' => [
                            'id' => $target->id,
                            'target_calories' => $target->target_calories,
                            'protein' => $target->protein,
                            'carbs' => $target->carbs,
                            'fats' => $target->fats,
                            'updated_at' => $target->updated_at
                        ]
                    ]
                ]);
            }
            
            return redirect()->route('client.nutrition.targets')
                           ->with('success', 'Nutrition targets updated successfully');
            
        } catch (\Exception $e) {
            Log::error('Client failed to update nutrition targets: ' . $e->getMessage(), [
                'client_id' => Auth::id(),
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update nutrition targets'
                ], 500);
            }
            
            return back()->with('error', 'Failed to update nutrition targets')->withInput();
        }
    }

    /**
     * Delete client's nutrition targets
     * 
     * @return RedirectResponse|JsonResponse
     */
    public function deleteTargets(Request $request): RedirectResponse|JsonResponse
    {
        try {
            $clientId = Auth::id();
            
            $target = ClientNutritionTarget::where('client_id', $clientId)->first();
            
            if (!$target) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No nutrition targets found'
                    ], 404);
                }
                
                return back()->with('error', 'No nutrition targets found');
            }
            
            $target->delete();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nutrition targets deleted successfully'
                ]);
            }
            
            return redirect()->route('client.nutrition.targets')
                           ->with('success', 'Nutrition targets deleted successfully');
            
        } catch (\Exception $e) {
            Log::error('Client failed to delete nutrition targets: ' . $e->getMessage(), [
                'client_id' => Auth::id()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete nutrition targets'
                ], 500);
            }
            
            return back()->with('error', 'Failed to delete nutrition targets');
        }
    }
}

