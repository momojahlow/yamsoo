<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class TestCompleteScenarioAnalysis extends Command
{
    protected $signature = 'test:complete-scenario-analysis';
    protected $description = 'Test complet du scénario avec analyse détaillée des modèles et relations';

    private FamilyRelationService $familyRelationService;
    private SuggestionService $suggestionService;

    public function __construct(FamilyRelationService $familyRelationService, SuggestionService $suggestionService)
    {
        parent::__construct();
        $this->familyRelationService = $familyRelationService;
        $this->suggestionService = $suggestionService;
    }

    public function handle()
    {
        $this->info('🎯 ANALYSE COMPLÈTE DU SYSTÈME DE RELATIONS FAMILIALES');
        $this->info('================================================================');

        // 1. Analyse des modèles et structure de base
        $this->analyzeModelsStructure();

        // 2. Reset et préparation
        $this->info("\n🔄 Reset de la base de données...");
        $this->call('migrate:fresh', ['--seed' => true]);

        // 3. Récupération des utilisateurs
        $users = $this->getUsers();
        if (!$users) {
            $this->error('❌ Impossible de récupérer les utilisateurs');
            return;
        }

        // 4. Test du scénario complet
        $this->testCompleteScenario($users);

        $this->info("\n🎉 Analyse terminée !");
    }

    private function analyzeModelsStructure()
    {
        $this->info("\n📊 ANALYSE DES MODÈLES ET STRUCTURE");
        $this->info("=====================================");

        // Analyser User
        $this->info("\n🔍 Modèle User:");
        $userFillable = (new User())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $userFillable));

        // Analyser Profile
        $this->info("\n🔍 Modèle Profile:");
        $profileFillable = (new \App\Models\Profile())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $profileFillable));

        // Analyser FamilyRelationship
        $this->info("\n🔍 Modèle FamilyRelationship:");
        $familyRelFillable = (new FamilyRelationship())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $familyRelFillable));

        // Analyser RelationshipRequest
        $this->info("\n🔍 Modèle RelationshipRequest:");
        $relReqFillable = (new RelationshipRequest())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $relReqFillable));

        // Analyser RelationshipType
        $this->info("\n🔍 Modèle RelationshipType:");
        $relTypeFillable = (new RelationshipType())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $relTypeFillable));

        // Analyser Suggestion
        $this->info("\n🔍 Modèle Suggestion:");
        $suggestionFillable = (new Suggestion())->getFillable();
        $this->info("   Champs fillable: " . implode(', ', $suggestionFillable));

        // Analyser les types de relations disponibles
        $this->info("\n🔍 Types de relations disponibles:");
        $relationTypes = RelationshipType::all();
        foreach ($relationTypes as $type) {
            $this->info("   - {$type->name} ({$type->display_name_fr}) - Catégorie: {$type->category}");
        }
    }

    private function getUsers()
    {
        $users = [];
        $userEmails = [
            'Ahmed' => 'ahmed.benali@example.com',
            'Fatima' => 'fatima.zahra@example.com',
            'Mohammed' => 'mohammed.alami@example.com',
            'Amina' => 'amina.tazi@example.com',
            'Youssef' => 'youssef.bennani@example.com',
            'Leila' => 'leila.mansouri@example.com',
            'Karim' => 'karim.elfassi@example.com',
        ];

        foreach ($userEmails as $name => $email) {
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->error("❌ Utilisateur {$name} non trouvé");
                return null;
            }
            $users[$name] = $user;
            $this->info("✅ {$name} trouvé (ID: {$user->id})");
        }

        return $users;
    }

    private function testCompleteScenario($users)
    {
        $this->info("\n🎯 TEST DU SCÉNARIO COMPLET");
        $this->info("============================");

        // PHASE 1: Ahmed crée les demandes
        $this->info("\n📋 PHASE 1: Ahmed crée les demandes");
        $this->createAhmedRequests($users);

        // PHASE 2: Amina crée les demandes
        $this->info("\n📋 PHASE 2: Amina crée les demandes");
        $this->createAminaRequests($users);

        // PHASE 3: Amina crée demande pour Leila
        $this->info("\n📋 PHASE 3: Amina crée demande pour Leila");
        $this->createAminaLeilaRequest($users);

        // PHASE 4: Analyse finale
        $this->info("\n📋 PHASE 4: Analyse finale des relations et suggestions");
        $this->analyzeFinalState($users);
    }

    private function createAhmedRequests($users)
    {
        // Ahmed → Fatima (épouse)
        $wifeType = RelationshipType::where('name', 'wife')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $users['Ahmed'], $users['Fatima']->id, $wifeType->id, 'Ahmed épouse Fatima'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        $this->info("✅ Ahmed ↔ Fatima (époux)");

        // Ahmed → Mohammed (fils)
        $sonType = RelationshipType::where('name', 'son')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $users['Ahmed'], $users['Mohammed']->id, $sonType->id, 'Ahmed père de Mohammed'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        $this->info("✅ Ahmed → Mohammed (père)");

        // Ahmed → Amina (fille)
        $daughterType = RelationshipType::where('name', 'daughter')->first();
        $request3 = $this->familyRelationService->createRelationshipRequest(
            $users['Ahmed'], $users['Amina']->id, $daughterType->id, 'Ahmed père d\'Amina'
        );
        $this->familyRelationService->acceptRelationshipRequest($request3);
        $this->info("✅ Ahmed → Amina (père)");

        // Traitement des jobs de suggestions
        $this->info("📋 Traitement des jobs de suggestions...");
        $this->call('queue:work', ['--stop-when-empty' => true]);
    }

    private function createAminaRequests($users)
    {
        // Amina → Youssef (mari)
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $users['Amina'], $users['Youssef']->id, $husbandType->id, 'Amina épouse Youssef'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        $this->info("✅ Amina ↔ Youssef (époux)");

        // Amina → Karim (fils)
        $sonType = RelationshipType::where('name', 'son')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $users['Amina'], $users['Karim']->id, $sonType->id, 'Amina mère de Karim'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        $this->info("✅ Amina → Karim (mère)");

        // Traitement des jobs de suggestions
        $this->info("📋 Traitement des jobs de suggestions...");
        $this->call('queue:work', ['--stop-when-empty' => true]);
    }

    private function createAminaLeilaRequest($users)
    {
        // Amina → Leila (sœur)
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $request = $this->familyRelationService->createRelationshipRequest(
            $users['Amina'], $users['Leila']->id, $sisterType->id, 'Amina sœur de Leila'
        );
        $this->familyRelationService->acceptRelationshipRequest($request);
        $this->info("✅ Amina ↔ Leila (sœur)");

        // Traitement des jobs de suggestions
        $this->info("📋 Traitement des jobs de suggestions...");
        $this->call('queue:work', ['--stop-when-empty' => true]);
    }

    private function analyzeFinalState($users)
    {
        // Analyser toutes les relations créées
        $this->info("\n🔗 RELATIONS CRÉÉES:");
        $relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        foreach ($relations as $rel) {
            $this->info("   {$rel->user->name} → {$rel->relatedUser->name} : {$rel->relationshipType->name} ({$rel->relationshipType->display_name_fr})");
        }

        // Analyser toutes les suggestions générées
        $this->info("\n💡 SUGGESTIONS GÉNÉRÉES:");
        $suggestions = Suggestion::with(['user', 'suggestedUser'])->get();
        if ($suggestions->isEmpty()) {
            $this->info("   (Aucune suggestion)");
        } else {
            foreach ($suggestions as $suggestion) {
                $this->info("   {$suggestion->user->name} → {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
            }
        }

        // Vérifier les suggestions attendues selon le scénario
        $this->verifyExpectedSuggestions($users);
    }

    private function verifyExpectedSuggestions($users)
    {
        $this->info("\n✅ VÉRIFICATION DES SUGGESTIONS ATTENDUES:");
        
        $expectedSuggestions = [
            // Suggestions directes/proches
            'Mohammed' => [
                ['user' => 'Fatima', 'relation' => 'mother', 'description' => 'Fatima comme mère']
            ],
            'Amina' => [
                ['user' => 'Fatima', 'relation' => 'mother', 'description' => 'Fatima comme mère'],
                ['user' => 'Mohammed', 'relation' => 'brother', 'description' => 'Mohammed comme frère']
            ],
            'Karim' => [
                ['user' => 'Youssef', 'relation' => 'father', 'description' => 'Youssef comme père']
            ],
            'Youssef' => [
                ['user' => 'Karim', 'relation' => 'son', 'description' => 'Karim comme fils'],
                ['user' => 'Ahmed', 'relation' => 'father_in_law', 'description' => 'Ahmed comme beau-père'],
                ['user' => 'Fatima', 'relation' => 'mother_in_law', 'description' => 'Fatima comme belle-mère'],
                ['user' => 'Mohammed', 'relation' => 'brother_in_law', 'description' => 'Mohammed comme beau-frère'],
                ['user' => 'Leila', 'relation' => 'sister_in_law', 'description' => 'Leila comme belle-sœur']
            ],
            'Leila' => [
                ['user' => 'Ahmed', 'relation' => 'father', 'description' => 'Ahmed comme père'],
                ['user' => 'Fatima', 'relation' => 'mother', 'description' => 'Fatima comme mère'],
                ['user' => 'Mohammed', 'relation' => 'brother', 'description' => 'Mohammed comme frère'],
                ['user' => 'Youssef', 'relation' => 'brother_in_law', 'description' => 'Youssef comme beau-frère']
            ],
            // Suggestions de belles-familles
            'Ahmed' => [
                ['user' => 'Youssef', 'relation' => 'son_in_law', 'description' => 'Youssef comme gendre']
            ],
            'Fatima' => [
                ['user' => 'Youssef', 'relation' => 'son_in_law', 'description' => 'Youssef comme gendre']
            ],
            'Mohammed' => [
                ['user' => 'Youssef', 'relation' => 'brother_in_law', 'description' => 'Youssef comme beau-frère']
            ]
        ];

        foreach ($expectedSuggestions as $userName => $expected) {
            $user = $users[$userName];
            $actualSuggestions = Suggestion::where('user_id', $user->id)->with('suggestedUser')->get();
            
            $this->info("\n🔍 {$userName}:");
            $this->info("   Attendu: " . count($expected) . " suggestions");
            $this->info("   Réel: " . $actualSuggestions->count() . " suggestions");
            
            if ($actualSuggestions->isEmpty()) {
                $this->warn("   ❌ Aucune suggestion trouvée");
                foreach ($expected as $exp) {
                    $this->warn("      MANQUANT: {$exp['user']} comme {$exp['relation']} ({$exp['description']})");
                }
            } else {
                foreach ($actualSuggestions as $suggestion) {
                    $this->info("   ✅ {$suggestion->suggestedUser->name} : {$suggestion->suggested_relation_code} ({$suggestion->suggested_relation_name})");
                }
            }
        }
    }
}
