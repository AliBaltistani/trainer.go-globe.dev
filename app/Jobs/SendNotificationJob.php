<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data
     * [
     *    'type' => 'tokens' | 'topic',
     *    'target' => ['token1', 'token2'] | 'topic_name',
     *    'title' => 'Title',
     *    'message' => 'Message',
     *    'payload' => [],
     *    'log_id' => 1 // Optional, ID of NotificationLog to update
     * ]
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(FcmService $fcmService): void
    {
        $type = $this->data['type'] ?? 'tokens';
        $target = $this->data['target'];
        $title = $this->data['title'];
        $message = $this->data['message'];
        $payload = $this->data['payload'] ?? [];
        $logId = $this->data['log_id'] ?? null;

        try {
            $response = [];
            if ($type === 'topic') {
                $response = $fcmService->sendToTopic($target, $title, $message, $payload);
            } else {
                // Default to tokens
                $response = $fcmService->sendToTokens($target, $title, $message, $payload);
            }

            // Update Log if exists
            if ($logId) {
                $log = NotificationLog::find($logId);
                if ($log) {
                    // Check for success in response
                    // FCM response format: { "success": 1, "failure": 0, ... } or { "message_id": ... } for topics
                    $status = 'sent';
                    
                    if (isset($response['failure']) && $response['failure'] > 0 && $response['success'] == 0) {
                         $status = 'failed';
                    }
                    // For topic messaging, success usually returns a message_id. If error, it throws or returns error structure.
                    if (isset($response['error'])) {
                        $status = 'failed';
                    }

                    $log->update(['status' => $status]);
                }
            }

        } catch (\Exception $e) {
            Log::error('SendNotificationJob Failed: ' . $e->getMessage());
            
            if ($logId) {
                 $log = NotificationLog::find($logId);
                 if ($log) {
                     $log->update(['status' => 'failed']);
                 }
            }
            
            // Optionally release back to queue if needed, or just fail
            $this->fail($e);
        }
    }
}
