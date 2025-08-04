<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestYoussefLeilaScenario extends Command
{
    protected $signature = 'test:youssef-leila-scenario';
    protected $description = 'Test du scénario Youssef-Leila avec suggestions bidirectionnelles';

    public function __construct(
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🎯 Test du scénario Youssef-Leila avec suggestions bidirectionnelles');
        
        // Reset database
        $this->info('🔄 Reset de la base de données...');
        $this->call('migrate:fresh', ['--seed' => true]);
        
        // Obtenir les utilisateurs
        $users = $this->getUsers();
        
        // Phase 1: Créer la famille de base (Ahmed + Fatima + enfants)
        $this->createBaseFamilyStructure($users);
        
        // Phase 2: Youssef épouse Leila et a un fils Karim
        $this->createYoussefLeilaFamily($users);
        
        // Phase 3: Traiter les jobs de suggestions
        $this->processQueueJobs();
        
        // Phase 4: Analyser les suggestions générées
        $this->analyzeSuggestions($users);
        
        $this->info("\n🎉 Test terminé !");
    }

    private function getUsers()
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

    private function createBaseFamilyStructure($users)
    {
        $this->info("\n📋 PHASE 1: Création de la famille de base Ahmed-Fatima");
        
        try {
            // Ahmed épouse Fatima
            $wifeType = RelationshipType::where('name', 'wife')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest(
                $users['Ahmed'], $users['Fatima']->id, $wifeType->id, 'Ahmed épouse Fatima'
            );
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Ahmed ↔ Fatima (époux)");

            // Ahmed père de Youssef
            $fatherType = RelationshipType::where('name', 'father')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest(
                $users['Ahmed'], $users['Youssef']->id, $fatherType->id, 'Ahmed père de Youssef'
            );
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Ahmed → Youssef (père)");

            // Ahmed père de Mohammed
            $request3 = $this->familyRelationService->createRelationshipRequest(
                $users['Ahmed'], $users['Mohammed']->id, $fatherType->id, 'Ahmed père de Mohammed'
            );
            $this->familyRelationService->acceptRelationshipRequest($request3);
            $this->info("✅ Ahmed → Mohammed (père)");

            // Ahmed père d'Amina
            $request4 = $this->familyRelationService->createRelationshipRequest(
                $users['Ahmed'], $users['Amina']->id, $fatherType->id, 'Ahmed père d\'Amina'
            );
            $this->familyRelationService->acceptRelationshipRequest($request4);
            $this->info("✅ Ahmed → Amina (père)");

        } catch (\Exception $e) {
            $this->error("❌ Erreur phase 1: " . $e->getMessage());
        }
    }

    private function createYoussefLeilaFamily($users)
    {
        $this->info("\n📋 PHASE 2: Youssef épouse Leila et a un fils Karim");
        
        try {
            // Youssef épouse Leila
            $wifeType = RelationshipType::where('name', 'wife')->first();
            $request1 = $this->familyRelationService->createRelationshipRequest(
                $users['Youssef'], $users['Leila']->id, $wifeType->id, 'Youssef épouse Leila'
            );
            $this->familyRelationService->acceptRelationshipRequest($request1);
            $this->info("✅ Youssef ↔ Leila (époux)");

            // Youssef père de Karim
            $fatherType = RelationshipType::where('name', 'father')->first();
            $request2 = $this->familyRelationService->createRelationshipRequest(
                $users['Youssef'], $users['Karim']->id, $fatherType->id, 'Youssef père de Karim'
            );
            $this->familyRelationService->acceptRelationshipRequest($request2);
            $this->info("✅ Youssef → Karim (père)");

        } catch (\Exception $e) {
            $this->error("❌ Erreur phase 2: " . $e->getMessage());
        }
    }

    private function processQueueJobs()
    {
        $this->info("\n📋 PHASE 3: Traitement des jobs de suggestions...");
        
        // Traiter tous les jobs en queue
        $this->call('queue:work', ['--stop-when-empty' => true]);
    }

    private function analyzeSuggestions($users)
    {
        $this->info("\n📋 PHASE 4: Analyse des suggestions générées");
        
        $this->info("\n🔍 PROBLÈME IDENTIFIÉ - Suggestions asymétriques:");
        
        // Vérifier les suggestions pour Leila (devrait voir les beaux-parents)
        $this->checkSuggestionsFor($users['Leila'], [
            'Ahmed comme beau-père',
            'Fatima comme belle-mère',
            'Mohammed comme beau-frère', 
            'Amina comme belle-sœur'
        ], "Leila (épouse de Youssef)");

        // Vérifier les suggestions pour Ahmed (devrait voir Leila comme belle-fille)
        $this->checkSuggestionsFor($users['Ahmed'], [
            'Leila comme belle-fille'
        ], "Ahmed (père de Youssef)");

        // Vérifier les suggestions pour Fatima (devrait voir Leila comme belle-fille)
        $this->checkSuggestionsFor($users['Fatima'], [
            'Leila comme belle-fille'
        ], "Fatima (mère de Youssef)");

        // Vérifier les suggestions pour Mohammed (devrait voir Leila comme belle-sœur)
        $this->checkSuggestionsFor($users['Mohammed'], [
            'Leila comme belle-sœur'
        ], "Mohammed (frère de Youssef)");

        // Vérifier les suggestions pour Amina (devrait voir Leila comme belle-sœur)
        $this->checkSuggestionsFor($users['Amina'], [
            'Leila comme belle-sœur'
        ], "Amina (sœur de Youssef)");
    }

    private function checkSuggestionsFor(User $user, array $expectedSuggestions, string $context)
    {
        $suggestions = Suggestion::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['suggestedUser'])
            ->get();
        
        $this->info("\n🔍 Suggestions pour {$user->name} ({$context}):");
        
        if ($suggestions->isEmpty()) {
            $this->info("  (Aucune suggestion)");
        } else {
            foreach ($suggestions as $suggestion) {
                $suggestedUser = $suggestion->suggestedUser;
                $relationName = $suggestion->suggested_relation_name ?? $suggestion->suggested_relation_code;
                $this->info("  - {$suggestedUser->name} comme {$relationName}");
            }
        }
        
        // Vérifier si les suggestions attendues sont présentes
        foreach ($expectedSuggestions as $expected) {
            $found = false;
            foreach ($suggestions as $suggestion) {
                $suggestionText = $suggestion->suggestedUser->name . ' comme ' . ($suggestion->suggested_relation_name ?? $suggestion->suggested_relation_code);
                if (str_contains(strtolower($suggestionText), strtolower($expected))) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                $this->info("  ✅ Trouvé: {$expected}");
            } else {
                $this->error("  ❌ MANQUANT: {$expected}");
            }
        }
    }
}
