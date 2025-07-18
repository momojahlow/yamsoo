<?php

namespace App\Events;

use App\Models\User;
use App\Models\RelationshipRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RelationshipAccepted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $requester;
    public User $target;
    public RelationshipRequest $relationshipRequest;

    /**
     * Create a new event instance.
     */
    public function __construct(User $requester, User $target, RelationshipRequest $relationshipRequest)
    {
        $this->requester = $requester;
        $this->target = $target;
        $this->relationshipRequest = $relationshipRequest;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
