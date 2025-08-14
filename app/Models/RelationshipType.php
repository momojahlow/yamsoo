<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelationshipType extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'display_name_fr',
        'display_name_ar',
        'display_name_en',
        'description',
        'reverse_relationship',
        'category',
        'generation_level',
        'sort_order',
    ];

    protected $casts = [
        'generation_level' => 'integer',
        'sort_order' => 'integer',
    ];

    public function familyRelationships(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class);
    }

    public function relationshipRequests(): HasMany
    {
        return $this->hasMany(RelationshipRequest::class);
    }

    /**
     * Scope pour ordonner par sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope pour filtrer par catégorie
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope pour filtrer par niveau de génération
     */
    public function scopeByGenerationLevel($query, int $level)
    {
        return $query->where('generation_level', $level);
    }

    /**
     * Obtenir le nom d'affichage dans la langue spécifiée
     */
    public function getDisplayName(string $locale = 'fr'): string
    {
        return match($locale) {
            'ar' => $this->display_name_ar,
            'en' => $this->display_name_en,
            default => $this->display_name_fr,
        };
    }

    /**
     * Obtenir le type de relation inverse
     */
    public function getReverseRelationType()
    {
        return static::where('name', $this->reverse_relationship)->first();
    }

    /**
     * Vérifier si c'est une relation directe (parent/enfant/frère/sœur)
     */
    public function isDirectRelation(): bool
    {
        return $this->category === 'direct';
    }

    /**
     * Vérifier si c'est une relation par mariage
     */
    public function isMarriageRelation(): bool
    {
        return $this->category === 'marriage';
    }

    /**
     * Vérifier si c'est une relation étendue
     */
    public function isExtendedRelation(): bool
    {
        return $this->category === 'extended';
    }
}
