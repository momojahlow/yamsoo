<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\IntelligentSuggestionService;

class GenerateIntelligentSuggestions extends Command
{
    protected $signature = 'generate:intelligent-suggestions {--user-email= : Email de l\'utilisateur spécifique}';
    protected $description = 'Générer des suggestions intelligentes basées sur les relations existantes';

    protected IntelligentSuggestionService $intelligentSuggestionService;

    public function __construct(IntelligentSuggestionService $intelligentSuggestionService)
    {
        parent::__construct();
        $this->intelligentSuggestionService = $intelligentSuggestionService;
    }

    public function handle()
    {
        $this->info('🧠 GÉNÉRATION DE SUGGESTIONS INTELLIGENTES');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        $userEmail = $this->option('user-email');

        if ($userEmail) {
            // Générer pour un utilisateur spécifique
            $user = User::where('email', $userEmail)->first();
            
            if (!$user) {
                $this->error("❌ Utilisateur avec l'email {$userEmail} non trouvé");
                return 1;
            }

            $this->info("👤 Génération pour : {$user->name}");
            $suggestions = $this->intelligentSuggestionService->generateIntelligentSuggestions($user);
            $this->info("💡 Suggestions créées : {$suggestions}");
        } else {
            // Générer pour tous les utilisateurs
            $this->info("👥 Génération pour tous les utilisateurs...");
            $totalSuggestions = $this->intelligentSuggestionService->generateSuggestionsForAllUsers();
            $this->info("💡 Total suggestions créées : {$totalSuggestions}");
        }

        $this->newLine();
        $this->info('✅ Génération terminée !');

        // Afficher un résumé
        $this->displaySummary();

        return 0;
    }

    private function displaySummary(): void
    {
        $this->info('📊 RÉSUMÉ DES SUGGESTIONS :');
        
        $users = User::whereIn('email', [
            'fatima.zahra@example.com',
            'ahmed.benali@example.com',
            'mohammed.alami@example.com',
            'youssef.bennani@example.com'
        ])->get();

        foreach ($users as $user) {
            $suggestions = \App\Models\Suggestion::where('user_id', $user->id)
                ->where('status', 'pending')
                ->with('suggestedUser')
                ->get();

            $this->line("   👤 {$user->name} : {$suggestions->count()} suggestions");
            
            foreach ($suggestions->take(3) as $suggestion) {
                $relationName = $suggestion->suggested_relation_name ?: $suggestion->suggested_relation_code;
                $this->line("      - {$suggestion->suggestedUser->name} ({$relationName})");
            }
            
            if ($suggestions->count() > 3) {
                $this->line("      ... et " . ($suggestions->count() - 3) . " autres");
            }
        }
    }
}
