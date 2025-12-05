<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use App\Models\NotificationLog;
use App\Models\User;
use App\Jobs\SendNotificationJob;
use App\Models\TrainerSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PushNotificationController extends Controller
{
    /**
     * Save or update device token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
            'platform' => 'required|in:android,ios,web',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $token = DeviceToken::updateOrCreate(
            [
                'device_token' => $request->device_token,
            ],
            [
                'user_id' => $user->id,
                'platform' => $request->platform,
            ]
        );

        return response()->json([
            'message' => 'Device token saved successfully',
            'data' => $token
        ]);
    }

    /**
     * Remove device token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDeviceToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        
        // Only allow deleting own token or if logic permits
        // Here we delete based on token string and user ownership
        $deleted = DeviceToken::where('device_token', $request->device_token)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Device token removed successfully']);
        }

        return response()->json(['message' => 'Token not found or access denied'], 404);
    }

    /**
     * Send notification to a specific user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendToUser(Request $request)
    {
        $currentUser = Auth::user();
        
        if (!$currentUser) {
             return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string',
            'message' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $targetUserId = $request->user_id;

        // Access Control
        if ($currentUser->role === 'admin') {
            // Admin can send to anyone
        } elseif ($currentUser->role === 'trainer') {
            // Trainer can only send to their subscribed clients
            $isSubscribed = TrainerSubscription::where('trainer_id', $currentUser->id)
                ->where('client_id', $targetUserId)
                ->where('status', 'active')
                ->exists();

            if (!$isSubscribed) {
                 return response()->json(['message' => 'Access denied. You can only send notifications to your active clients.'], 403);
            }
        } else {
             // Clients or others cannot use this endpoint directly
             return response()->json(['message' => 'Access denied.'], 403);
        }

        $targetUser = User::find($targetUserId);
        $tokens = $targetUser->deviceTokens()->pluck('device_token')->toArray();

        if (empty($tokens)) {
            return response()->json(['message' => 'User has no registered devices'], 404);
        }

        // Create Log
        $log = NotificationLog::create([
            'user_id' => $targetUser->id,
            'title' => $request->title,
            'message' => $request->message,
            'payload' => $request->payload,
            'status' => 'pending',
        ]);

        // Dispatch Job
        SendNotificationJob::dispatch([
            'type' => 'tokens',
            'target' => $tokens,
            'title' => $request->title,
            'message' => $request->message,
            'payload' => $request->payload ?? [],
            'log_id' => $log->id,
        ]);

        return response()->json([
            'message' => 'Notification queued successfully',
            'log_id' => $log->id
        ]);
    }

    /**
     * Broadcast notification.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function broadcast(Request $request)
    {
        $currentUser = Auth::user();

        if (!$currentUser || $currentUser->role !== 'admin') {
             return response()->json(['message' => 'Access denied. Only Admins can broadcast.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'message' => 'required|string',
            'target' => 'required|in:trainer,client,all',
            'payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $target = $request->target;
        $topics = [];

        if ($target === 'all') {
            $topics = ['trainers', 'clients'];
        } else {
            // 'trainer' -> 'trainers', 'client' -> 'clients'
            $topics = [$target . 's']; 
        }

        foreach ($topics as $topic) {
            // Create a general log for broadcast? 
            // We might not want to create thousands of logs. 
            // Let's create one log entry with null user_id to represent broadcast
            
            $log = NotificationLog::create([
                'user_id' => null,
                'title' => $request->title . " (Broadcast: $topic)",
                'message' => $request->message,
                'payload' => $request->payload,
                'status' => 'pending',
            ]);

            SendNotificationJob::dispatch([
                'type' => 'topic',
                'target' => $topic,
                'title' => $request->title,
                'message' => $request->message,
                'payload' => $request->payload ?? [],
                'log_id' => $log->id,
            ]);
        }

        return response()->json([
            'message' => 'Broadcast queued successfully'
        ]);
    }

    /**
     * Get notification history.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(Request $request)
    {
        $user = Auth::user();
        
        $logs = NotificationLog::where('user_id', $user->id)
            ->latest()
            ->paginate(20);

        return response()->json($logs);
    }
}
