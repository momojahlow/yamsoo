<?php

namespace Database\Factories;

use App\Models\FamilyRelationship;
use App\Models\User;
use App\Models\RelationshipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FamilyRelationship>
 */
class FamilyRelationshipFactory extends Factory
{
    protected $model = FamilyRelationship::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'related_user_id' => User::factory(),
            'relationship_type_id' => RelationshipType::factory(),
            'status' => 'accepted',
            'accepted_at' => now(),
            'created_automatically' => false,
        ];
    }
}
