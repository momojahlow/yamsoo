<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\GeminiRelationshipService;
use App\Services\SuggestionService;

class TestGeminiSuggestions extends Command
{
    protected $signature = 'test:gemini-suggestions';
    protected $description = 'Test des suggestions intelligentes avec Gemini AI';

    public function handle()
    {
        $this->info('🤖 TEST DES SUGGESTIONS INTELLIGENTES AVEC GEMINI AI');
        $this->info('=======================================================');

        // Vérifier la configuration Gemini
        $apiKey = config('services.gemini.api_key');
        if (!$apiKey) {
            $this->error('❌ GEMINI_API_KEY non configuré dans .env');
            return;
        }

        $this->info("✅ Gemini API Key configuré: " . substr($apiKey, 0, 10) . "...");

        // Test 1: Analyse de relation simple
        $this->info("\n🔍 TEST 1: Analyse de relation simple");
        $this->testSimpleRelationAnalysis();

        // Test 2: Suggestions pour un utilisateur
        $this->info("\n🔍 TEST 2: Génération de suggestions pour un utilisateur");
        $this->testUserSuggestions();

        // Test 3: Scénario complet avec Gemini
        $this->info("\n🔍 TEST 3: Scénario complet avec suggestions Gemini");
        $this->testCompleteScenario();

        $this->info("\n🎉 Tests terminés !");
    }

    private function testSimpleRelationAnalysis()
    {
        try {
            $geminiService = app(GeminiRelationshipService::class);
            
            // Récupérer deux utilisateurs pour test
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            $fatima = User::where('email', 'fatima.zahra@example.com')->first();

            if (!$ahmed || !$fatima) {
                $this->error('❌ Utilisateurs de test non trouvés');
                return;
            }

            $this->info("Analyse de relation entre {$ahmed->name} et {$fatima->name}...");

            $analysis = $geminiService->analyzeRelationship($ahmed, $fatima);

            if ($analysis) {
                $this->info("✅ Analyse réussie:");
                $this->info("   Relation suggérée: {$analysis['relation_code']} ({$analysis['relation_name']})");
                $this->info("   Confiance: " . round($analysis['confidence'] * 100) . "%");
                $this->info("   Raisonnement: {$analysis['reasoning']}");
            } else {
                $this->warn("⚠️ Aucune analyse retournée par Gemini");
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de l'analyse: " . $e->getMessage());
        }
    }

    private function testUserSuggestions()
    {
        try {
            $suggestionService = app(SuggestionService::class);
            
            // Récupérer Ahmed
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            if (!$ahmed) {
                $this->error('❌ Ahmed non trouvé');
                return;
            }

            $this->info("Génération de suggestions pour {$ahmed->name}...");

            // Supprimer les anciennes suggestions pour test propre
            \App\Models\Suggestion::where('user_id', $ahmed->id)->delete();

            $suggestions = $suggestionService->generateSuggestions($ahmed);

            $this->info("📊 Nombre de suggestions générées: " . count($suggestions));

            foreach ($suggestions as $suggestion) {
                if (isset($suggestion->suggestedUser)) {
                    $this->info("   {$ahmed->name} → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
                    $this->info("     Type: {$suggestion->type} | Message: {$suggestion->message}");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors de la génération de suggestions: " . $e->getMessage());
        }
    }

    private function testCompleteScenario()
    {
        try {
            $this->info("🔄 Reset de la base de données...");
            
            // Reset de la base de données
            $this->call('migrate:fresh', ['--seed' => true]);

            $this->info("✅ Base de données réinitialisée");

            // Récupérer les utilisateurs
            $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
            $fatima = User::where('email', 'fatima.zahra@example.com')->first();
            $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
            $amina = User::where('email', 'amina.tazi@example.com')->first();

            if (!$ahmed || !$fatima || !$mohammed || !$amina) {
                $this->error('❌ Utilisateurs de test non trouvés');
                return;
            }

            $this->info("✅ Utilisateurs trouvés");

            // Créer quelques relations de base
            $this->info("\n📋 Création de relations de base...");
            
            $familyRelationService = app(\App\Services\FamilyRelationService::class);
            
            // Récupérer les types de relations
            $husbandType = \App\Models\RelationshipType::where('name', 'husband')->first();
            $fatherType = \App\Models\RelationshipType::where('name', 'father')->first();

            if (!$husbandType || !$fatherType) {
                $this->error('❌ Types de relations non trouvés');
                return;
            }

            // Ahmed épouse Fatima
            $request1 = $familyRelationService->createRelationshipRequest(
                $ahmed,
                $fatima->id,
                $husbandType->id,
                'Test Gemini'
            );

            // Ahmed père de Mohammed
            $request2 = $familyRelationService->createRelationshipRequest(
                $ahmed,
                $mohammed->id,
                $fatherType->id,
                'Test Gemini'
            );

            $this->info("✅ Relations de base créées");

            // Accepter les relations pour déclencher les suggestions
            $this->info("📋 Acceptation des relations...");

            $familyRelationService->acceptRelationshipRequest($request1);
            $familyRelationService->acceptRelationshipRequest($request2);

            $this->info("✅ Relations acceptées");

            // Traiter les jobs de suggestions
            $this->info("\n📋 Traitement des suggestions avec Gemini...");
            $this->call('queue:work', ['--stop-when-empty' => true]);

            // Vérifier les suggestions générées
            $this->info("\n📊 Suggestions générées avec Gemini:");
            
            $allUsers = [$ahmed, $fatima, $mohammed, $amina];
            
            foreach ($allUsers as $user) {
                $suggestions = \App\Models\Suggestion::where('user_id', $user->id)
                    ->with('suggestedUser')
                    ->get();
                
                $this->info("\n🔍 {$user->name}:");
                if ($suggestions->count() > 0) {
                    foreach ($suggestions as $suggestion) {
                        $type = $suggestion->type === 'gemini_ai' ? '🤖 GEMINI' : '🔧 CLASSIQUE';
                        $this->info("   {$type}: {$suggestion->suggestedUser->name} → {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
                    }
                } else {
                    $this->info("   (Aucune suggestion)");
                }
            }

        } catch (\Exception $e) {
            $this->error("❌ Erreur lors du scénario complet: " . $e->getMessage());
        }
    }
}
