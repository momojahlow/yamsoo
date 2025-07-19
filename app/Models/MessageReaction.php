<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageReaction extends Model
{
    protected $fillable = [
        'message_id',
        'user_id',
        'emoji'
    ];

    /**
     * Message associé
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Utilisateur qui a réagi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
