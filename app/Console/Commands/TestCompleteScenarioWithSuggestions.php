<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;
use Illuminate\Console\Command;

class TestCompleteScenarioWithSuggestions extends Command
{
    protected $signature = 'test:complete-scenario-suggestions';
    protected $description = 'Test du scénario complet avec suggestions automatiques';

    public function __construct(
        private FamilyRelationService $familyRelationService,
        private SuggestionService $suggestionService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🎯 Test du scénario complet avec suggestions automatiques');
        
        // Obtenir les utilisateurs existants
        $users = $this->getExistingUsers();
        
        // Phase 1: Ahmed crée ses demandes
        $this->testAhmedPhase($users);
        
        // Phase 2: Amina crée ses demandes
        $this->testAminaPhase($users);
        
        // Phase 3: Amina ajoute Leila
        $this->testLeilaPhase($users);
        
        // Vérification finale des suggestions
        $this->verifyFinalSuggestions($users);
        
        $this->info("\n🎉 Test complet terminé !");
    }

    private function getExistingUsers()
    {
        $userEmails = [
            'ahmed.benali@example.com' => 'Ahmed',
            'fatima.zahra@example.com' => 'Fatima',
            'mohammed.alami@example.com' => 'Mohammed',
            'amina.tazi@example.com' => 'Amina',
            'youssef.bennani@example.com' => 'Youssef',
            'karim.elfassi@example.com' => 'Karim',
            'leila.mansouri@example.com' => 'Leila',
        ];

        $users = [];
        foreach ($userEmails as $email => $shortName) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $users[$shortName] = $user;
                $this->info("✅ {$user->name} trouvé");
            } else {
                $this->error("❌ Utilisateur {$email} non trouvé");
            }
        }

        return $users;
    }

    private function testAhmedPhase($users)
    {
        $this->info("\n📋 PHASE 1: Ahmed crée les demandes Fatima (épouse), Mohammed (fils), Amina (fille)");
        
        $ahmed = $users['Ahmed'];
        $fatima = $users['Fatima'];
        $mohammed = $users['Mohammed'];
        $amina = $users['Amina'];
        
        try {
            // 1. Ahmed → Fatima (épouse)
            $wifeType = RelationshipType::where('name', 'wife')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest($ahmed, $fatima->id, $wifeType->id, 'Ahmed demande Fatima comme épouse');
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Fatima accepte → devient épouse d'Ahmed");
            
            // 2. Ahmed → Mohammed (fils)
            $sonType = RelationshipType::where('name', 'son')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest($ahmed, $mohammed->id, $sonType->id, 'Ahmed demande Mohammed comme fils');
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Mohammed accepte → devient fils d'Ahmed");
            
            // Vérifier suggestions pour Mohammed
            $this->checkSuggestionsFor($mohammed, ['Fatima comme mère']);
            
            // 3. Ahmed → Amina (fille)
            $daughterType = RelationshipType::where('name', 'daughter')->first();
            $request3 = $this->familyRelationService->createRelationshipRequest($ahmed, $amina->id, $daughterType->id, 'Ahmed demande Amina comme fille');
            $this->familyRelationService->acceptRelationshipRequest($request3);
            $this->info("✅ Amina accepte → devient fille d'Ahmed");
            
            // Vérifier suggestions pour Amina
            $this->checkSuggestionsFor($amina, ['Fatima comme mère', 'Mohammed comme frère']);
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans phase Ahmed: " . $e->getMessage());
        }
    }

    private function testAminaPhase($users)
    {
        $this->info("\n📋 PHASE 2: Amina crée les demandes Youssef (mari), Karim (fils)");
        
        $amina = $users['Amina'];
        $youssef = $users['Youssef'];
        $karim = $users['Karim'];
        
        try {
            // 1. Amina → Karim (fils) - AVANT le mariage
            $sonType = RelationshipType::where('name', 'son')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest($amina, $karim->id, $sonType->id, 'Amina demande Karim comme fils');
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Karim accepte → devient fils d'Amina");
            
            // Vérifier qu'il n'y a PAS encore de suggestion Youssef comme père pour Karim
            $this->info("🔍 Vérification: Karim ne devrait PAS encore avoir Youssef comme père suggéré");
            
            // 2. Amina → Youssef (mari)
            $husbandType = RelationshipType::where('name', 'husband')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest($amina, $youssef->id, $husbandType->id, 'Amina demande Youssef comme mari');
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Youssef accepte → devient mari d'Amina");
            
            // Maintenant vérifier les suggestions déclenchées par le mariage
            $this->info("\n🔍 Suggestions déclenchées par le mariage Amina-Youssef:");
            
            // Suggestions directes
            $this->checkSuggestionsFor($karim, ['Youssef comme père']);
            $this->checkSuggestionsFor($youssef, ['Karim comme fils']);
            
            // Suggestions de belle-famille pour Youssef
            $this->checkSuggestionsFor($youssef, [
                'Ahmed comme beau-père',
                'Fatima comme belle-mère', 
                'Mohammed comme beau-frère'
            ]);
            
            // Suggestions réciproques
            $this->checkSuggestionsFor($users['Ahmed'], ['Youssef comme gendre']);
            $this->checkSuggestionsFor($users['Fatima'], ['Youssef comme gendre']);
            $this->checkSuggestionsFor($users['Mohammed'], ['Youssef comme beau-frère']);
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans phase Amina: " . $e->getMessage());
        }
    }

    private function testLeilaPhase($users)
    {
        $this->info("\n📋 PHASE 3: Amina crée demande Leila (sœur)");
        
        $amina = $users['Amina'];
        $leila = $users['Leila'];
        
        try {
            // Amina → Leila (sœur)
            $sisterType = RelationshipType::where('name', 'sister')->first();
            $request = $this->familyRelationService->createRelationshipRequest($amina, $leila->id, $sisterType->id, 'Amina demande Leila comme sœur');
            $this->familyRelationService->acceptRelationshipRequest($request);
            $this->info("✅ Leila accepte → devient sœur d'Amina");
            
            // Vérifier suggestions pour Leila
            $this->checkSuggestionsFor($leila, [
                'Ahmed comme père',
                'Fatima comme mère',
                'Mohammed comme frère',
                'Youssef comme beau-frère'
            ]);
            
            // Suggestions réciproques pour Youssef
            $this->checkSuggestionsFor($users['Youssef'], ['Leila comme belle-sœur']);
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur dans phase Leila: " . $e->getMessage());
        }
    }

    private function checkSuggestionsFor(User $user, array $expectedSuggestions)
    {
        $suggestions = Suggestion::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['suggestedUser', 'relationshipType'])
            ->get();
        
        $this->info("\n🔍 Suggestions pour {$user->name}:");
        
        if ($suggestions->isEmpty()) {
            $this->info("  (Aucune suggestion)");
        } else {
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relationType = $suggestion->relationshipType;
                $this->info("  - {$suggestedUser->name} comme {$relationType->display_name_fr} ({$relationType->name})");
            }
        }
        
        // Vérifier si les suggestions attendues sont présentes
        foreach ($expectedSuggestions as $expected) {
            $found = false;
            foreach ($suggestions as $suggestion) {
                $suggestionText = $suggestion->suggestedUser->name . ' comme ' . $suggestion->relationshipType->display_name_fr;
                if (str_contains(strtolower($suggestionText), strtolower($expected))) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $this->info("  ✅ Trouvé: {$expected}");
            } else {
                $this->error("  ❌ Manquant: {$expected}");
            }
        }
    }

    private function verifyFinalSuggestions($users)
    {
        $this->info("\n📋 VÉRIFICATION FINALE - Résumé de toutes les suggestions");
        
        foreach ($users as $name => $user) {
            $suggestions = Suggestion::where('target_user_id', $user->id)
                ->where('status', 'pending')
                ->with(['suggestedUser', 'relationshipType'])
                ->get();
            
            $this->info("\n🌳 Suggestions pour {$user->name}:");
            
            if ($suggestions->isEmpty()) {
                $this->info("  (Aucune suggestion)");
            } else {
                foreach ($suggestions as $suggestion) {
                    $suggestedUser = $suggestion->suggestedUser;
                    $relationType = $suggestion->relationshipType;
                    $category = $this->categorizeSuggestion($relationType->name);
                    $this->info("  - {$suggestedUser->name} comme {$relationType->display_name_fr} [{$category}]");
                }
            }
        }
    }

    private function categorizeSuggestion(string $relationName): string
    {
        $directRelations = ['father', 'mother', 'son', 'daughter', 'brother', 'sister'];
        $marriageRelations = ['husband', 'wife'];
        $inLawRelations = ['father_in_law', 'mother_in_law', 'son_in_law', 'daughter_in_law'];
        
        if (in_array($relationName, $directRelations)) {
            return 'DIRECT';
        } elseif (in_array($relationName, $marriageRelations)) {
            return 'MARIAGE';
        } elseif (in_array($relationName, $inLawRelations)) {
            return 'BELLE-FAMILLE';
        } else {
            return 'ÉTENDU';
        }
    }
}
