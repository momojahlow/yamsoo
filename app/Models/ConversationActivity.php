<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationActivity extends Model
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'action',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    /**
     * Conversation associée
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Utilisateur qui a effectué l'action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Créer une activité de création de groupe
     */
    public static function logGroupCreated(Conversation $conversation, User $creator): void
    {
        static::create([
            'conversation_id' => $conversation->id,
            'user_id' => $creator->id,
            'action' => 'created',
            'metadata' => [
                'group_name' => $conversation->name
            ]
        ]);
    }

    /**
     * Créer une activité d'ajout de participant
     */
    public static function logParticipantAdded(Conversation $conversation, User $addedBy, User $addedUser): void
    {
        static::create([
            'conversation_id' => $conversation->id,
            'user_id' => $addedBy->id,
            'action' => 'added',
            'metadata' => [
                'added_user_id' => $addedUser->id,
                'added_user_name' => $addedUser->name
            ]
        ]);
    }

    /**
     * Créer une activité de départ de participant
     */
    public static function logParticipantLeft(Conversation $conversation, User $user): void
    {
        static::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'action' => 'left',
            'metadata' => []
        ]);
    }

    /**
     * Créer une activité de changement de nom
     */
    public static function logNameChanged(Conversation $conversation, User $changedBy, string $oldName, string $newName): void
    {
        static::create([
            'conversation_id' => $conversation->id,
            'user_id' => $changedBy->id,
            'action' => 'name_changed',
            'metadata' => [
                'old_name' => $oldName,
                'new_name' => $newName
            ]
        ]);
    }

    /**
     * Obtenir le message formaté pour l'activité
     */
    public function getFormattedMessageAttribute(): string
    {
        $userName = $this->user->name;
        
        return match($this->action) {
            'created' => "{$userName} a créé le groupe",
            'joined' => "{$userName} a rejoint le groupe",
            'left' => "{$userName} a quitté le groupe",
            'added' => "{$userName} a ajouté {$this->metadata['added_user_name']} au groupe",
            'removed' => "{$userName} a retiré {$this->metadata['removed_user_name']} du groupe",
            'promoted' => "{$userName} a promu {$this->metadata['promoted_user_name']} administrateur",
            'demoted' => "{$userName} a retiré les droits d'administrateur à {$this->metadata['demoted_user_name']}",
            'name_changed' => "{$userName} a changé le nom du groupe de \"{$this->metadata['old_name']}\" à \"{$this->metadata['new_name']}\"",
            'description_changed' => "{$userName} a modifié la description du groupe",
            'avatar_changed' => "{$userName} a modifié la photo du groupe",
            default => "{$userName} a effectué une action"
        };
    }
}
