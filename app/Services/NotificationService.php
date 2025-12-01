<?php

namespace App\Services;

use App\Models\User;
use App\Models\NotificationLog;
use App\Jobs\SendNotificationJob;

class NotificationService
{
    /**
     * Send a notification to a user.
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param array $payload
     * @return void
     */
    public function sendNotification(User $user, string $title, string $message, array $payload = [])
    {
        // Create Log
        $log = NotificationLog::create([
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'payload' => $payload,
            'status' => 'pending',
        ]);

        $tokens = $user->deviceTokens()->pluck('device_token')->toArray();

        if (empty($tokens)) {
            return;
        }
        // Dispatch Job
        SendNotificationJob::dispatch([
            'type' => 'tokens',
            'target' => $tokens ?? '',
            'title' => $title,
            'message' => $message,
            'payload' => $payload,
            'log_id' => $log->id,
        ]);
    }

    /**
     * Notify trainer when a client subscribes.
     */
    public function notifySubscription(User $trainer, User $client)
    {
        $title = "New Client Subscription";
        $message = "{$client->name} has subscribed to your training program.";
        $payload = [
            'type' => 'subscription',
            'client_id' => $client->id,
            'redirect' => 'ClientProfileScreen'
        ];

        $this->sendNotification($trainer, $title, $message, $payload);
    }

    /**
     * Notify client when a workout is scheduled.
     */
    public function notifyWorkoutSchedule(User $client, $workoutDetails)
    {
        $title = "New Workout Scheduled";
        $message = "A new workout '{$workoutDetails['name']}' has been scheduled for you.";
        $payload = [
            'type' => 'workout_update',
            'workout_id' => $workoutDetails['id'],
            'redirect' => 'WorkoutScreen'
        ];

        $this->sendNotification($client, $title, $message, $payload);
    }

    /**
     * Notify user about payment status.
     */
    public function notifyPaymentStatus(User $user, string $status, $transactionId)
    {
        $title = "Payment Update";
        $message = "Your payment status is now: $status.";
        $payload = [
            'type' => 'payment_update',
            'transaction_id' => $transactionId,
            'status' => $status,
            'redirect' => 'PaymentHistoryScreen'
        ];

        $this->sendNotification($user, $title, $message, $payload);
    }

    /**
     * Notify user about a new session/booking.
     */
    public function notifyNewSession(User $user, $sessionDetails)
    {
        $title = "New Session Booked";
        $message = "You have a new session booked on {$sessionDetails['date']}.";
        $payload = [
            'type' => 'session_booking',
            'session_id' => $sessionDetails['id'],
            'redirect' => 'SessionScreen'
        ];

        $this->sendNotification($user, $title, $message, $payload);
    }

    /**
     * Notify receiver of a new message.
     */
    public function notifyMessage(User $receiver, User $sender, string $messageContent)
    {
        $title = "New Message from {$sender->name}";
        $message = $messageContent; // Truncate if too long?
        $payload = [
            'type' => 'chat_message',
            'sender_id' => $sender->id,
            'redirect' => 'ChatScreen'
        ];

        $this->sendNotification($receiver, $title, $message, $payload);
    }
}
