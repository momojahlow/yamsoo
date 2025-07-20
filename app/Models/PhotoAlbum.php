<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PhotoAlbum extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'cover_photo',
        'privacy',
        'is_default',
        'photos_count',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'photos_count' => 'integer',
    ];

    /**
     * Propriétaire de l'album
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Photos de l'album
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class)->orderBy('order')->orderBy('created_at');
    }

    /**
     * Photos récentes de l'album
     */
    public function recentPhotos(int $limit = 6): HasMany
    {
        return $this->hasMany(Photo::class)->latest()->limit($limit);
    }

    /**
     * Mettre à jour le compteur de photos
     */
    public function updatePhotosCount(): void
    {
        $this->update(['photos_count' => $this->photos()->count()]);
    }

    /**
     * Définir comme album par défaut
     */
    public function setAsDefault(): void
    {
        // Retirer le statut par défaut des autres albums de l'utilisateur
        static::where('user_id', $this->user_id)
              ->where('id', '!=', $this->id)
              ->update(['is_default' => false]);

        // Définir cet album comme par défaut
        $this->update(['is_default' => true]);
    }

    /**
     * Vérifier si l'utilisateur peut voir cet album
     */
    public function canBeViewedBy(User $user): bool
    {
        // Le propriétaire peut toujours voir ses albums
        if ($this->user_id === $user->id) {
            return true;
        }

        // Albums publics visibles par tous
        if ($this->privacy === 'public') {
            return true;
        }

        // Albums familiaux visibles par les membres de la famille
        if ($this->privacy === 'family') {
            return $this->user->hasRelationWith($user);
        }

        // Albums privés visibles uniquement par le propriétaire
        return false;
    }
}
