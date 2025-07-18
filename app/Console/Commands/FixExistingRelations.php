<?php

namespace App\Console\Commands;

use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Console\Command;

class FixExistingRelations extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fix:existing-relations {--dry-run : Afficher les changements sans les appliquer}';

    /**
     * The console command description.
     */
    protected $description = 'Corrige les relations existantes avec des genres incorrects';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔧 Correction des relations existantes avec genres incorrects...');
        $this->newLine();
        
        // Récupérer toutes les relations acceptées
        $relations = FamilyRelationship::where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();
            
        $this->info("📊 {$relations->count()} relation(s) à analyser");
        $this->newLine();
        
        $corrections = collect();
        
        foreach ($relations as $relation) {
            $user = $relation->user;
            $relatedUser = $relation->relatedUser;
            $currentType = $relation->relationshipType;
            
            // Vérifier si la relation est cohérente avec le genre
            $correction = $this->checkRelationConsistency($user, $relatedUser, $currentType);
            
            if ($correction) {
                $corrections->push([
                    'relation' => $relation,
                    'current_type' => $currentType,
                    'correct_type' => $correction,
                    'user' => $user,
                    'related_user' => $relatedUser
                ]);
            }
        }
        
        if ($corrections->isEmpty()) {
            $this->info('✅ Toutes les relations sont cohérentes avec les genres !');
            return;
        }
        
        $this->warn("⚠️  {$corrections->count()} relation(s) incohérente(s) détectée(s) :");
        $this->newLine();
        
        foreach ($corrections as $correction) {
            $relation = $correction['relation'];
            $user = $correction['user'];
            $relatedUser = $correction['related_user'];
            $currentType = $correction['current_type'];
            $correctType = $correction['correct_type'];
            
            $this->line("🔄 {$user->name} → {$relatedUser->name}");
            $this->line("   Actuel: {$currentType->name_fr} (code: {$currentType->code})");
            $this->line("   Correct: {$correctType->name_fr} (code: {$correctType->code})");
            $this->line("   Raison: Genre de {$relatedUser->name} = {$relatedUser->profile?->gender}");
            
            if (!$isDryRun) {
                // Appliquer la correction
                $relation->update(['relationship_type_id' => $correctType->id]);
                $this->info("   ✅ Corrigé !");
            } else {
                $this->line("   📋 Sera corrigé");
            }
            
            $this->newLine();
        }
        
        if ($isDryRun) {
            $this->info("📋 Mode simulation - Aucun changement appliqué");
            $this->info("💡 Exécutez sans --dry-run pour appliquer les corrections");
        } else {
            $this->info("✅ {$corrections->count()} relation(s) corrigée(s) !");
        }
    }
    
    private function checkRelationConsistency($user, $relatedUser, $currentType): ?RelationshipType
    {
        $relatedUserGender = $relatedUser->profile?->gender;
        $userGender = $user->profile?->gender;
        
        if (!$relatedUserGender || !$userGender) {
            return null; // Impossible de vérifier sans genre défini
        }
        
        $code = $currentType->code;
        
        // Vérifications pour les relations parent-enfant
        switch ($code) {
            case 'father':
                // Si l'utilisateur est marqué comme "père" mais n'est pas masculin
                if ($userGender !== 'male') {
                    return RelationshipType::where('code', 'mother')->first();
                }
                break;
                
            case 'mother':
                // Si l'utilisateur est marqué comme "mère" mais n'est pas féminin
                if ($userGender !== 'female') {
                    return RelationshipType::where('code', 'father')->first();
                }
                break;
                
            case 'son':
                // Si l'utilisateur est marqué comme "fils" mais n'est pas masculin
                if ($userGender !== 'male') {
                    return RelationshipType::where('code', 'daughter')->first();
                }
                break;
                
            case 'daughter':
                // Si l'utilisateur est marqué comme "fille" mais n'est pas féminin
                if ($userGender !== 'female') {
                    return RelationshipType::where('code', 'son')->first();
                }
                break;
                
            case 'brother':
                // Si l'utilisateur est marqué comme "frère" mais n'est pas masculin
                if ($userGender !== 'male') {
                    return RelationshipType::where('code', 'sister')->first();
                }
                break;
                
            case 'sister':
                // Si l'utilisateur est marqué comme "sœur" mais n'est pas féminin
                if ($userGender !== 'female') {
                    return RelationshipType::where('code', 'brother')->first();
                }
                break;
                
            case 'husband':
                // Si l'utilisateur est marqué comme "mari" mais n'est pas masculin
                if ($userGender !== 'male') {
                    return RelationshipType::where('code', 'wife')->first();
                }
                break;
                
            case 'wife':
                // Si l'utilisateur est marqué comme "épouse" mais n'est pas féminin
                if ($userGender !== 'female') {
                    return RelationshipType::where('code', 'husband')->first();
                }
                break;
        }
        
        return null; // Aucune correction nécessaire
    }
}
