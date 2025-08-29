<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message, User $user)
    {
        $this->message = $message;
        $this->user = $user;

        // Log pour debug
        Log::info('🚀 MessageSent Event créé', [
            'message_id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id' => $user->id,
            'channel' => 'private-conversation.' . $message->conversation_id,
            'event' => 'message.sent'
        ]);

        // Log visible dans error_log
        error_log("🚀 EVENT MESSAGESENT CRÉÉ - Message: {$message->id}, Conversation: {$message->conversation_id}");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channel = 'conversation.' . $this->message->conversation_id;
        Log::info('📡 MessageSent BROADCASTING ON', ['channel' => $channel]);

        // Configuration standard Laravel avec canal privé
        return [
            new PrivateChannel($channel)
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'content' => $this->message->content,
                'type' => $this->message->type ?? 'text',
                'file_url' => $this->message->file_url,
                'file_name' => $this->message->file_name,
                'file_size' => $this->message->formatted_file_size ?? null,
                'created_at' => $this->message->created_at ? $this->message->created_at->toISOString() : '',
                'is_edited' => false,
                'edited_at' => null,
                'user' => [
                    'id' => $this->message->user->id,
                    'name' => $this->message->user->name,
                    'avatar' => $this->message->user->profile?->avatar_url ?? null
                ],
                'reply_to' => $this->message->replyTo ? [
                    'id' => $this->message->replyTo->id,
                    'content' => $this->message->replyTo->content,
                    'user_name' => $this->message->replyTo->user->name
                ] : null,
                'reactions' => []
            ]
        ];
    }

}
