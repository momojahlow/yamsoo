<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_album_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'thumbnail_path',
        'metadata',
        'order',
        'taken_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'order' => 'integer',
        'taken_at' => 'datetime',
    ];

    /**
     * Propriétaire de la photo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Album contenant la photo
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(PhotoAlbum::class, 'photo_album_id');
    }

    /**
     * URL complète de la photo
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * URL de la miniature
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    /**
     * Taille formatée du fichier
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Dimensions de l'image
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return $this->width . ' × ' . $this->height;
        }
        return null;
    }

    /**
     * Vérifier si l'utilisateur peut voir cette photo
     */
    public function canBeViewedBy(User $user): bool
    {
        return $this->album->canBeViewedBy($user);
    }

    /**
     * Supprimer la photo et ses fichiers
     */
    public function deleteWithFiles(): bool
    {
        // Supprimer les fichiers du stockage
        if ($this->file_path && Storage::exists($this->file_path)) {
            Storage::delete($this->file_path);
        }

        if ($this->thumbnail_path && Storage::exists($this->thumbnail_path)) {
            Storage::delete($this->thumbnail_path);
        }

        // Mettre à jour le compteur de l'album
        $this->album->updatePhotosCount();

        // Supprimer l'enregistrement de la base de données
        return $this->delete();
    }
}
