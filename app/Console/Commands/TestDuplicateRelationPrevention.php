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

class TestDuplicateRelationPrevention extends Command
{
    protected $signature = 'test:duplicate-prevention';
    protected $description = 'Test la prévention des doublons de relations et demandes';

    public function __construct(
        private SuggestionService $suggestionService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test de prévention des doublons de relations');

        // Trouver des utilisateurs de test
        $user1 = User::first();
        $user2 = User::skip(1)->first();
        $user3 = User::skip(2)->first();

        if (!$user1 || !$user2 || !$user3) {
            $this->error('❌ Pas assez d\'utilisateurs de test');
            return;
        }

        $this->info("✅ Utilisateurs trouvés:");
        $this->info("  - User1: {$user1->name}");
        $this->info("  - User2: {$user2->name}");
        $this->info("  - User3: {$user3->name}");

        // Nettoyer les données existantes pour ce test
        $this->cleanupTestData($user1, $user2, $user3);

        // Test 1: Créer une relation directe
        $this->info("\n📋 Test 1: Création d'une relation directe");
        $fatherType = RelationshipType::where('name', 'father')->first();
        
        try {
            $relationship = $this->familyRelationService->createDirectRelationship(
                $user1,
                $user2,
                $fatherType,
                'Test relation père-fils'
            );
            $this->info("✅ Relation créée: {$user1->name} → {$user2->name} (père)");
        } catch (\Exception $e) {
            $this->error("❌ Erreur création relation: " . $e->getMessage());
            return;
        }

        // Test 2: Tenter de créer la même relation (doit échouer)
        $this->info("\n📋 Test 2: Tentative de doublon de relation directe");
        try {
            $duplicate = $this->familyRelationService->createDirectRelationship(
                $user1,
                $user2,
                $fatherType,
                'Tentative de doublon'
            );
            $this->error("❌ PROBLÈME: Doublon de relation créé !");
        } catch (\Exception $e) {
            $this->info("✅ Doublon correctement bloqué: " . $e->getMessage());
        }

        // Test 3: Tenter de créer une suggestion pour une relation existante
        $this->info("\n📋 Test 3: Suggestion pour relation existante");
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $user1,
                $user2->id,
                'family_relation',
                "Suggestion pour relation existante",
                'father'
            );
            $this->error("❌ PROBLÈME: Suggestion créée malgré relation existante !");
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->info("✅ Suggestion bloquée: " . $e->getMessage());
        }

        // Test 4: Créer une demande de relation
        $this->info("\n📋 Test 4: Création d'une demande de relation");
        $brotherType = RelationshipType::where('name', 'brother')->first();
        
        try {
            $request = $this->familyRelationService->createRelationshipRequest(
                $user1,
                $user3->id,
                $brotherType->id,
                'Demande de relation frère'
            );
            $this->info("✅ Demande créée: {$user1->name} → {$user3->name} (frère)");
        } catch (\Exception $e) {
            $this->error("❌ Erreur création demande: " . $e->getMessage());
        }

        // Test 5: Tenter de créer une demande en doublon
        $this->info("\n📋 Test 5: Tentative de doublon de demande");
        try {
            $duplicateRequest = $this->familyRelationService->createRelationshipRequest(
                $user1,
                $user3->id,
                $brotherType->id,
                'Tentative de doublon de demande'
            );
            $this->error("❌ PROBLÈME: Doublon de demande créé !");
        } catch (\Exception $e) {
            $this->info("✅ Doublon de demande correctement bloqué: " . $e->getMessage());
        }

        // Test 6: Tenter de créer une suggestion pour une demande existante
        $this->info("\n📋 Test 6: Suggestion pour demande existante");
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $user1,
                $user3->id,
                'family_relation',
                "Suggestion pour demande existante",
                'brother'
            );
            $this->error("❌ PROBLÈME: Suggestion créée malgré demande existante !");
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->info("✅ Suggestion bloquée: " . $e->getMessage());
        }

        // Résumé des vérifications nécessaires
        $this->info("\n🎯 Résumé des vérifications nécessaires:");
        $this->info("1. ✅ Bloquer les doublons de relations directes");
        $this->info("2. ✅ Bloquer les suggestions si relation existe déjà");
        $this->info("3. ✅ Bloquer les doublons de demandes de relation");
        $this->info("4. ✅ Bloquer les suggestions si demande existe déjà");

        // Nettoyer après le test
        $this->cleanupTestData($user1, $user2, $user3);
        $this->info("\n🧹 Données de test nettoyées");
        $this->info("🎉 Test terminé !");
    }

    private function cleanupTestData(User $user1, User $user2, User $user3)
    {
        // Supprimer les relations de test
        FamilyRelationship::where('user_id', $user1->id)
            ->whereIn('related_user_id', [$user2->id, $user3->id])
            ->delete();
        
        FamilyRelationship::where('user_id', $user2->id)
            ->where('related_user_id', $user1->id)
            ->delete();

        FamilyRelationship::where('user_id', $user3->id)
            ->where('related_user_id', $user1->id)
            ->delete();

        // Supprimer les demandes de test
        RelationshipRequest::where('requester_id', $user1->id)
            ->whereIn('target_user_id', [$user2->id, $user3->id])
            ->delete();

        RelationshipRequest::where('requester_id', $user2->id)
            ->where('target_user_id', $user1->id)
            ->delete();

        RelationshipRequest::where('requester_id', $user3->id)
            ->where('target_user_id', $user1->id)
            ->delete();

        // Supprimer les suggestions de test
        Suggestion::where('user_id', $user1->id)
            ->whereIn('suggested_user_id', [$user2->id, $user3->id])
            ->delete();
    }
}
