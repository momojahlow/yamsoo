<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Services\SuggestionService;

class CreateTestSuggestions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:create-suggestions';

    /**
     * The description of the console command.
     */
    protected $description = 'Créer des suggestions de test pour vérifier le filtrage';

    protected SuggestionService $suggestionService;

    public function __construct(SuggestionService $suggestionService)
    {
        parent::__construct();
        $this->suggestionService = $suggestionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Création de suggestions de test');
        $this->newLine();

        // Récupérer les utilisateurs
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $aicha = User::where('email', 'aicha.idrissi@example.com')->first();

        if (!$fatima || !$ahmed || !$mohammed || !$youssef || !$aicha) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        // Créer des suggestions pour Fatima (qui a déjà des relations avec Ahmed et Mohammed)
        $this->info('📝 Création de suggestions pour Fatima...');
        
        // Suggestion avec Youssef (qui n'est pas encore en relation directe avec Fatima)
        $this->suggestionService->createSuggestion(
            $fatima,
            $youssef->id,
            'family',
            'Suggestion automatique basée sur les relations familiales',
            'uncle_paternal'
        );
        $this->info("   ✅ Suggestion créée : Youssef comme oncle paternel de Fatima");

        // Suggestion avec Aicha (qui n'a aucune relation)
        $this->suggestionService->createSuggestion(
            $fatima,
            $aicha->id,
            'family',
            'Suggestion automatique',
            'sister'
        );
        $this->info("   ✅ Suggestion créée : Aicha comme sœur de Fatima");

        // Créer des suggestions pour Ahmed (qui a déjà des relations)
        $this->info('📝 Création de suggestions pour Ahmed...');
        
        $this->suggestionService->createSuggestion(
            $ahmed,
            $aicha->id,
            'family',
            'Suggestion automatique',
            'daughter'
        );
        $this->info("   ✅ Suggestion créée : Aicha comme fille d'Ahmed");

        // Créer une suggestion qui devrait être filtrée (relation existante)
        $this->suggestionService->createSuggestion(
            $fatima,
            $ahmed->id,
            'family',
            'Cette suggestion devrait être filtrée car Ahmed est déjà le père de Fatima',
            'father'
        );
        $this->info("   ⚠️  Suggestion créée avec Ahmed (devrait être filtrée)");

        $this->newLine();
        $this->info('🔍 Vérification des suggestions après filtrage :');

        // Vérifier les suggestions pour Fatima
        $fatimasSuggestions = $this->suggestionService->getUserSuggestions($fatima);
        $this->info("   👩 Fatima a {$fatimasSuggestions->count()} suggestion(s) :");
        foreach ($fatimasSuggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relation = $suggestion->suggested_relation_name ?? 'Non définie';
            $this->line("     - {$suggestedUser->name} ({$relation})");
        }

        // Vérifier les suggestions pour Ahmed
        $ahmedsSuggestions = $this->suggestionService->getUserSuggestions($ahmed);
        $this->info("   👨 Ahmed a {$ahmedsSuggestions->count()} suggestion(s) :");
        foreach ($ahmedsSuggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relation = $suggestion->suggested_relation_name ?? 'Non définie';
            $this->line("     - {$suggestedUser->name} ({$relation})");
        }

        $this->newLine();
        $this->info('✅ Test des suggestions terminé !');
        $this->info('💡 Les suggestions avec des personnes déjà en relation devraient être automatiquement filtrées.');
        
        return 0;
    }
}
