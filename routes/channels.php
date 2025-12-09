<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Models\Conversation;

Broadcast::channel('conversation.{id}', function (User $user, $id) {
    $conversation = Conversation::find($id);
    return $conversation && ($user->id === $conversation->trainer_id || $user->id === $conversation->client_id);
});

Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});
