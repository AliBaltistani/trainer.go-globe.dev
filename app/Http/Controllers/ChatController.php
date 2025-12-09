<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Events\MessageSent;
use App\Events\MessageRead;

class ChatController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Fetch conversations where user is trainer or client
        $conversations = Conversation::where('trainer_id', $user->id)
            ->orWhere('client_id', $user->id)
            ->with(['trainer', 'client', 'lastMessage'])
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                      ->where('is_read', false);
            }])
            ->orderBy('updated_at', 'desc')
            ->get();

        $potentialChatPartners = collect();

        // 1. Get clients who have active subscription with this user (acting as trainer)
        $clients = $user->subscriptionsAsTrainer()
            ->where('status', 'active')
            ->with('client')
            ->get()
            ->pluck('client')
            ->filter();
            
        // 2. Get trainers whom this user has active subscription with (acting as client)
        $trainers = $user->subscriptionsAsClient()
            ->where('status', 'active')
            ->with('trainer')
            ->get()
            ->pluck('trainer')
            ->filter();

        // Merge and unique
        $potentialChatPartners = $clients->merge($trainers)->unique('id');

        // Calculate total unread messages and unread conversations count
        $totalUnread = $conversations->sum('unread_count');
        $unreadConversationsCount = $conversations->where('unread_count', '>', 0)->count();

        return view('chat', compact('conversations', 'potentialChatPartners', 'clients', 'trainers', 'totalUnread', 'unreadConversationsCount'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'partner_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $partnerId = $request->partner_id;
        $partner = User::findOrFail($partnerId);

        $trainerId = null;
        $clientId = null;

        // Determine relationship direction
        // Check if I am trainer and they are my client
        if ($user->hasActiveClient($partnerId)) {
            $trainerId = $user->id;
            $clientId = $partnerId;
        } 
        // Check if I am client and they are my trainer
        elseif ($user->hasActiveSubscriptionTo($partnerId)) {
            $trainerId = $partnerId;
            $clientId = $user->id;
        }
        else {
            return response()->json(['message' => 'Unauthorized: No active subscription found.'], 403);
        }

        // Check if conversation exists
        $conversation = Conversation::where('trainer_id', $trainerId)
            ->where('client_id', $clientId)
            ->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'trainer_id' => $trainerId,
                'client_id' => $clientId,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'conversation' => $conversation->load(['trainer', 'client'])
        ]);
    }

    public function fetchMessages($conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        // Authorization check
        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            abort(403);
        }

        // Paginate messages
        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        
        // Authorization check
        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            abort(403);
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

        // Broadcast event
        broadcast(new MessageSent($message))->toOthers();

        // Send Notification (Logic to be added)

        return response()->json(['status' => 'Message Sent!', 'message' => $message]);
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        if ($conversation->trainer_id !== Auth::id() && $conversation->client_id !== Auth::id()) {
            abort(403);
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
            'message' => 'Messages marked as read'
        ]);
    }
}
