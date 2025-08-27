<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageRead extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * Relation avec le message
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Marquer un message comme lu par un utilisateur
     */
    public static function markAsRead(int $messageId, int $userId): self
    {
        return self::updateOrCreate(
            [
                'message_id' => $messageId,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    /**
     * Marquer plusieurs messages comme lus
     */
    public static function markMultipleAsRead(array $messageIds, int $userId): void
    {
        $reads = [];
        $now = now();

        foreach ($messageIds as $messageId) {
            $reads[] = [
                'message_id' => $messageId,
                'user_id' => $userId,
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        self::upsert($reads, ['message_id', 'user_id'], ['read_at', 'updated_at']);
    }

    /**
     * Obtenir les utilisateurs qui ont lu un message
     */
    public static function getReadersForMessage(int $messageId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('message_id', $messageId)
            ->with('user:id,name,avatar')
            ->orderBy('read_at')
            ->get();
    }

    /**
     * Vérifier si un utilisateur a lu un message
     */
    public static function hasUserReadMessage(int $messageId, int $userId): bool
    {
        return self::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->exists();
    }
}
