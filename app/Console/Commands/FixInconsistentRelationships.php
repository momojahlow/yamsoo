<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

class FixInconsistentRelationships extends Command
{
    protected $signature = 'fix:inconsistent-relationships';
    protected $description = 'Corriger les relations incohérentes dans la base';

    public function handle()
    {
        $this->info('🔧 CORRECTION DES RELATIONS INCOHÉRENTES');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        // 1. Analyser les relations actuelles
        $this->analyzeCurrentRelationships();
        $this->newLine();

        // 2. Supprimer les relations incohérentes
        $this->removeInconsistentRelationships();
        $this->newLine();

        // 3. Créer des relations cohérentes
        $this->createConsistentRelationships();
        $this->newLine();

        // 4. Vérifier le résultat
        $this->verifyResults();

        return 0;
    }

    private function analyzeCurrentRelationships(): void
    {
        $this->info('🔍 ANALYSE DES RELATIONS ACTUELLES :');
        
        $relationships = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        
        foreach ($relationships as $relation) {
            $this->line("   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}");
        }
        
        $this->line("   Total : {$relationships->count()} relations");
    }

    private function removeInconsistentRelationships(): void
    {
        $this->info('🗑️  SUPPRESSION DES RELATIONS INCOHÉRENTES :');
        
        // Supprimer toutes les relations existantes pour repartir sur une base saine
        $count = FamilyRelationship::count();
        FamilyRelationship::truncate();
        
        $this->line("   ✅ {$count} relations supprimées");
    }

    private function createConsistentRelationships(): void
    {
        $this->info('✨ CRÉATION DE RELATIONS COHÉRENTES :');
        
        // Récupérer les utilisateurs
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();

        if (!$youssef || !$fatima || !$ahmed || !$amina || !$mohammed) {
            $this->error('   ❌ Utilisateurs manquants');
            return;
        }

        // Créer des relations logiques et cohérentes
        $relations = [
            // Youssef est le père d'Ahmed
            [$youssef, $ahmed, 'father'],
            // Ahmed est le fils de Youssef (relation inverse automatique)
            [$ahmed, $youssef, 'son'],
            
            // Youssef est le père d'Amina
            [$youssef, $amina, 'father'],
            // Amina est la fille de Youssef (relation inverse automatique)
            [$amina, $youssef, 'daughter'],
            
            // Youssef est le mari de Fatima
            [$youssef, $fatima, 'husband'],
            // Fatima est l'épouse de Youssef (relation inverse automatique)
            [$fatima, $youssef, 'wife'],
            
            // Ahmed et Amina sont frère et sœur (même père)
            [$ahmed, $amina, 'brother'],
            [$amina, $ahmed, 'sister'],
        ];

        foreach ($relations as [$user, $relatedUser, $relationCode]) {
            $relationType = RelationshipType::where('code', $relationCode)->first();
            
            if ($relationType) {
                FamilyRelationship::create([
                    'user_id' => $user->id,
                    'related_user_id' => $relatedUser->id,
                    'relationship_type_id' => $relationType->id,
                    'status' => 'accepted',
                    'created_automatically' => false,
                    'accepted_at' => now(),
                ]);
                
                $this->line("   ✅ {$user->name} → {$relatedUser->name} : {$relationType->name_fr}");
            }
        }
    }

    private function verifyResults(): void
    {
        $this->info('✅ VÉRIFICATION DES RÉSULTATS :');
        
        $users = [
            'youssef.bennani@example.com' => 'Youssef',
            'fatima.zahra@example.com' => 'Fatima',
            'ahmed.benali@example.com' => 'Ahmed',
            'amina.tazi@example.com' => 'Amina',
        ];

        foreach ($users as $email => $name) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $relations = FamilyRelationship::where('user_id', $user->id)
                    ->with(['relatedUser', 'relationshipType'])
                    ->get();
                
                $this->line("   👤 {$name} ({$relations->count()} relations) :");
                foreach ($relations as $relation) {
                    $this->line("      - {$relation->relationshipType->name_fr} : {$relation->relatedUser->name}");
                }
            }
        }
    }
}
