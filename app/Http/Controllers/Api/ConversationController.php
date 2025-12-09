<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    /**
     * Get list of conversations
     */
    public function index()
    {
        $user = Auth::user();
        
        $conversations = Conversation::where('trainer_id', $user->id)
            ->orWhere('client_id', $user->id)
            ->with(['trainer', 'client', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversations
        ]);
    }

    /**
     * Start a conversation
     */
    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();
        $receiverId = $request->receiver_id;

        // Determine trainer and client based on roles
        // Assuming the logic: if current user is trainer, receiver is client, and vice versa.
        // Or strictly checking roles if available.
        
        $trainerId = null;
        $clientId = null;

        if ($user->role === 'trainer') {
            $trainerId = $user->id;
            $clientId = $receiverId;
        } elseif ($user->role === 'client') {
            $trainerId = $receiverId;
            $clientId = $user->id;
        } else {
             // Fallback or specific logic for other roles if they can chat
             // For now assuming strict trainer-client chat
             // We can also check the receiver's role to be sure
        }

        if (!$trainerId || !$clientId) {
             return response()->json([
                'success' => false,
                'message' => 'Invalid chat participants.'
            ], 400);
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
            'success' => true,
            'data' => $conversation->load(['trainer', 'client', 'lastMessage'])
        ]);
    }
}
