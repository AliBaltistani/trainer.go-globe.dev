<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display a listing of conversations.
     */
    public function index()
    {
        $conversations = Conversation::with(['trainer', 'client', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.chat.index', compact('conversations'));
    }

    /**
     * Display the specified conversation.
     */
    public function show(Conversation $conversation)
    {
        $conversation->load(['messages.sender', 'trainer', 'client']);
        return view('admin.chat.show', compact('conversation'));
    }
}
