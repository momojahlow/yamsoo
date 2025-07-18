<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use App\Services\FamilyRelationService;

class DiagnosticComplet extends Command
{
    protected $signature = 'diagnostic:complet';
    protected $description = 'Diagnostic complet des problèmes';

    protected SuggestionService $suggestionService;
    protected FamilyRelationService $familyRelationService;

    public function __construct(SuggestionService $suggestionService, FamilyRelationService $familyRelationService)
    {
        parent::__construct();
        $this->suggestionService = $suggestionService;
        $this->familyRelationService = $familyRelationService;
    }

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC COMPLET DES PROBLÈMES');
        $this->info('═══════════════════════════════════');
        $this->newLine();

        // 1. Vérifier les relations en base
        $this->checkRelations();
        $this->newLine();

        // 2. Vérifier les suggestions en base
        $this->checkSuggestions();
        $this->newLine();

        // 3. Tester le service de suggestions
        $this->testSuggestionService();
        $this->newLine();

        // 4. Vérifier les statistiques
        $this->checkStatistics();

        return 0;
    }

    private function checkRelations(): void
    {
        $this->info('🔗 VÉRIFICATION DES RELATIONS :');
        
        $relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        $this->line("   Total relations en base : {$relations->count()}");
        
        if ($relations->count() > 0) {
            $this->line("   📋 Détail des relations :");
            foreach ($relations as $relation) {
                $auto = $relation->created_automatically ? ' 🤖' : ' 👤';
                $this->line("      - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr}{$auto}");
            }
        }
    }

    private function checkSuggestions(): void
    {
        $this->info('💡 VÉRIFICATION DES SUGGESTIONS EN BASE :');
        
        $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
        $this->line("   Total suggestions en base : {$suggestions->count()}");
        
        if ($suggestions->count() > 0) {
            $this->line("   📋 Détail des suggestions :");
            foreach ($suggestions->take(10) as $suggestion) {
                $this->line("      - Pour {$suggestion->user->name} : {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_code}) - Status: {$suggestion->status}");
            }
            
            if ($suggestions->count() > 10) {
                $this->line("      ... et " . ($suggestions->count() - 10) . " autres suggestions");
            }
        }
    }

    private function testSuggestionService(): void
    {
        $this->info('🧪 TEST DU SERVICE DE SUGGESTIONS :');
        
        $users = User::whereIn('email', [
            'fatima.zahra@example.com',
            'ahmed.benali@example.com',
            'youssef.bennani@example.com'
        ])->get();

        foreach ($users as $user) {
            $this->line("   👤 Test pour {$user->name} :");
            
            // Vérifier les relations existantes
            $relations = $this->familyRelationService->getUserRelationships($user);
            $this->line("      - Relations existantes : {$relations->count()}");
            
            // Tester le service de suggestions
            $suggestions = $this->suggestionService->getUserSuggestions($user);
            $this->line("      - Suggestions retournées : {$suggestions->count()}");
            
            if ($suggestions->count() > 0) {
                foreach ($suggestions->take(3) as $suggestion) {
                    $this->line("        • {$suggestion->suggestedUser->name} ({$suggestion->suggested_relation_name})");
                }
            } else {
                $this->line("        (Aucune suggestion)");
            }
            
            $this->newLine();
        }
    }

    private function checkStatistics(): void
    {
        $this->info('📊 VÉRIFICATION DES STATISTIQUES :');
        
        $users = User::whereIn('email', [
            'fatima.zahra@example.com',
            'ahmed.benali@example.com',
            'youssef.bennani@example.com'
        ])->get();

        foreach ($users as $user) {
            $relations = $this->familyRelationService->getUserRelationships($user);
            $statistics = $this->familyRelationService->getFamilyStatistics($user);
            
            $this->line("   👤 {$user->name} :");
            $this->line("      - Relations service : {$relations->count()}");
            $this->line("      - Statistiques total : {$statistics['total_relatives']}");
            $this->line("      - Relations automatiques : {$statistics['automatic_relations']}");
            $this->line("      - Relations manuelles : {$statistics['manual_relations']}");
        }
    }
}
