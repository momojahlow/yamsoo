<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile',
        'password',
        'family_id',
        'last_seen_at',
        'role',
        'role_assigned_at',
        'role_assigned_by',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'role_assigned_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Conversations de l'utilisateur
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['joined_at', 'left_at', 'last_read_at', 'is_admin'])
            ->withTimestamps()
            ->whereNull('conversation_participants.left_at');
    }

    /**
     * Relations familiales de l'utilisateur
     */
    public function familyRelationships(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class);
    }

    /**
     * Vérifier si l'utilisateur est en ligne
     */
    public function isOnline(): bool
    {
        // Logique pour déterminer si l'utilisateur est en ligne
        // Peut être basée sur last_seen_at ou une session active
        return $this->last_seen_at && $this->last_seen_at->diffInMinutes(now()) < 5;
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    /**
     * Relations familiales où cet utilisateur est l'initiateur
     */
    public function familyRelations(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class, 'user_id');
    }

    /**
     * Relations familiales où cet utilisateur est la cible
     */
    public function familyRelationsAsTarget(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class, 'related_user_id');
    }

    /**
     * Toutes les relations familiales de cet utilisateur (initiateur ou cible)
     */
    public function allFamilyRelations()
    {
        return FamilyRelationship::where(function($query) {
            $query->where('user_id', $this->id)
                  ->orWhere('related_user_id', $this->id);
        })->where('status', 'accepted');
    }

    /**
     * Obtient tous les utilisateurs liés par une relation familiale acceptée
     */
    public function getRelatedUsers()
    {
        $relatedUserIds = collect();

        // Relations où cet utilisateur est l'initiateur
        $relatedUserIds = $relatedUserIds->merge(
            $this->familyRelations()->where('status', 'accepted')->pluck('related_user_id')
        );

        // Relations où cet utilisateur est la cible
        $relatedUserIds = $relatedUserIds->merge(
            $this->familyRelationsAsTarget()->where('status', 'accepted')->pluck('user_id')
        );

        return User::whereIn('id', $relatedUserIds->unique())->get();
    }

    /**
     * Vérifie si cet utilisateur a une relation familiale avec un autre utilisateur
     */
    public function hasRelationWith(User $otherUser): bool
    {
        return FamilyRelationship::where(function($query) use ($otherUser) {
            $query->where('user_id', $this->id)->where('related_user_id', $otherUser->id);
        })->orWhere(function($query) use ($otherUser) {
            $query->where('user_id', $otherUser->id)->where('related_user_id', $this->id);
        })->where('status', 'accepted')->exists();
    }

    /**
     * Albums photo de l'utilisateur
     */
    public function photoAlbums(): HasMany
    {
        return $this->hasMany(PhotoAlbum::class)->orderBy('is_default', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Photos de l'utilisateur
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->latest();
    }

    /**
     * Album par défaut de l'utilisateur
     */
    public function defaultPhotoAlbum(): HasOne
    {
        return $this->hasOne(PhotoAlbum::class)->where('is_default', true);
    }

    /**
     * Obtenir ou créer l'album par défaut
     */
    public function getOrCreateDefaultAlbum(): PhotoAlbum
    {
        $defaultAlbum = $this->defaultPhotoAlbum;

        if (!$defaultAlbum) {
            $defaultAlbum = $this->photoAlbums()->create([
                'title' => 'Photos de ' . $this->name,
                'description' => 'Album photo principal',
                'privacy' => 'family',
                'is_default' => true,
            ]);
        }

        return $defaultAlbum;
    }

    /**
     * Photos récentes de l'utilisateur
     */
    public function recentPhotos(int $limit = 12)
    {
        return $this->photos()->limit($limit);
    }

    /**
     * Albums visibles par un utilisateur donné
     */
    public function visibleAlbumsFor(User $viewer)
    {
        return $this->photoAlbums()->get()->filter(function ($album) use ($viewer) {
            return $album->canBeViewedBy($viewer);
        });
    }

    // ==========================================
    // MÉTHODES DE GESTION DES RÔLES
    // ==========================================

    /**
     * Vérifier si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Vérifier si l'utilisateur a l'un des rôles spécifiés
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Vérifier si l'utilisateur est administrateur
     */
    public function isAdmin(): bool
    {
        return $this->hasAnyRole(['admin', 'super_admin']);
    }

    /**
     * Vérifier si l'utilisateur est super administrateur
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Vérifier si l'utilisateur est modérateur ou plus
     */
    public function isModerator(): bool
    {
        return $this->hasAnyRole(['moderator', 'admin', 'super_admin']);
    }

    /**
     * Assigner un rôle à l'utilisateur
     */
    public function assignRole(string $role, ?User $assignedBy = null): void
    {
        $this->update([
            'role' => $role,
            'role_assigned_at' => now(),
            'role_assigned_by' => $assignedBy?->id,
        ]);
    }

    /**
     * Activer/désactiver l'utilisateur
     */
    public function setActive(bool $active): void
    {
        $this->update(['is_active' => $active]);
    }

    /**
     * Enregistrer la dernière connexion
     */
    public function recordLogin(string $ip): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip,
            'last_seen_at' => now(),
        ]);
    }

    /**
     * Obtenir le nom du rôle en français
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'user' => 'Utilisateur',
            'moderator' => 'Modérateur',
            'admin' => 'Administrateur',
            'super_admin' => 'Super Administrateur',
            default => 'Inconnu',
        };
    }

    /**
     * Scope pour filtrer par rôle
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope pour les administrateurs
     */
    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'super_admin']);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Relation avec l'utilisateur qui a assigné le rôle
     */
    public function roleAssignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'role_assigned_by');
    }
}
