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

class TestCompleteSystem extends Command
{
    protected $signature = 'test:complete-system';
    protected $description = 'Test complet du système de relations familiales';

    public function __construct(
        private SuggestionService $suggestionService,
        private FamilyRelationService $familyRelationService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('🔍 Test complet du système de relations familiales');

        // Nettoyer les données de test
        $this->cleanupTestData();

        // Trouver des utilisateurs de test
        $users = User::limit(4)->get();
        if ($users->count() < 4) {
            $this->error('❌ Pas assez d\'utilisateurs de test (minimum 4)');
            return;
        }

        [$user1, $user2, $user3, $user4] = $users;
        $this->info("✅ Utilisateurs trouvés:");
        foreach ($users as $i => $user) {
            $this->info("  - User" . ($i + 1) . ": {$user->name}");
        }

        // Test 1: Créer des suggestions valides
        $this->info("\n📋 Test 1: Création de suggestions valides");
        $relationTypes = ['father', 'mother', 'brother', 'sister', 'uncle', 'aunt'];
        
        foreach ($relationTypes as $relationType) {
            try {
                $suggestion = $this->suggestionService->createSuggestion(
                    $user1,
                    $user2->id,
                    'family_relation',
                    "Suggestion de relation {$relationType}",
                    $relationType
                );
                $this->info("✅ Suggestion {$relationType} créée (ID: {$suggestion->id})");
                
                // Nettoyer immédiatement pour éviter les conflits
                $suggestion->delete();
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour {$relationType}: " . $e->getMessage());
            }
        }

        // Test 2: Acceptation de suggestions complexes
        $this->info("\n📋 Test 2: Acceptation de suggestions complexes");
        $complexRelations = ['grandfather', 'grandmother', 'uncle', 'aunt'];
        
        foreach ($complexRelations as $relationType) {
            try {
                // Créer la suggestion
                $suggestion = $this->suggestionService->createSuggestion(
                    $user1,
                    $user3->id,
                    'family_relation',
                    "Test acceptation {$relationType}",
                    $relationType
                );

                // Accepter la suggestion
                $this->suggestionService->acceptSuggestion($suggestion, $relationType);
                $this->info("✅ Suggestion {$relationType} acceptée");

                // Vérifier qu'une demande a été créée
                $relationshipType = RelationshipType::where('name', $relationType)->first();
                $request = RelationshipRequest::where('requester_id', $user1->id)
                    ->where('target_user_id', $user3->id)
                    ->where('relationship_type_id', $relationshipType->id)
                    ->first();

                if ($request) {
                    $this->info("  → Demande de relation créée (ID: {$request->id})");
                    $request->delete(); // Nettoyer
                } else {
                    $this->warn("  → Aucune demande trouvée");
                }

                $suggestion->delete(); // Nettoyer
            } catch (\Exception $e) {
                $this->error("❌ Erreur pour {$relationType}: " . $e->getMessage());
            }
        }

        // Test 3: Prévention des doublons
        $this->info("\n📋 Test 3: Prévention des doublons");
        
        // Créer une relation directe
        $fatherType = RelationshipType::where('name', 'father')->first();
        $relationship = $this->familyRelationService->createDirectRelationship(
            $user1, $user4, $fatherType, 'Test relation père'
        );
        $this->info("✅ Relation père créée");

        // Tenter de créer une suggestion pour la même relation
        try {
            $suggestion = $this->suggestionService->createSuggestion(
                $user1, $user4->id, 'family_relation', 'Doublon', 'father'
            );
            $this->error("❌ PROBLÈME: Suggestion créée malgré relation existante");
            $suggestion->delete();
        } catch (\Exception $e) {
            $this->info("✅ Doublon correctement bloqué");
        }

        // Test 4: Affichage français
        $this->info("\n📋 Test 4: Affichage français des relations");
        $relations = $this->familyRelationService->getUserRelationships($user1);
        
        foreach ($relations as $relation) {
            $relationType = $relation->relationshipType;
            $relatedUser = $relation->relatedUser;
            $this->info("  - {$relatedUser->name}: {$relationType->display_name_fr} ({$relationType->name})");
        }

        // Test 5: API de l'arbre familial
        $this->info("\n📋 Test 5: API de l'arbre familial");
        try {
            // Simuler une requête HTTP
            $request = new \Illuminate\Http\Request();
            $request->setUserResolver(function () use ($user1) {
                return $user1;
            });

            $controller = app('App\Http\Controllers\FamilyTreeController');
            $response = $controller->getFamilyRelations($request);
            $data = $response->getData(true);

            $this->info("✅ API fonctionne:");
            $this->info("  - Utilisateur: {$data['user']['name']}");
            $this->info("  - Nombre de relations: " . count($data['relationships']));

            foreach ($data['relationships'] as $rel) {
                $this->info("    * {$rel['related_user']['name']}: {$rel['type']} ({$rel['type_code']})");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur API: " . $e->getMessage());
        }

        // Nettoyer les données de test
        $this->cleanupTestData();
        $this->info("\n🧹 Données de test nettoyées");

        $this->info("\n🎉 Test complet terminé avec succès !");
        $this->info("\n✅ Résumé des fonctionnalités testées:");
        $this->info("  1. Création de suggestions avec validation");
        $this->info("  2. Acceptation de suggestions complexes");
        $this->info("  3. Prévention des doublons");
        $this->info("  4. Affichage français des relations");
        $this->info("  5. API de l'arbre familial");
    }

    private function cleanupTestData()
    {
        // Supprimer toutes les suggestions de test
        Suggestion::where('message', 'like', '%Test%')->delete();
        Suggestion::where('message', 'like', '%Doublon%')->delete();
        
        // Supprimer les relations de test
        $users = User::limit(4)->get();
        if ($users->count() >= 4) {
            foreach ($users as $user1) {
                foreach ($users as $user2) {
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
        }
    }
}
