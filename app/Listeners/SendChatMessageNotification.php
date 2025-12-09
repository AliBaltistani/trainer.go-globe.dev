<?php

namespace App\Listeners;

use App\Events\MessageSent;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendChatMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(MessageSent $event): void
    {
        $message = $event->message;
        
        // Ensure relationships are loaded
        if (!$message->relationLoaded('sender')) {
            $message->load('sender');
        }
        if (!$message->relationLoaded('receiver')) {
            $message->load('receiver');
        }

        $sender = $message->sender;
        $receiver = $message->receiver;

        $content = $message->message;
        if ($message->message_type !== 'text') {
            $content = $sender->name . ' sent a ' . $message->message_type;
        }
        
        // Fallback if message is empty (e.g. only file)
        if (empty($content)) {
             $content = $sender->name . ' sent a message';
        }

        $this->notificationService->notifyMessage($receiver, $sender, $content);
    }
}
