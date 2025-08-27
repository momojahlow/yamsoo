<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privé pour les conversations
Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    // Vérifier que l'utilisateur fait partie de cette conversation
    return $user->conversations()->where('conversations.id', $conversationId)->exists();
});
