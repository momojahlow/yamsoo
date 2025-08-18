<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'birth_date',
        'gender',
        'avatar',
        'bio',
        'language',
        'timezone',
        'notifications_email',
        'notifications_push',
        'notifications_sms',
        'privacy_profile',
        'privacy_family',
        'theme',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'birth_date' => 'date',
        'notifications_email' => 'boolean',
        'notifications_push' => 'boolean',
        'notifications_sms' => 'boolean',
    ];

    /**
     * The attributes that have default values.
     */
    protected $attributes = [
        'language' => 'fr',
        'timezone' => 'UTC',
        'notifications_email' => true,
        'notifications_push' => true,
        'notifications_sms' => false,
        'privacy_profile' => 'friends',
        'privacy_family' => 'public',
        'theme' => 'light',
    ];

    /**
     * Validation rules for profile creation
     */
    public static function validationRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'birth_date' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'bio' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Accessor pour obtenir le genre en français
     */
    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Masculin',
            'female' => 'Féminin',
            'other' => 'Autre',
            default => 'Non défini'
        };
    }

    /**
     * Vérifie si le profil a un genre clairement défini (masculin ou féminin)
     */
    public function hasDefinedGender(): bool
    {
        return in_array($this->gender, ['male', 'female']);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
