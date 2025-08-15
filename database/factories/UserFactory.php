<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with a profile and gender
     */
    public function withProfile(?string $gender = null): static
    {
        return $this->afterCreating(function ($user) use ($gender) {
            // Déterminer le genre basé sur le nom si non fourni
            if (!$gender) {
                $maleNames = ['Ahmed', 'Youssef', 'Mohammed', 'Hassan', 'Omar', 'Karim', 'Adil', 'Rachid'];
                $femaleNames = ['Fatima', 'Amina', 'Leila', 'Nadia', 'Sara', 'Zineb', 'Hanae'];

                $firstName = explode(' ', $user->name)[0];
                if (in_array($firstName, $maleNames)) {
                    $gender = 'male';
                } elseif (in_array($firstName, $femaleNames)) {
                    $gender = 'female';
                } else {
                    $gender = fake()->randomElement(['male', 'female']);
                }
            }

            $user->profile()->create([
                'gender' => $gender,
                'bio' => fake()->sentence(),
                'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
                'language' => 'fr',
            ]);
        });
    }
}
