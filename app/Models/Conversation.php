<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Conversation extends Model
{
    protected $fillable = [
        'name',
        'description',
        'avatar',
        'type', // 'private' ou 'group'
        'created_by',
        'last_message_at',
        'max_participants',
        'is_active',
        'visibility',
        'join_approval_required',
        'last_message_id',
        'last_activity_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_active' => 'boolean',
        'join_approval_required' => 'boolean',
        'last_activity_at' => 'datetime'
    ];

    /**
     * Participants de la conversation
     */
    public function participants(): BelongsToMany
    {
        // Vérifier quelles colonnes existent dans la table pivot
        $pivotColumns = ['joined_at', 'left_at', 'is_admin', 'last_read_at'];

        // Ajouter les nouvelles colonnes si elles existent
        if (Schema::hasColumn('conversation_participants', 'role')) {
            $pivotColumns[] = 'role';
        }
        if (Schema::hasColumn('conversation_participants', 'notifications_enabled')) {
            $pivotColumns[] = 'notifications_enabled';
        }
        if (Schema::hasColumn('conversation_participants', 'nickname')) {
            $pivotColumns[] = 'nickname';
        }
        if (Schema::hasColumn('conversation_participants', 'status')) {
            $pivotColumns[] = 'status';
        }
        if (Schema::hasColumn('conversation_participants', 'invited_by')) {
            $pivotColumns[] = 'invited_by';
        }
        if (Schema::hasColumn('conversation_participants', 'invitation_sent_at')) {
            $pivotColumns[] = 'invitation_sent_at';
        }

        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot($pivotColumns)
            ->withTimestamps();
    }



    /**
     * Administrateurs de la conversation
     */
    public function admins(): BelongsToMany
    {
        return $this->activeParticipants()->where('conversation_participants.is_admin', true);
    }

    /**
     * Messages de la conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'desc');
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

    /**
     * Vérifier si l'utilisateur est admin
     */
    public function isAdmin(User $user): bool
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();
        return $participant && $participant->pivot->is_admin;
    }

    /**
     * Vérifier si c'est un groupe
     */
    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    /**
     * Obtenir le nombre de participants actifs
     */
    public function getActiveParticipantsCountAttribute(): int
    {
        return $this->activeParticipants()->count();
    }

    /**
     * Vérifier si le groupe peut accepter plus de participants
     */
    public function canAddParticipants(): bool
    {
        if (!$this->isGroup()) {
            return false;
        }

        return $this->active_participants_count < $this->max_participants;
    }

    /**
     * Relation avec le dernier message
     */
    public function lastMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }

    /**
     * Relation avec les activités de la conversation
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ConversationActivity::class);
    }

    /**
     * Mettre à jour le dernier message et l'activité
     */
    public function updateLastActivity(Message $message): void
    {
        $this->update([
            'last_message_id' => $message->id,
            'last_activity_at' => $message->created_at,
            'last_message_at' => $message->created_at, // Compatibilité
        ]);
    }

    /**
     * Vérifier si la conversation est publique
     */
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    /**
     * Vérifier si la conversation nécessite une approbation pour rejoindre
     */
    public function requiresApproval(): bool
    {
        return $this->join_approval_required;
    }

    /**
     * Obtenir les participants actifs (non bannis, non partis)
     */
    public function activeParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'active');
    }

    /**
     * Obtenir les participants en attente d'approbation
     */
    public function pendingParticipants(): BelongsToMany
    {
        return $this->participants()->wherePivot('status', 'pending');
    }

    /**
     * Ajouter un participant avec un statut spécifique
     */
    public function addParticipant(int $userId, array $attributes = []): void
    {
        $defaultAttributes = [
            'joined_at' => now(),
            'role' => 'member',
            'status' => 'active',
            'notifications_enabled' => true,
        ];

        $this->participants()->attach($userId, array_merge($defaultAttributes, $attributes));
    }

    /**
     * Inviter un utilisateur (statut pending)
     */
    public function inviteUser(int $userId, int $invitedBy, ?string $nickname = null): void
    {
        $this->addParticipant($userId, [
            'status' => 'invited',
            'invited_by' => $invitedBy,
            'invitation_sent_at' => now(),
            'nickname' => $nickname,
        ]);
    }

    // Compatibilité avec l'ancien modèle
    public function users()
    {
        return $this->participants();
    }
}
