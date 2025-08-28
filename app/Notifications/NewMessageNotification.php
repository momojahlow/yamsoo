<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message;
use App\Models\User;

class NewMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $message;
    public $sender;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message, User $sender)
    {
        $this->message = $message;
        $this->sender = $sender;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['broadcast', 'database'];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'new_message',
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'conversation_id' => $this->message->conversation_id,
                'created_at' => $this->message->created_at,
                'user' => [
                    'id' => $this->sender->id,
                    'name' => $this->sender->name,
                    'avatar' => $this->sender->profile?->avatar_url ?? null
                ]
            ],
            'conversation' => [
                'id' => $this->message->conversation_id,
                'type' => $this->message->conversation->type ?? 'private'
            ]
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_message',
            'message_id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->sender->id,
            'sender_name' => $this->sender->name,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at
        ];
    }
}
