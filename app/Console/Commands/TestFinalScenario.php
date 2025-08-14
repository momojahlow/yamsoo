<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use App\Services\FamilyRelationService;
use Illuminate\Console\Command;

class TestFinalScenario extends Command
{
    protected $signature = 'test:final-scenario';
    protected $description = 'Test final du scénario avec données fraîches';

    public function __construct(
        private SuggestionService $suggestionService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🎯 Test final du scénario avec données fraîches');
        
        // Nettoyer complètement
        $this->cleanupAllData();

        // Créer 4 utilisateurs simples
        $users = $this->createSimpleUsers();
        
        // Test 1: Relations parent-enfant avec genres corrects
        $this->testParentChildRelations($users);
        
        // Test 2: Suggestions et acceptations
        $this->testSuggestionsWorkflow($users);
        
        // Test 3: Relations par alliance
        $this->testInLawRelations($users);
        
        // Test 4: Vérification finale des arbres
        $this->verifyFinalTrees($users);
        
        $this->info("\n🎉 Test final terminé avec succès !");
    }

    private function createSimpleUsers()
    {
        $this->info("\n📋 Création d'utilisateurs de test simples");
        
        $usersData = [
            'ahmed@test.com' => ['Ahmed Benali', 'male'],
            'fatima@test.com' => ['Fatima Zahra', 'female'],
            'mohammed@test.com' => ['Mohammed Alami', 'male'],
            'leila@test.com' => ['Leila Mansouri', 'female'],
        ];

        $users = [];
        foreach ($usersData as $email => [$name, $gender]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => bcrypt('password123')
            ]);
            
            if (!$user->profile) {
                $user->profile()->create([
                    'first_name' => explode(' ', $name)[0],
                    'last_name' => substr($name, strlen(explode(' ', $name)[0]) + 1),
                    'gender' => $gender,
                    'birth_date' => now()->subYears(rand(25, 60)),
                ]);
            } else {
                $user->profile->update(['gender' => $gender]);
            }
            
            $users[$email] = $user;
            $this->info("✅ {$name} ({$gender})");
        }

        return $users;
    }

    private function testParentChildRelations($users)
    {
        $this->info("\n📋 Test 1: Relations parent-enfant avec genres corrects");
        
        $ahmed = $users['ahmed@test.com'];
        $fatima = $users['fatima@test.com'];
        $mohammed = $users['mohammed@test.com'];
        
        // Ahmed (homme) → Mohammed (fils)
        $fatherType = RelationshipType::where('name', 'father')->first();
        $relationship1 = $this->familyRelationService->createDirectRelationship(
            $ahmed, $mohammed, $fatherType, 'Test père-fils'
        );
        $this->info("✅ Ahmed → Mohammed: Père");
        
        // Fatima (femme) → Mohammed (fils)
        $motherType = RelationshipType::where('name', 'mother')->first();
        $relationship2 = $this->familyRelationService->createDirectRelationship(
            $fatima, $mohammed, $motherType, 'Test mère-fils'
        );
        $this->info("✅ Fatima → Mohammed: Mère");
        
        // Vérifier les relations inverses
        $this->info("\n🔍 Vérification des relations inverses:");
        $ahmedRelations = $this->familyRelationService->getUserRelationships($ahmed);
        foreach ($ahmedRelations as $rel) {
            $this->info("  Ahmed → {$rel->relatedUser->name}: {$rel->relationshipType->display_name_fr}");
        }
        
        $mohammedRelations = $this->familyRelationService->getUserRelationships($mohammed);
        foreach ($mohammedRelations as $rel) {
            $this->info("  Mohammed → {$rel->relatedUser->name}: {$rel->relationshipType->display_name_fr}");
        }
    }

    private function testSuggestionsWorkflow($users)
    {
        $this->info("\n📋 Test 2: Workflow des suggestions");
        
        $fatima = $users['fatima@test.com'];
        $leila = $users['leila@test.com'];
        
        // Test suggestion belle-fille
        $this->info("\n🔍 Test suggestion belle-fille:");
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $fatima,
                $leila->id,
                'family_relation',
                'Test suggestion belle-fille',
                'daughter_in_law'
            );
            $this->info("✅ Suggestion créée");
            
            // Accepter la suggestion
            $this->suggestionService->acceptSuggestion($suggestion, 'daughter_in_law');
            $this->info("✅ Suggestion acceptée");
            
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function testInLawRelations($users)
    {
        $this->info("\n📋 Test 3: Relations par alliance");
        
        $mohammed = $users['mohammed@test.com'];
        $leila = $users['leila@test.com'];
        
        // Mohammed épouse Leila
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $relationship = $this->familyRelationService->createDirectRelationship(
            $mohammed, $leila, $husbandType, 'Test mariage'
        );
        $this->info("✅ Mohammed → Leila: Mari");
    }

    private function verifyFinalTrees($users)
    {
        $this->info("\n📋 Test 4: Vérification finale des arbres familiaux");
        
        foreach ($users as $email => $user) {
            $this->info("\n🌳 Arbre de {$user->name}:");
            $relations = $this->familyRelationService->getUserRelationships($user);
            
            if ($relations->isEmpty()) {
                $this->info("  (Aucune relation)");
            } else {
                foreach ($relations as $relation) {
                    $relatedUser = $relation->relatedUser;
                    $relationType = $relation->relationshipType;
                    $this->info("  - {$relatedUser->name}: {$relationType->display_name_fr} ({$relationType->name})");
                }
            }
        }
        
        // Test spécifique bidirectionnalité
        $this->info("\n🔍 Test bidirectionnalité Fatima ↔ Leila:");
        $fatima = $users['fatima@test.com'];
        $leila = $users['leila@test.com'];
        
        $fatimaToLeila = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $leila->id)
            ->with('relationshipType')
            ->first();
            
        $leilaToFatima = FamilyRelationship::where('user_id', $leila->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();
            
        if ($fatimaToLeila) {
            $this->info("✅ Fatima → Leila: {$fatimaToLeila->relationshipType->display_name_fr}");
        } else {
            $this->error("❌ Aucune relation Fatima → Leila");
        }
        
        if ($leilaToFatima) {
            $this->info("✅ Leila → Fatima: {$leilaToFatima->relationshipType->display_name_fr}");
        } else {
            $this->error("❌ Aucune relation Leila → Fatima");
        }
    }

    private function cleanupAllData()
    {
        $this->info("🧹 Nettoyage complet des données de test");
        
        // Supprimer toutes les relations de test
        $testEmails = ['ahmed@test.com', 'fatima@test.com', 'mohammed@test.com', 'leila@test.com'];
        $testUsers = User::whereIn('email', $testEmails)->get();
        
        foreach ($testUsers as $user1) {
            foreach ($testUsers as $user2) {
                if ($user1->id !== $user2->id) {
                    FamilyRelationship::where('user_id', $user1->id)
                        ->where('related_user_id', $user2->id)
                        ->delete();
                    
                    RelationshipRequest::where('requester_id', $user1->id)
                        ->where('target_user_id', $user2->id)
                        ->delete();
                }
            }
        }
        
        Suggestion::where('message', 'like', '%Test%')->delete();
    }
}
