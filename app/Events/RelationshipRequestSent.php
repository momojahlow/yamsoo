<?php

namespace App\Events;

use App\Models\RelationshipRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RelationshipRequestSent
{
    use Dispatchable, SerializesModels;

    public RelationshipRequest $request;

    public function __construct(RelationshipRequest $request)
    {
        $this->request = $request;
    }
}
