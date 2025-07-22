<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RelationshipType extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'name_fr',
        'name_ar',
        'name_en',
        'gender',
        'requires_mother_name',
    ];

    protected $casts = [
        'requires_mother_name' => 'boolean',
    ];

    public function familyRelationships(): HasMany
    {
        return $this->hasMany(FamilyRelationship::class);
    }

    public function relationshipRequests(): HasMany
    {
        return $this->hasMany(RelationshipRequest::class);
    }
}
