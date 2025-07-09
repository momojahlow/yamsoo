<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Family extends Model
{
    protected $fillable = [
        'name',
        'description',
        'created_by',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'family_user')
                    ->withPivot('relation')
                    ->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
