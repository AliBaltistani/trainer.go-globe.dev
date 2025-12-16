<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\TrainerSubscription;
use App\Models\User;
use App\Models\BookingSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * Unified Subscription API Controller
 * 
 * Handles subscription-related operations for both trainers and clients
 * Provides endpoints to get subscribed clients (for trainers) and subscribed trainers (for clients)
 * 
 * @package     Laravel CMS App
 * @subpackage  Controllers\Api
 * @category    Subscription API
 * @author      Go Globe CMS Team
 * @since       1.0.0
 */
class SubscriptionController extends ApiBaseController
{
    /**
     * Get subscribed clients (for trainers) or subscribed trainers (for clients)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            
            if ($user->role === 'trainer') {
                return $this->getSubscribedClients($request);
            } elseif ($user->role === 'client') {
                return $this->getSubscribedTrainers($request);
            }
            
            return $this->sendError('Unauthorized', ['error' => 'Invalid user role'], 403);
        } catch (\Exception $e) {
            Log::error('SubscriptionController@index failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->sendError('Retrieval Failed', ['error' => 'Unable to retrieve subscriptions'], 500);
        }
    }

    /**
     * Get subscribed clients for authenticated trainer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    private function getSubscribedClients(Request $request): JsonResponse
    {
        $trainer = Auth::user();
        
        $search = trim((string) $request->get('search', ''));
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        
        $query = TrainerSubscription::where('trainer_id', $trainer->id)
            ->where('status', 'active')
            ->with(['client:id,name,email,phone,profile_image']);
        
        if ($search !== '') {
            $query->whereHas('client', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $subscriptions = $query->orderBy('subscribed_at', 'desc')->paginate($perPage);
        
        $data = $subscriptions->getCollection()->map(function ($sub) {
            $client = $sub->client;
            $image = null;
            
            if ($client && $client->profile_image && file_exists(public_path('storage/' . $client->profile_image))) {
                $image = asset('storage/' . $client->profile_image);
            }
            
            return [
                'subscription_id' => $sub->id,
                'client_id' => $sub->client_id,
                'client_name' => $client ? $client->name : null,
                'client_email' => $client ? $client->email : null,
                'client_phone' => $client ? $client->phone : null,
                'client_image' => $image,
                'status' => $sub->status,
                'subscribed_at' => $sub->subscribed_at ? $sub->subscribed_at->toISOString() : null,
            ];
        });
        
        return $this->sendResponse([
            'subscriptions' => $data,
            'pagination' => [
                'total' => $subscriptions->total(),
                'per_page' => $subscriptions->perPage(),
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage()
            ]
        ], 'Subscribed clients retrieved successfully');
    }

    /**
     * Get subscribed trainers for authenticated client
     * 
     * @param Request $request
     * @return JsonResponse
     */
    private function getSubscribedTrainers(Request $request): JsonResponse
    {
        $client = Auth::user();
        
        $search = trim((string) $request->get('search', ''));
        $perPage = min(max((int) $request->get('per_page', 20), 1), 100);
        
        $query = TrainerSubscription::where('client_id', $client->id)
            ->where('status', 'active')
            ->with(['trainer:id,name,email,phone,profile_image']);
        
        if ($search !== '') {
            $query->whereHas('trainer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $subscriptions = $query->orderBy('subscribed_at', 'desc')->paginate($perPage);
        
        $data = $subscriptions->getCollection()->map(function ($sub) {
            $trainer = $sub->trainer;
            $image = null;
            
            if ($trainer && $trainer->profile_image && file_exists(public_path('storage/' . $trainer->profile_image))) {
                $image = asset('storage/' . $trainer->profile_image);
            }
            
            // Get booking settings for the trainer
            $bookingSettings = BookingSetting::where('trainer_id', $trainer->id)->first();
            
            return [
                'subscription_id' => $sub->id,
                'trainer_id' => $sub->trainer_id,
                'trainer_name' => $trainer ? $trainer->name : null,
                'trainer_email' => $trainer ? $trainer->email : null,
                'trainer_phone' => $trainer ? $trainer->phone : null,
                'trainer_image' => $image,
                'status' => $sub->status,
                'subscribed_at' => $sub->subscribed_at ? $sub->subscribed_at->toISOString() : null,
                'booking_settings' => $bookingSettings ? [
                    'allow_self_booking' => $bookingSettings->allow_self_booking,
                    'require_approval' => $bookingSettings->require_approval,
                    'allow_weekend_booking' => $bookingSettings->allow_weekend_booking,
                    'advance_booking_days' => $bookingSettings->advance_booking_days,
                    'cancellation_hours' => $bookingSettings->cancellation_hours,
                ] : [
                    'allow_self_booking' => true, // Default
                    'require_approval' => false, // Default
                    'allow_weekend_booking' => true, // Default
                    'advance_booking_days' => 30, // Default
                    'cancellation_hours' => 24, // Default
                ],
            ];
        });
        
        return $this->sendResponse([
            'subscriptions' => $data,
            'pagination' => [
                'total' => $subscriptions->total(),
                'per_page' => $subscriptions->perPage(),
                'current_page' => $subscriptions->currentPage(),
                'last_page' => $subscriptions->lastPage()
            ]
        ], 'Subscribed trainers retrieved successfully');
    }

    /**
     * Check if client is subscribed to trainer
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkSubscription(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'trainer_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->sendError('Validation Error', $validator->errors(), 422);
            }

            $user = Auth::user();
            
            if ($user->role !== 'client') {
                return $this->sendError('Unauthorized', ['error' => 'Client access required'], 403);
            }

            $trainer = User::find($request->trainer_id);
            if (!$trainer || $trainer->role !== 'trainer') {
                return $this->sendError('Invalid Trainer', ['error' => 'Trainer not found'], 404);
            }

            $subscription = TrainerSubscription::where('client_id', $user->id)
                ->where('trainer_id', $trainer->id)
                ->where('status', 'active')
                ->first();

            $bookingSettings = BookingSetting::where('trainer_id', $trainer->id)->first();

            return $this->sendResponse([
                'is_subscribed' => $subscription !== null,
                'booking_settings' => $bookingSettings ? [
                    'allow_self_booking' => $bookingSettings->allow_self_booking,
                    'require_approval' => $bookingSettings->require_approval,
                    // 'allow_weekend_booking' => $bookingSettings->allow_weekend_booking,
                    // 'advance_booking_days' => $bookingSettings->advance_booking_days,
                    // 'cancellation_hours' => $bookingSettings->cancellation_hours,
                ] : [
                    'allow_self_booking' => false,
                    'require_approval' => false,
                    // 'allow_weekend_booking' => true,
                    // 'advance_booking_days' => 30,
                    // 'cancellation_hours' => 24,
                ],
            ], 'Subscription status retrieved successfully');
        } catch (\Exception $e) {
            Log::error('SubscriptionController@checkSubscription failed: ' . $e->getMessage());
            return $this->sendError('Check Failed', ['error' => 'Unable to check subscription'], 500);
        }
    }
}

