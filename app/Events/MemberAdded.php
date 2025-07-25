<?php

namespace App\Events;

use App\Models\User;
use App\Models\FamilyRelationship;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $newMember;
    public User $addedBy;
    public FamilyRelationship $relationship;

    /**
     * Create a new event instance.
     */
    public function __construct(User $newMember, User $addedBy, FamilyRelationship $relationship)
    {
        $this->newMember = $newMember;
        $this->addedBy = $addedBy;
        $this->relationship = $relationship;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('family-updates'),
        ];
    }
}
