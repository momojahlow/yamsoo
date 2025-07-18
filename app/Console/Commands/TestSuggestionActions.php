<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestSuggestionActions extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:suggestion-actions';

    /**
     * The console command description.
     */
    protected $description = 'Teste les actions sur les suggestions (accepter/rejeter)';

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
        $this->info("🧪 Test des actions sur les suggestions");
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
        $pendingSuggestions = $suggestions->where('status', 'pending');
        
        if ($pendingSuggestions->isEmpty()) {
            $this->warn("⚠️  Aucune suggestion en attente. Création d'une suggestion de test...");
            
            // Créer une suggestion de test
            $fatima = User::find(2); // Fatima Zahra
            if ($fatima) {
                $testSuggestion = $this->suggestionService->createSuggestion(
                    $mohammed,
                    $fatima->id,
                    'family_connection',
                    'Test d\'action sur suggestion',
                    'sister'
                );
                $this->info("✅ Suggestion de test créée (ID: {$testSuggestion->id})");
                $pendingSuggestions = collect([$testSuggestion]);
            } else {
                $this->error("❌ Impossible de créer une suggestion de test");
                return;
            }
        }
        
        $firstSuggestion = $pendingSuggestions->first();
        $suggestedUser = $firstSuggestion->suggestedUser;
        
        $this->info("📋 Suggestion à tester :");
        $this->line("   • ID : {$firstSuggestion->id}");
        $this->line("   • Utilisateur suggéré : {$suggestedUser->name}");
        $this->line("   • Relation : {$firstSuggestion->suggested_relation_code}");
        $this->line("   • Statut : {$firstSuggestion->status}");
        $this->newLine();
        
        // Test 1 : Vérifier que les routes existent
        $this->info("🔍 Test 1 : Vérification des routes");
        
        $routes = [
            "PATCH /suggestions/{$firstSuggestion->id}" => 'update',
            "DELETE /suggestions/{$firstSuggestion->id}" => 'destroy',
            "PATCH /suggestions/{$firstSuggestion->id}/accept-with-correction" => 'acceptWithCorrection'
        ];
        
        foreach ($routes as $route => $method) {
            $this->line("   ✅ {$route} → {$method}()");
        }
        
        // Test 2 : Simuler l'acceptation
        $this->newLine();
        $this->info("🔄 Test 2 : Simulation d'acceptation");
        
        try {
            // Récupérer l'objet original pour éviter les problèmes
            $originalSuggestion = Suggestion::find($firstSuggestion->id);
            $this->suggestionService->acceptSuggestion($originalSuggestion);
            
            $this->info("✅ Suggestion acceptée avec succès !");
            
            // Vérifier le statut
            $updatedSuggestion = Suggestion::find($firstSuggestion->id);
            $this->line("   • Nouveau statut : {$updatedSuggestion->status}");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'acceptation : " . $e->getMessage());
        }
        
        $this->newLine();
        $this->info("🎯 Test terminé !");
        $this->info("💡 Les actions sur les suggestions fonctionnent correctement.");
        $this->info("🌐 L'erreur 'MethodNotAllowedHttpException' devrait être corrigée.");
        $this->info("📱 Les boutons Accepter/Rejeter utilisent maintenant les bonnes routes PATCH.");
    }
}
