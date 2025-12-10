<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
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
            ->withCount(['messages as unread_count' => function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                      ->where('is_read', false);
            }])
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
        $receiver = User::findOrFail($receiverId);

        $trainerId = null;
        $clientId = null;

        if ($user->isTrainerRole() && $receiver->isClientRole() && $user->hasActiveClient($receiverId)) {
            $trainerId = $user->id;
            $clientId = $receiverId;
        } elseif ($user->isClientRole() && $receiver->isTrainerRole() && $user->hasActiveSubscriptionTo($receiverId)) {
            $trainerId = $receiverId;
            $clientId = $user->id;
        }

        if (!$trainerId || !$clientId) {
             return response()->json([
                'success' => false,
                'message' => 'Unauthorized: No active subscription found.'
            ], 403);
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

    /**
     * Delete a conversation
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        if ($conversation->trainer_id !== $user->id && $conversation->client_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this conversation.'
            ], 403);
        }

        $conversation->messages()->delete();
        $conversation->delete();

        return response()->json([
            'success' => true,
            'message' => 'Conversation deleted successfully'
        ]);
    }

    /**
     * Unread counts summary
     */
    public function unreadCounts()
    {
        $user = Auth::user();

        $totalUnread = Message::where('receiver_id', $user->id)
            ->where('is_read', false)
            ->count();

        $unreadConversations = Conversation::where(function ($q) use ($user) {
                $q->where('trainer_id', $user->id)
                  ->orWhere('client_id', $user->id);
            })
            ->whereHas('messages', function ($q) use ($user) {
                $q->where('receiver_id', $user->id)
                  ->where('is_read', false);
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_unread_messages' => $totalUnread,
                'unread_conversations' => $unreadConversations,
            ]
        ]);
    }

    /**
     * List contacts for subscribed users
     */
    public function contacts()
    {
        $user = Auth::user();

        if ($user->isTrainerRole()) {
            $contacts = $user->subscriptionsAsTrainer()
                ->active()
                ->with('client:id,name,email,phone,profile_image,role')
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => optional($sub->client)->id,
                        'name' => optional($sub->client)->name,
                        'email' => optional($sub->client)->email,
                        'phone' => optional($sub->client)->phone,
                        'role' => optional($sub->client)->role,
                        'profile_image' => optional($sub->client)->profile_image ? asset('storage/' . optional($sub->client)->profile_image) : null,
                        'subscribed_at' => optional($sub->subscribed_at)->toDateTimeString(),
                    ];
                });
        } elseif ($user->isClientRole()) {
            $contacts = $user->subscriptionsAsClient()
                ->active()
                ->with('trainer:id,name,email,phone,profile_image,role')
                ->get()
                ->map(function ($sub) {
                    return [
                        'id' => optional($sub->trainer)->id,
                        'name' => optional($sub->trainer)->name,
                        'email' => optional($sub->trainer)->email,
                        'phone' => optional($sub->trainer)->phone,
                        'role' => optional($sub->trainer)->role,
                        'profile_image' => optional($sub->trainer)->profile_image ? asset('storage/' . optional($sub->trainer)->profile_image) : null,
                        'subscribed_at' => optional($sub->subscribed_at)->toDateTimeString(),
                    ];
                });
        } else {
            $contacts = collect();
        }

        return response()->json([
            'success' => true,
            'data' => $contacts,
        ]);
    }
}
