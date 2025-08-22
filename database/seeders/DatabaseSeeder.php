<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed les types de relations en premier
        $this->call([
            ComprehensiveRelationshipTypesSeeder::class,
        ]);

        // Seed les utilisateurs avec leurs profils
        $this->call([
            UsersSeeder::class,
        ]);

        // Seed les relations familiales
        $this->call([
            FamilyRelationsSeeder::class,
        ]);

        // Seed les notifications
        $this->call([
            NotificationsSeeder::class,
        ]);

        // Seed les suggestions - DÉSACTIVÉ pour éviter les suggestions automatiques
        // $this->call([
        //     SuggestionsSeeder::class,
        // ]);

        // Créer un utilisateur de test pour le développement avec profil
        $testUser = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        // Créer le profil pour l'utilisateur de test s'il n'existe pas
        if (!$testUser->profile) {
            $testUser->profile()->create([
                'first_name' => 'Test',
                'last_name' => 'User',
                'gender' => 'male',
                'bio' => 'Utilisateur de test pour le développement',
                'birth_date' => '1990-01-01',
                'language' => 'fr',
            ]);
        }
    }
}
