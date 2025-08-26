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

    /**
     * Ajouter ou supprimer une réaction
     */
    public static function toggleReaction(int $messageId, int $userId, string $emoji): bool
    {
        $reaction = self::where([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ])->first();

        if ($reaction) {
            $reaction->delete();
            return false; // Réaction supprimée
        } else {
            self::create([
                'message_id' => $messageId,
                'user_id' => $userId,
                'emoji' => $emoji,
            ]);
            return true; // Réaction ajoutée
        }
    }

    /**
     * Obtenir les réactions groupées par emoji pour un message
     */
    public static function getReactionsForMessage(int $messageId): array
    {
        $reactions = self::where('message_id', $messageId)
            ->with('user:id,name,avatar')
            ->get()
            ->groupBy('emoji');

        $result = [];
        foreach ($reactions as $emoji => $reactionGroup) {
            $result[] = [
                'emoji' => $emoji,
                'count' => $reactionGroup->count(),
                'users' => $reactionGroup->pluck('user')->toArray(),
            ];
        }

        return $result;
    }

    /**
     * Vérifier si un utilisateur a réagi avec un emoji spécifique
     */
    public static function hasUserReacted(int $messageId, int $userId, string $emoji): bool
    {
        return self::where([
            'message_id' => $messageId,
            'user_id' => $userId,
            'emoji' => $emoji,
        ])->exists();
    }
}
