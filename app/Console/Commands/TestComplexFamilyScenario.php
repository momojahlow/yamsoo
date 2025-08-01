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

class TestComplexFamilyScenario extends Command
{
    protected $signature = 'test:complex-family-scenario';
    protected $description = 'Test du scénario familial complexe avec 4 familles interconnectées';

    public function __construct(
        private SuggestionService $suggestionService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test du scénario familial complexe');
        
        // Nettoyer les données existantes
        $this->cleanupTestData();

        // Créer les utilisateurs selon le scénario
        $users = $this->createTestUsers();
        
        // Construire les relations familiales
        $this->buildFamilyRelationships($users);
        
        // Tester les suggestions et acceptations
        $this->testSuggestionsAndAcceptance($users);
        
        // Vérifier la cohérence des arbres familiaux
        $this->verifyFamilyTreeConsistency($users);
        
        $this->info("\n🎉 Test du scénario complexe terminé !");
    }

    private function createTestUsers()
    {
        $this->info("\n📋 Création des utilisateurs du scénario");
        
        $usersData = [
            // Famille Benali
            'ahmed.benali@example.com' => ['Ahmed Benali', 'male'],
            'fatima.zahra@example.com' => ['Fatima Zahra Benslimane', 'female'],
            'mohammed.benali@example.com' => ['Mohammed Alami Benali', 'male'],
            'amina.tazi@example.com' => ['Amina Benali', 'female'],
            
            // Famille Alami (Mohammed + Leila)
            'leila.mansouri@example.com' => ['Leila Mansouri', 'female'],
            'rachid.alaoui@example.com' => ['Rachid Alami Benali', 'male'],
            'hassan.idrissi@example.com' => ['Hassan Benali', 'male'],
            
            // Famille Bennani (Youssef + Amina)
            'youssef.bennani@example.com' => ['Youssef Bennani', 'male'],
            'karim.elfassi@example.com' => ['Karim Bennani', 'male'],
            'hanae.mernissi@example.com' => ['Hanae Bennani', 'female'],
            
            // Famille Benslimane
            'adil.benslimane@example.com' => ['Adil Benslimane', 'male'],
            'nadia.berrada@example.com' => ['Nadia Berrada', 'female'],
            'omar.cherkaoui@example.com' => ['Omar Benslimane', 'male'],
            'zineb.elkhayat@example.com' => ['Zineb Benslimane', 'female'],
        ];

        $users = [];
        foreach ($usersData as $email => [$name, $gender]) {
            $user = User::firstOrCreate(['email' => $email], [
                'name' => $name,
                'password' => bcrypt('password123')
            ]);
            
            // Créer ou mettre à jour le profil
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

    private function buildFamilyRelationships($users)
    {
        $this->info("\n📋 Construction des relations familiales de base");
        
        // Relations de base (parents-enfants et mariages)
        $relationships = [
            // Famille Benali
            ['ahmed.benali@example.com', 'mohammed.benali@example.com', 'father'],
            ['fatima.zahra@example.com', 'mohammed.benali@example.com', 'mother'],
            ['ahmed.benali@example.com', 'amina.tazi@example.com', 'father'],
            ['fatima.zahra@example.com', 'amina.tazi@example.com', 'mother'],
            ['ahmed.benali@example.com', 'fatima.zahra@example.com', 'husband'],
            
            // Famille Alami (Mohammed + Leila)
            ['mohammed.benali@example.com', 'rachid.alaoui@example.com', 'father'],
            ['leila.mansouri@example.com', 'rachid.alaoui@example.com', 'mother'],
            ['mohammed.benali@example.com', 'hassan.idrissi@example.com', 'father'],
            ['leila.mansouri@example.com', 'hassan.idrissi@example.com', 'mother'],
            ['mohammed.benali@example.com', 'leila.mansouri@example.com', 'husband'],
            
            // Famille Bennani (Youssef + Amina)
            ['youssef.bennani@example.com', 'karim.elfassi@example.com', 'father'],
            ['amina.tazi@example.com', 'karim.elfassi@example.com', 'mother'],
            ['youssef.bennani@example.com', 'hanae.mernissi@example.com', 'father'],
            ['amina.tazi@example.com', 'hanae.mernissi@example.com', 'mother'],
            ['youssef.bennani@example.com', 'amina.tazi@example.com', 'husband'],
            
            // Famille Benslimane
            ['adil.benslimane@example.com', 'omar.cherkaoui@example.com', 'father'],
            ['nadia.berrada@example.com', 'omar.cherkaoui@example.com', 'mother'],
            ['adil.benslimane@example.com', 'zineb.elkhayat@example.com', 'father'],
            ['nadia.berrada@example.com', 'zineb.elkhayat@example.com', 'mother'],
            ['adil.benslimane@example.com', 'nadia.berrada@example.com', 'husband'],
            
            // Mariage Hassan + Hanae
            ['hassan.idrissi@example.com', 'hanae.mernissi@example.com', 'husband'],
        ];

        foreach ($relationships as [$userEmail, $relatedEmail, $relationType]) {
            try {
                $user = $users[$userEmail];
                $relatedUser = $users[$relatedEmail];
                $relationshipType = RelationshipType::where('name', $relationType)->first();
                
                if (!$relationshipType) {
                    $this->error("❌ Type de relation '{$relationType}' introuvable");
                    continue;
                }

                $this->familyRelationService->createDirectRelationship(
                    $user, $relatedUser, $relationshipType, 'Relation de base du scénario'
                );
                
                $this->info("✅ {$user->name} → {$relatedUser->name} ({$relationType})");
            } catch (\Exception $e) {
                $this->warn("⚠️ Relation existe déjà: {$user->name} → {$relatedUser->name} ({$relationType})");
            }
        }
    }

    private function testSuggestionsAndAcceptance($users)
    {
        $this->info("\n📋 Test des suggestions et acceptations problématiques");
        
        // Test 1: Mohammed (homme) accepte suggestion "mère" de Fatima
        $this->info("\n🔍 Test 1: Problème de genre - Mohammed accepte 'mère'");
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $users['mohammed.benali@example.com'],
                $users['fatima.zahra@example.com']->id,
                'family_relation',
                'Test suggestion mère pour homme',
                'mother'
            );
            
            $this->suggestionService->acceptSuggestion($suggestion, 'mother');
            $this->info("✅ Suggestion acceptée");
            
            // Vérifier la demande créée
            $motherType = RelationshipType::where('name', 'mother')->first();
            $request = RelationshipRequest::where('requester_id', $users['mohammed.benali@example.com']->id)
                ->where('target_user_id', $users['fatima.zahra@example.com']->id)
                ->where('relationship_type_id', $motherType->id)
                ->first();
                
            if ($request) {
                $this->error("❌ PROBLÈME: Demande 'mère' créée pour un homme !");
            } else {
                $this->info("✅ Aucune demande incorrecte créée");
            }
            
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->info("✅ Suggestion bloquée: " . $e->getMessage());
        }

        // Test 2: Fatima accepte Leila comme "belle-fille"
        $this->info("\n🔍 Test 2: Problème bidirectionnel - belle-fille");
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $users['fatima.zahra@example.com'],
                $users['leila.mansouri@example.com']->id,
                'family_relation',
                'Test belle-fille',
                'daughter_in_law'
            );
            
            $this->suggestionService->acceptSuggestion($suggestion, 'daughter_in_law');
            $this->info("✅ Suggestion belle-fille acceptée");
            
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }

    private function verifyFamilyTreeConsistency($users)
    {
        $this->info("\n📋 Vérification de la cohérence des arbres familiaux");
        
        // Vérifier l'arbre de Fatima
        $this->info("\n🌳 Arbre familial de Fatima Zahra:");
        $fatimaRelations = $this->familyRelationService->getUserRelationships($users['fatima.zahra@example.com']);
        foreach ($fatimaRelations as $relation) {
            $this->info("  - {$relation->relatedUser->name}: {$relation->relationshipType->display_name_fr}");
        }
        
        // Vérifier l'arbre de Leila
        $this->info("\n🌳 Arbre familial de Leila Mansouri:");
        $leilaRelations = $this->familyRelationService->getUserRelationships($users['leila.mansouri@example.com']);
        foreach ($leilaRelations as $relation) {
            $this->info("  - {$relation->relatedUser->name}: {$relation->relationshipType->display_name_fr}");
        }
        
        // Vérifier la bidirectionnalité
        $this->info("\n🔍 Vérification bidirectionnalité Fatima ↔ Leila:");
        $fatimaToLeila = FamilyRelationship::where('user_id', $users['fatima.zahra@example.com']->id)
            ->where('related_user_id', $users['leila.mansouri@example.com']->id)
            ->with('relationshipType')
            ->first();
            
        $leilaToFatima = FamilyRelationship::where('user_id', $users['leila.mansouri@example.com']->id)
            ->where('related_user_id', $users['fatima.zahra@example.com']->id)
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

    private function cleanupTestData()
    {
        // Supprimer les relations de test
        $testEmails = [
            'ahmed.benali@example.com', 'fatima.zahra@example.com', 'mohammed.benali@example.com',
            'amina.tazi@example.com', 'leila.mansouri@example.com', 'rachid.alaoui@example.com',
            'hassan.idrissi@example.com', 'youssef.bennani@example.com', 'karim.elfassi@example.com',
            'hanae.mernissi@example.com', 'adil.benslimane@example.com', 'nadia.berrada@example.com',
            'omar.cherkaoui@example.com', 'zineb.elkhayat@example.com'
        ];

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
