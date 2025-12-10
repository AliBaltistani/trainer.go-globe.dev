<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Events\MessageRead;

class MessageController extends Controller
{
    /**
     * Get messages for a conversation
     */
    public function index($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        // Authorization check
        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this conversation.'
            ], 403);
        }

        // Get messages with pagination (newest first)
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Authorization check
        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $mediaUrl = null;
        $messageType = 'text';

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('chat_files', 'public');
            $mediaUrl = 'storage/' . $path;
            $messageType = $request->file('file')->isValid() && str_starts_with($request->file('file')->getMimeType(), 'image/') ? 'image' : 'file';
        }

        $message = $conversation->messages()->create([
            'sender_id' => Auth::id(),
            'receiver_id' => (Auth::id() == $conversation->trainer_id) ? $conversation->client_id : $conversation->trainer_id,
            'message' => $request->message,
            'media_url' => $mediaUrl,
            'message_type' => $messageType,
        ]);

        // Update last message
        $conversation->update(['last_message_id' => $message->id]);

        // Load sender for broadcast
        $message->load('sender');

        // Broadcast event
        broadcast(new MessageSent($message))->toOthers();

        // Send Notification (Logic to be added)
        // trigger_notification($message);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
            'data' => $message
        ]);
    }

    /**
     * Mark messages as read
     */
    public function read(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        // Mark all messages where receiver is current user as read
        $updated = Message::where('conversation_id', $conversation->id)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            broadcast(new MessageRead($conversation->id, Auth::id()))->toOthers();
        }

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
            'updated_count' => $updated
        ]);
    }
}
