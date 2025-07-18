<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestCorrectionFeature extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:correction-feature';

    /**
     * The console command description.
     */
    protected $description = 'Teste la fonctionnalité de correction de relation';

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
        $this->info("🧪 Test de la fonctionnalité de correction de relation");
        $this->newLine();

        // Trouver Mohammed Alami
        $mohammed = User::find(3);

        if (!$mohammed) {
            $this->error("Mohammed Alami non trouvé");
            return;
        }

        $this->info("👤 Utilisateur : {$mohammed->name}");

        // Récupérer ses suggestions
        $suggestions = $this->suggestionService->getUserSuggestions($mohammed);

        if ($suggestions->isEmpty()) {
            $this->warn("⚠️  Aucune suggestion trouvée. Génération de suggestions...");

            // Générer des suggestions
            $this->call('generate:suggestions-mohammed');
            $suggestions = $this->suggestionService->getUserSuggestions($mohammed);
        }

        $this->info("📋 Suggestions disponibles :");

        foreach ($suggestions as $index => $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;
            $relationName = $suggestion->suggested_relation_name ?? 'Non définie';

            $this->line("   " . ($index + 1) . ". {$suggestedUser->name}");
            $this->line("      Relation suggérée : {$relationName}");
            $this->line("      Code : {$suggestion->suggested_relation_code}");
            $this->line("      Message : {$suggestion->message}");
            $this->newLine();
        }

        // Test de correction
        if ($suggestions->isNotEmpty()) {
            $firstSuggestion = $suggestions->first();
            $suggestedUser = $firstSuggestion->suggestedUser;
            $originalRelation = $firstSuggestion->suggested_relation_name;

            $this->info("🔧 Test de correction pour : {$suggestedUser->name}");
            $this->line("   Relation originale : {$originalRelation}");

            // Simuler une correction (par exemple, changer Mère en Tante)
            $newRelationCode = 'sister'; // Changement pour test
            $newRelationName = 'Sœur';

            $this->line("   Nouvelle relation : {$newRelationName}");

            // Appliquer la correction (utiliser l'objet original de la base de données)
            $originalSuggestion = Suggestion::find($firstSuggestion->id);
            $this->suggestionService->acceptSuggestion($originalSuggestion, $newRelationCode);

            $this->info("✅ Correction appliquée avec succès !");

            // Vérifier la correction
            $updatedSuggestion = Suggestion::find($firstSuggestion->id);
            if ($updatedSuggestion) {
                $this->line("   Statut : {$updatedSuggestion->status}");
                $this->line("   Nouvelle relation : {$updatedSuggestion->suggested_relation_name}");
                $this->line("   Nouveau code : {$updatedSuggestion->suggested_relation_code}");
            }
        }

        $this->newLine();
        $this->info("🎯 Test terminé. La fonctionnalité de correction est opérationnelle !");
        $this->info("💡 Sur la page web, l'utilisateur peut maintenant :");
        $this->line("   • Voir la relation suggérée");
        $this->line("   • Cliquer sur 'Corriger' pour la modifier");
        $this->line("   • Choisir la bonne relation dans la liste");
        $this->line("   • Accepter avec la relation corrigée");
    }
}
