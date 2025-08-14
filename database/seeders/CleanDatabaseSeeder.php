<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Profile;

class CleanDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer toutes les relations et suggestions existantes
        // Utiliser delete au lieu de truncate pour éviter les problèmes de clés étrangères
        DB::table('suggestions')->delete();
        DB::table('family_relationships')->delete();
        DB::table('relationship_requests')->delete();
        DB::table('notifications')->delete();

        $this->command->info('🧹 Base de données nettoyée (relations et suggestions supprimées)');

        // Créer les utilisateurs de base sans aucune relation
        $this->createUsers();

        // Créer les types de relations avec la nouvelle structure
        $this->call([
            ComprehensiveRelationshipTypesSeeder::class,
        ]);

        $this->command->info('✅ Base de données réinitialisée avec succès');
        $this->command->info('👥 Utilisateurs créés sans aucune relation');
        $this->command->info('🔗 Types de relations configurés');
    }

    private function createUsers(): void
    {
        $users = [
            [
                'name' => 'Ahmed Benali',
                'email' => 'ahmed.benali@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Ahmed',
                    'last_name' => 'Benali',
                    'gender' => 'male',
                    'birth_date' => '1985-03-15',
                    'phone' => '+212 6 12 34 56 78'
                ]
            ],
            [
                'name' => 'Fatima Zahra',
                'email' => 'fatima.zahra@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Fatima',
                    'last_name' => 'Zahra',
                    'gender' => 'female',
                    'birth_date' => '1990-07-22',
                    'phone' => '+212 6 23 45 67 89'
                ]
            ],
            [
                'name' => 'Mohammed Alami',
                'email' => 'mohammed.alami@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Mohammed',
                    'last_name' => 'Alami',
                    'gender' => 'male',
                    'birth_date' => '1988-11-10',
                    'phone' => '+212 6 34 56 78 90'
                ]
            ],
            [
                'name' => 'Youssef Bennani',
                'email' => 'youssef.bennani@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Youssef',
                    'last_name' => 'Bennani',
                    'gender' => 'male',
                    'birth_date' => '1992-05-18',
                    'phone' => '+212 6 45 67 89 01'
                ]
            ],
            [
                'name' => 'Aicha Idrissi',
                'email' => 'aicha.idrissi@example.com',
                'password' => Hash::make('password'),
                'profile' => [
                    'first_name' => 'Aicha',
                    'last_name' => 'Idrissi',
                    'gender' => 'female',
                    'birth_date' => '1995-09-03',
                    'phone' => '+212 6 56 78 90 12'
                ]
            ]
        ];

        foreach ($users as $userData) {
            $profileData = $userData['profile'];
            unset($userData['profile']);

            $user = User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                array_merge($profileData, ['user_id' => $user->id])
            );

            $this->command->info("👤 Utilisateur mis à jour : {$user->name}");
        }
    }


}
