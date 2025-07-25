<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use Illuminate\Database\Seeder;

class FamilyRelationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nettoyer les relations et demandes existantes
        FamilyRelationship::truncate();
        RelationshipRequest::truncate();

        // Message informatif
        $this->command->info('Base de données nettoyée. Aucune relation pré-établie créée.');
        $this->command->info('Vous pouvez maintenant créer vos propres demandes de relation via l\'interface.');
        $this->command->info('');
        $this->command->info('Utilisateurs disponibles pour créer des relations :');

        // Afficher la liste des utilisateurs disponibles
        $users = User::with('profile')->get();
        foreach ($users as $user) {
            $displayName = $user->profile && $user->profile->first_name && $user->profile->last_name
                ? "{$user->profile->first_name} {$user->profile->last_name}"
                : $user->name;
            $this->command->info("- {$displayName} ({$user->email})");
        }

        $this->command->info('');
        $this->command->info('Types de relations disponibles :');

        // Afficher les types de relations disponibles
        $relationshipTypes = RelationshipType::ordered()->get();
        foreach ($relationshipTypes as $type) {
            $this->command->info("- {$type->display_name_fr} (nom: {$type->name}, catégorie: {$type->category})");
        }
    }
}
