<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class CompleteFamilySeeder extends Seeder
{
    /**
     * Créer un scénario de famille complète avec belle-famille, cousins, neveux
     */
    public function run(): void
    {
        // Utilisateur principal (Ahmed)
        $ahmed = User::create([
            'name' => 'Ahmed Benali',
            'email' => 'ahmed@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id' => $ahmed->id,
            'bio' => 'Chef de famille, ingénieur informatique',
            'birth_date' => '1985-03-15',
            'gender' => 'male',
            'phone' => '+33123456789',
        ]);

        // Sa femme (Fatima)
        $fatima = User::create([
            'name' => 'Fatima Benali',
            'email' => 'fatima@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        Profile::create([
            'user_id' => $fatima->id,
            'bio' => 'Professeure de français',
            'birth_date' => '1987-07-22',
            'gender' => 'female',
            'phone' => '+33123456790',
        ]);

        // Parents d'Ahmed
        $pereAhmed = User::create([
            'name' => 'Mohamed Benali',
            'email' => 'mohamed@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $mereAhmed = User::create([
            'name' => 'Aicha Benali',
            'email' => 'aicha@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Frère d'Ahmed
        $frereAhmed = User::create([
            'name' => 'Omar Benali',
            'email' => 'omar@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Sœur d'Ahmed
        $soeurAhmed = User::create([
            'name' => 'Leila Benali',
            'email' => 'leila@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Enfants d'Ahmed et Fatima
        $fils = User::create([
            'name' => 'Youssef Benali',
            'email' => 'youssef@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $fille = User::create([
            'name' => 'Amina Benali',
            'email' => 'amina@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Parents de Fatima (beaux-parents d'Ahmed)
        $pereFatima = User::create([
            'name' => 'Hassan Alami',
            'email' => 'hassan@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $mereFatima = User::create([
            'name' => 'Khadija Alami',
            'email' => 'khadija@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Frère de Fatima (beau-frère d'Ahmed)
        $beleFrere = User::create([
            'name' => 'Karim Alami',
            'email' => 'karim@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Oncle d'Ahmed
        $oncle = User::create([
            'name' => 'Abdellah Benali',
            'email' => 'abdellah@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Cousin d'Ahmed (fils de l'oncle)
        $cousin = User::create([
            'name' => 'Mehdi Benali',
            'email' => 'mehdi@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Neveu d'Ahmed (fils du frère)
        $neveu = User::create([
            'name' => 'Anas Benali',
            'email' => 'anas@yamsoo.test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Récupérer les types de relations
        $relationTypes = RelationshipType::all()->keyBy('name');

        // Créer les relations familiales
        $relations = [
            // Relations directes d'Ahmed
            [$ahmed->id, $fatima->id, 'wife'],
            [$ahmed->id, $pereAhmed->id, 'father'],
            [$ahmed->id, $mereAhmed->id, 'mother'],
            [$ahmed->id, $frereAhmed->id, 'brother'],
            [$ahmed->id, $soeurAhmed->id, 'sister'],
            [$ahmed->id, $fils->id, 'son'],
            [$ahmed->id, $fille->id, 'daughter'],
            [$ahmed->id, $oncle->id, 'uncle'],
            [$ahmed->id, $cousin->id, 'cousin'],
            [$ahmed->id, $neveu->id, 'nephew'],
            
            // Belle-famille d'Ahmed
            [$ahmed->id, $pereFatima->id, 'father_in_law'],
            [$ahmed->id, $mereFatima->id, 'mother_in_law'],
            [$ahmed->id, $beleFrere->id, 'brother_in_law'],
        ];

        foreach ($relations as $relation) {
            $relationshipType = $relationTypes[$relation[2]] ?? null;
            if ($relationshipType) {
                FamilyRelationship::create([
                    'user_id' => $relation[0],
                    'related_user_id' => $relation[1],
                    'relationship_type_id' => $relationshipType->id,
                    'status' => 'accepted',
                    'accepted_at' => now(),
                    'created_automatically' => false,
                ]);
            }
        }

        $this->command->info('Famille complète créée avec succès !');
        $this->command->info('Utilisateur principal : ahmed@yamsoo.test (mot de passe: password)');
        $this->command->info('La famille inclut : conjoint, parents, frères/sœurs, enfants, belle-famille, oncle, cousin, neveu');
    }
}
