<?php

namespace Database\Factories;

use App\Models\RelationshipType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RelationshipType>
 */
class RelationshipTypeFactory extends Factory
{
    protected $model = RelationshipType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'display_name_fr' => $this->faker->word(),
            'display_name_ar' => $this->faker->word(),
            'display_name_en' => $this->faker->word(),
            'description' => $this->faker->sentence(),
            'reverse_relationship' => $this->faker->word(),
            'category' => $this->faker->randomElement(['direct', 'extended', 'marriage', 'adoption']),
            'generation_level' => $this->faker->numberBetween(-2, 2),
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
