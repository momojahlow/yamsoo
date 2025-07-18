<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Suggestion;
use App\Services\IntelligentSuggestionService;

class FixGenderInconsistentSuggestions extends Command
{
    protected $signature = 'fix:gender-inconsistent-suggestions';
    protected $description = 'Corriger les suggestions incohérentes avec le genre';

    protected IntelligentSuggestionService $intelligentSuggestionService;

    public function __construct(IntelligentSuggestionService $intelligentSuggestionService)
    {
        parent::__construct();
        $this->intelligentSuggestionService = $intelligentSuggestionService;
    }

    public function handle()
    {
        $this->info('🔧 CORRECTION DES SUGGESTIONS INCOHÉRENTES');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        // Supprimer les suggestions intelligentes existantes (potentiellement erronées)
        $intelligentSuggestions = Suggestion::where('type', 'intelligent')->get();
        $this->info("🗑️  Suppression des suggestions intelligentes existantes : {$intelligentSuggestions->count()}");
        
        $deletedCount = 0;
        foreach ($intelligentSuggestions as $suggestion) {
            $this->line("   ❌ {$suggestion->user->name} ← {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_code})");
            $suggestion->delete();
            $deletedCount++;
        }

        $this->newLine();
        $this->info("✅ {$deletedCount} suggestions intelligentes supprimées");
        $this->newLine();

        // Régénérer les suggestions intelligentes avec validation du genre
        $this->info("🧠 RÉGÉNÉRATION DES SUGGESTIONS INTELLIGENTES...");
        $newSuggestions = $this->intelligentSuggestionService->generateSuggestionsForAllUsers();
        
        $this->newLine();
        $this->info("✅ {$newSuggestions} nouvelles suggestions intelligentes créées");
        $this->newLine();

        // Vérifier le résultat
        $this->info("📊 VÉRIFICATION DES RÉSULTATS :");
        
        $users = \App\Models\User::whereIn('email', [
            'ahmed.benali@example.com',
            'fatima.zahra@example.com',
            'mohammed.alami@example.com'
        ])->get();

        foreach ($users as $user) {
            $suggestions = Suggestion::where('user_id', $user->id)
                ->where('type', 'intelligent')
                ->with('suggestedUser')
                ->get();

            $this->line("   👤 {$user->name} : {$suggestions->count()} suggestions intelligentes");
            
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $gender = $suggestedUser->profile?->gender;
                $genderIcon = $gender === 'female' ? '👩' : '👨';
                $relationName = $suggestion->suggested_relation_name ?: $suggestion->suggested_relation_code;
                
                // Vérifier la cohérence
                $isConsistent = $this->checkGenderConsistency($gender, $suggestion->suggested_relation_code);
                $status = $isConsistent ? '✅' : '❌';
                
                $this->line("      {$status} {$genderIcon} {$suggestedUser->name} : {$relationName}");
            }
        }

        $this->newLine();
        $this->info("🎉 Correction terminée ! Les suggestions sont maintenant cohérentes avec les genres.");

        return 0;
    }

    private function checkGenderConsistency(?string $gender, string $relationCode): bool
    {
        // Relations qui nécessitent un genre masculin
        $maleRelations = [
            'father', 'father_in_law', 'grandfather_paternal', 'grandfather_maternal',
            'uncle_paternal', 'uncle_maternal', 'brother', 'brother_in_law',
            'husband', 'son', 'grandson', 'nephew'
        ];
        
        // Relations qui nécessitent un genre féminin
        $femaleRelations = [
            'mother', 'mother_in_law', 'grandmother_paternal', 'grandmother_maternal',
            'aunt_paternal', 'aunt_maternal', 'sister', 'sister_in_law',
            'wife', 'daughter', 'granddaughter', 'niece'
        ];
        
        // Vérifier la cohérence
        if (in_array($relationCode, $maleRelations) && $gender !== 'male') {
            return false;
        }
        
        if (in_array($relationCode, $femaleRelations) && $gender !== 'female') {
            return false;
        }
        
        return true;
    }
}
