<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
}
