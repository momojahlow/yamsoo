<?php

namespace App\Services;

use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Collection;

class MessageService
{
    public function getMessages(User $user): Collection
    {
        return Message::where('user_id', $user->id)
            ->orWhere('recipient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function sendMessage(User $sender, int $recipientId, string $content, ?string $attachment = null): Message
    {
        return Message::create([
            'user_id' => $sender->id,
            'recipient_id' => $recipientId,
            'content' => $content,
            'attachment' => $attachment,
        ]);
    }

    public function getConversation(User $user, int $otherUserId): Collection
    {
        return Message::where(function ($query) use ($user, $otherUserId) {
            $query->where('user_id', $user->id)
                  ->where('recipient_id', $otherUserId);
        })->orWhere(function ($query) use ($user, $otherUserId) {
            $query->where('user_id', $otherUserId)
                  ->where('recipient_id', $user->id);
        })->orderBy('created_at', 'asc')->get();
    }
}
