<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\IntelligentSuggestionService;

class TestImprovedSuggestions extends Command
{
    protected $signature = 'test:improved-suggestions';
    protected $description = 'Tester les suggestions améliorées avec validation générationnelle';

    public function handle()
    {
        $this->info('🧪 TEST DES SUGGESTIONS AMÉLIORÉES');
        $this->info('═══════════════════════════════════');
        $this->newLine();

        // Nettoyer les anciennes suggestions
        $this->info('🧹 NETTOYAGE DES ANCIENNES SUGGESTIONS :');
        $oldCount = Suggestion::count();
        Suggestion::truncate();
        $this->line("   ✅ {$oldCount} anciennes suggestions supprimées");
        $this->newLine();

        // Générer de nouvelles suggestions avec la logique améliorée
        $this->info('🤖 GÉNÉRATION DE NOUVELLES SUGGESTIONS :');
        
        $suggestionService = app(IntelligentSuggestionService::class);
        $users = User::all();
        $totalSuggestions = 0;

        foreach ($users as $user) {
            $userSuggestions = $suggestionService->generateIntelligentSuggestions($user);
            $totalSuggestions += $userSuggestions;
            
            if ($userSuggestions > 0) {
                $this->line("   👤 {$user->name} : {$userSuggestions} suggestions générées");
            }
        }

        $this->line("   ✅ Total : {$totalSuggestions} suggestions générées");
        $this->newLine();

        // Vérifier les suggestions pour Leila
        $this->info('🔍 VÉRIFICATION DES SUGGESTIONS POUR LEILA :');
        
        $leila = User::where('email', 'leila.mansouri@example.com')->first();
        if ($leila) {
            $leilaSuggestions = Suggestion::where('user_id', $leila->id)
                ->with(['suggestedUser'])
                ->get();

            if ($leilaSuggestions->isNotEmpty()) {
                foreach ($leilaSuggestions as $suggestion) {
                    $suggestedUser = $suggestion->suggestedUser;
                    $relationCode = $suggestion->suggested_relation_code;
                    $this->line("   👩 Leila → {$suggestedUser->name} : {$relationCode}");
                }
            } else {
                $this->line("   ℹ️ Aucune suggestion pour Leila");
            }
        }
        $this->newLine();

        // Vérifier qu'il n'y a plus de suggestions incohérentes
        $this->info('🔍 VÉRIFICATION DES INCOHÉRENCES :');
        
        $grandparentSuggestions = Suggestion::whereIn('suggested_relation_code', [
            'grandfather_paternal', 'grandmother_paternal', 
            'grandfather_maternal', 'grandmother_maternal'
        ])->with(['user', 'suggestedUser'])->get();

        $inconsistentCount = 0;
        foreach ($grandparentSuggestions as $suggestion) {
            $user = $suggestion->user;
            $suggestedUser = $suggestion->suggestedUser;
            
            // Vérifier s'ils ont des parents communs
            $commonParents = $this->findCommonParents($user, $suggestedUser);
            
            if ($commonParents->isNotEmpty()) {
                $this->line("   ⚠️ Suggestion potentiellement incohérente : {$user->name} → {$suggestedUser->name} ({$suggestion->suggested_relation_code})");
                $inconsistentCount++;
            }
        }

        if ($inconsistentCount === 0) {
            $this->line("   ✅ Aucune suggestion incohérente détectée");
        } else {
            $this->line("   ⚠️ {$inconsistentCount} suggestions potentiellement incohérentes trouvées");
        }
        $this->newLine();

        // Résumé
        $this->info('📊 RÉSUMÉ :');
        $this->line("   Suggestions générées : {$totalSuggestions}");
        $this->line("   Suggestions grand-parent : " . $grandparentSuggestions->count());
        $this->line("   Incohérences détectées : {$inconsistentCount}");
        $this->newLine();

        if ($inconsistentCount === 0) {
            $this->info('🎉 SUCCÈS ! La logique de validation générationnelle fonctionne correctement.');
        } else {
            $this->warn('⚠️ Des incohérences persistent. Révision nécessaire.');
        }

        return 0;
    }

    private function findCommonParents(User $user1, User $user2)
    {
        $user1Parents = \App\Models\FamilyRelationship::where('user_id', $user1->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->pluck('related_user_id');

        $user2Parents = \App\Models\FamilyRelationship::where('user_id', $user2->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->pluck('related_user_id');

        return $user1Parents->intersect($user2Parents);
    }
}
