<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'type', // 'private' ou 'group'
        'created_by',
        'last_message_at',
        'is_active'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Participants de la conversation
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['joined_at', 'left_at', 'is_admin'])
            ->withTimestamps();
    }

    /**
     * Messages de la conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
    }

    /**
     * Dernier message de la conversation
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /**
     * Créateur de la conversation
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtenir le nom d'affichage de la conversation
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->type === 'group') {
            return $this->name ?? 'Groupe sans nom';
        }

        // Pour les conversations privées, retourner le nom de l'autre participant
        $currentUserId = auth()->id();
        $otherParticipant = $this->participants()
            ->where('user_id', '!=', $currentUserId)
            ->first();

        return $otherParticipant ? $otherParticipant->name : 'Conversation';
    }

    /**
     * Obtenir l'avatar de la conversation
     */
    public function getAvatarAttribute(): ?string
    {
        if ($this->type === 'group') {
            return null; // Gérer les avatars de groupe plus tard
        }

        // Pour les conversations privées, retourner l'avatar de l'autre participant
        $currentUserId = auth()->id();
        $otherParticipant = $this->participants()
            ->where('user_id', '!=', $currentUserId)
            ->first();

        return $otherParticipant?->profile?->avatar;
    }

    /**
     * Vérifier si l'utilisateur est participant
     */
    public function hasParticipant(User $user): bool
    {
        return $this->participants()->where('user_id', $user->id)->exists();
    }

    /**
     * Ajouter un participant
     */
    public function addParticipant(User $user, bool $isAdmin = false): void
    {
        if (!$this->hasParticipant($user)) {
            $this->participants()->attach($user->id, [
                'joined_at' => now(),
                'is_admin' => $isAdmin
            ]);
        }
    }

    /**
     * Supprimer un participant
     */
    public function removeParticipant(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'left_at' => now()
        ]);
    }

    /**
     * Marquer comme lu pour un utilisateur
     */
    public function markAsReadFor(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now()
        ]);
    }

    /**
     * Obtenir le nombre de messages non lus pour un utilisateur
     */
    public function getUnreadCountFor(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        
        if (!$participant) {
            return 0;
        }

        $lastReadAt = $participant->pivot->last_read_at;
        
        return $this->messages()
            ->where('user_id', '!=', $user->id)
            ->when($lastReadAt, function ($query) use ($lastReadAt) {
                return $query->where('created_at', '>', $lastReadAt);
            })
            ->count();
    }
}
