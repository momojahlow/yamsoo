<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Services\SuggestionService;
use App\Services\FamilyRelationService;

class TestScenarioComplet extends Command
{
    protected $signature = 'test:scenario-complet';
    protected $description = 'Test du scénario complet de relations familiales avec suggestions Gemini';

    public function handle()
    {
        $this->info("🔍 TEST SCÉNARIO SPÉCIFIQUE: AHMED + FATIMA + ENFANTS");
        $this->info("=======================================================");
        $this->info("📝 Scénario: Ahmed ajoute Fatima comme épouse, puis Mohammed et Amina comme ses enfants");
        $this->info("🎯 Objectif: Fatima doit être suggérée comme 'mère' (mother) pour Mohammed et Amina, pas 'belle-mère'");
        $this->info("");
        
        // Reset complet de la base de données
        $this->info("🔄 Reset de la base de données...");
        $this->call('migrate:fresh', ['--seed' => true]);
        $this->info("✅ Base de données réinitialisée");
        
        // Récupérer les utilisateurs de test
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $fatima = User::where('email', 'fatima.zahra@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $amina = User::where('email', 'amina.tazi@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();
        $karim = User::where('email', 'karim.elfassi@example.com')->first();
        $leila = User::where('email', 'leila.mansouri@example.com')->first();
        
        if (!$ahmed || !$fatima || !$mohammed || !$amina || !$youssef || !$karim || !$leila) {
            $this->error("❌ Utilisateurs de test non trouvés");
            return;
        }
        
        $this->info("✅ Utilisateurs trouvés");
        
        $familyService = app(FamilyRelationService::class);
        $suggestionService = app(SuggestionService::class);
        
        // ÉTAPE 1: Ahmed crée les demandes dans l'ordre spécifique
        $this->info("");
        $this->info("📋 ÉTAPE 1: Ahmed crée les demandes dans l'ordre...");

        // 1. Ahmed → Fatima (épouse) - PREMIER
        $this->createAndAcceptRelation($familyService, $ahmed, $fatima, 'wife', 'husband');
        $this->info("✅ 1. Ahmed ↔ Fatima (époux/épouse) - MARIAGE ÉTABLI");

        // Attendre un peu pour s'assurer que les timestamps sont différents
        sleep(1);

        // 2. Ahmed → Mohammed (fils) - APRÈS LE MARIAGE
        $this->createAndAcceptRelation($familyService, $ahmed, $mohammed, 'son', 'father');
        $this->info("✅ 2. Ahmed ↔ Mohammed (père/fils) - ENFANT AJOUTÉ");

        // 3. Ahmed → Amina (fille) - APRÈS LE MARIAGE
        $this->createAndAcceptRelation($familyService, $ahmed, $amina, 'daughter', 'father');
        $this->info("✅ 3. Ahmed ↔ Amina (père/fille) - ENFANT AJOUTÉ");
        
        // Générer suggestions après étape 1 - FOCUS SUR LE PROBLÈME
        $this->info("");
        $this->info("📊 VÉRIFICATION DES SUGGESTIONS:");
        $this->info("🎯 Mohammed devrait voir Fatima comme 'mère' (mother), pas 'belle-mère' (stepmother)");
        $this->info("🎯 Amina devrait voir Fatima comme 'mère' (mother), pas 'belle-mère' (stepmother)");
        $this->info("");

        $this->generateSuggestionsForUsers([$mohammed, $amina, $fatima]);
        $this->displaySuggestions("Après étape 1", [$mohammed, $amina, $fatima]);

        
        $this->info("");
        $this->info("🎉 Scénario complet terminé !");
    }
    
    /**
     * Créer et accepter une relation bidirectionnelle
     */
    private function createAndAcceptRelation(FamilyRelationService $familyService, User $user1, User $user2, string $relation1to2, string $relation2to1)
    {
        // Récupérer les types de relations
        $relationType1 = RelationshipType::where('name', $relation1to2)->first();
        
        if (!$relationType1) {
            $this->error("❌ Type de relation non trouvé: {$relation1to2}");
            return;
        }
        
        // Créer la demande via le service
        $request = $familyService->createRelationshipRequest(
            $user1,
            $user2->id,
            $relationType1->id,
            "Demande de relation: {$relation1to2}"
        );
        
        // Accepter la demande (cela devrait créer les relations bidirectionnelles automatiquement)
        $familyService->acceptRelationshipRequest($request);
    }
    
    /**
     * Générer suggestions pour une liste d'utilisateurs
     */
    private function generateSuggestionsForUsers(array $users)
    {
        foreach ($users as $user) {
            try {
                // Déclencher la génération de suggestions directement
                $suggestionService = app(SuggestionService::class);
                $suggestionService->generateSuggestions($user);
            } catch (\Exception $e) {
                $this->warn("Erreur génération suggestions pour {$user->name}: " . $e->getMessage());
            }
        }

        // Traiter les jobs de queue
        $this->call('queue:work', ['--stop-when-empty' => true]);
    }
    
    /**
     * Afficher les suggestions pour une liste d'utilisateurs
     */
    private function displaySuggestions(string $phase, array $users)
    {
        $this->info("");
        $this->info("📊 Suggestions {$phase}:");
        
        $suggestionService = app(SuggestionService::class);
        
        foreach ($users as $user) {
            $suggestions = $suggestionService->getUserSuggestions($user);
            $this->info("🔍 {$user->name}:");
            
            if ($suggestions->isEmpty()) {
                $this->info("   (Aucune suggestion)");
            } else {
                foreach ($suggestions as $suggestion) {
                    $type = $suggestion->type ?? 'classique';
                    $icon = $type === 'gemini' ? '🤖 GEMINI' : '🔧 CLASSIQUE';
                    $this->info("   {$icon}: {$suggestion->suggestedUser->name} → {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
                }
            }
            $this->info("");
        }
    }
}
