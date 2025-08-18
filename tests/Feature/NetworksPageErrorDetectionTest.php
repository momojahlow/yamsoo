<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworksPageErrorDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;
    protected SuggestionService $suggestionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRelationService = app(FamilyRelationService::class);
        $this->suggestionService = app(SuggestionService::class);
        
        // Exécuter les seeders nécessaires
        $this->seed(\Database\Seeders\ComprehensiveRelationshipTypesSeeder::class);
    }

    /**
     * Test complet du scénario avec détection d'erreurs
     */
    public function test_complete_scenario_with_error_detection(): void
    {
        echo "\n=== TEST COMPLET AVEC DÉTECTION D'ERREURS ===\n";

        // Créer les utilisateurs
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);
        $leila = User::factory()->withProfile('female')->create(['name' => 'Leila Mansouri', 'email' => 'leila@test.com']);

        echo "✅ Utilisateurs créés avec succès\n";

        // Vérifier les types de relations nécessaires
        $this->verifyRelationshipTypes();

        // Test du scénario complet
        $this->executeScenarioSteps($ahmed, $fatima, $youssef, $leila);

        // Vérifications finales et détection d'erreurs
        $this->detectAndReportErrors($ahmed, $fatima, $youssef, $leila);

        echo "\n🎉 TEST COMPLET TERMINÉ\n";
    }

    /**
     * Vérifier que tous les types de relations nécessaires existent
     */
    private function verifyRelationshipTypes(): void
    {
        echo "\n=== VÉRIFICATION DES TYPES DE RELATIONS ===\n";

        $requiredTypes = ['husband', 'wife', 'brother', 'sister', 'brother_in_law', 'sister_in_law'];
        $missingTypes = [];

        foreach ($requiredTypes as $type) {
            $relationshipType = RelationshipType::where('name', $type)->first();
            if (!$relationshipType) {
                $missingTypes[] = $type;
                echo "❌ Type manquant: {$type}\n";
            } else {
                echo "✅ Type trouvé: {$type} (ID: {$relationshipType->id})\n";
            }
        }

        if (!empty($missingTypes)) {
            $this->fail('Types de relations manquants: ' . implode(', ', $missingTypes));
        }

        echo "✅ Tous les types de relations nécessaires sont présents\n";
    }

    /**
     * Exécuter les étapes du scénario
     */
    private function executeScenarioSteps(User $ahmed, User $fatima, User $youssef, User $leila): void
    {
        echo "\n=== EXÉCUTION DU SCÉNARIO ===\n";

        // 1. Ahmed → Fatima (Mari)
        echo "1. Ahmed demande à Fatima d'être son mari...\n";
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $fatima->id, $husbandType->id, 'Demande de mariage'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        echo "✅ Relation Ahmed-Fatima créée\n";

        // 2. Fatima → Youssef (Frère)
        echo "2. Fatima demande à Youssef d'être son frère...\n";
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $youssef->id, $brotherType->id, 'Tu es mon frère'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        echo "✅ Relation Fatima-Youssef créée\n";

        // 3. Youssef → Ahmed (Beau-frère)
        echo "3. Youssef demande à Ahmed d'être son beau-frère...\n";
        $brotherInLawType = RelationshipType::where('name', 'brother_in_law')->first();
        if ($brotherInLawType) {
            $request3 = $this->familyRelationService->createRelationshipRequest(
                $youssef, $ahmed->id, $brotherInLawType->id, 'Tu es mon beau-frère'
            );
            $this->familyRelationService->acceptRelationshipRequest($request3);
            echo "✅ Relation Youssef-Ahmed créée\n";
        } else {
            echo "⚠️ Type brother_in_law non trouvé\n";
        }

        // 4. Ahmed → Leila (Sœur)
        echo "4. Ahmed demande à Leila d'être sa sœur...\n";
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $request4 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $leila->id, $sisterType->id, 'Tu es ma sœur'
        );
        $this->familyRelationService->acceptRelationshipRequest($request4);
        echo "✅ Relation Ahmed-Leila créée\n";

        // 5. Leila → Youssef (Beau-frère)
        echo "5. Leila demande à Youssef d'être son beau-frère...\n";
        if ($brotherInLawType) {
            $request5 = $this->familyRelationService->createRelationshipRequest(
                $leila, $youssef->id, $brotherInLawType->id, 'Tu es mon beau-frère'
            );
            $this->familyRelationService->acceptRelationshipRequest($request5);
            echo "✅ Relation Leila-Youssef créée\n";
        }

        // 6. Leila → Fatima (Belle-sœur)
        echo "6. Leila demande à Fatima d'être sa belle-sœur...\n";
        $sisterInLawType = RelationshipType::where('name', 'sister_in_law')->first();
        if ($sisterInLawType) {
            $request6 = $this->familyRelationService->createRelationshipRequest(
                $leila, $fatima->id, $sisterInLawType->id, 'Tu es ma belle-sœur'
            );
            $this->familyRelationService->acceptRelationshipRequest($request6);
            echo "✅ Relation Leila-Fatima créée\n";
        } else {
            echo "⚠️ Type sister_in_law non trouvé\n";
        }
    }

    /**
     * Détecter et rapporter les erreurs
     */
    private function detectAndReportErrors(User $ahmed, User $fatima, User $youssef, User $leila): void
    {
        echo "\n=== DÉTECTION D'ERREURS ===\n";

        $errors = [];
        $warnings = [];

        // 1. Vérifier les relations bidirectionnelles
        $bidirectionalErrors = $this->checkBidirectionalRelations();
        if (!empty($bidirectionalErrors)) {
            $errors = array_merge($errors, $bidirectionalErrors);
        }

        // 2. Vérifier les relations automatiques (ne devraient pas exister)
        $automaticRelations = FamilyRelationship::where('created_automatically', true)->count();
        if ($automaticRelations > 0) {
            $errors[] = "❌ {$automaticRelations} relations automatiques trouvées (ne devrait pas y en avoir)";
        } else {
            echo "✅ Aucune relation automatique créée (correct)\n";
        }

        // 3. Vérifier la structure des données pour Networks.tsx
        $networkDataErrors = $this->checkNetworkDataStructure($ahmed, $fatima, $youssef, $leila);
        if (!empty($networkDataErrors)) {
            $errors = array_merge($errors, $networkDataErrors);
        }

        // 4. Vérifier les demandes en attente
        $pendingRequestErrors = $this->checkPendingRequestsStructure();
        if (!empty($pendingRequestErrors)) {
            $errors = array_merge($errors, $pendingRequestErrors);
        }

        // 5. Vérifier les suggestions
        $suggestionErrors = $this->checkSuggestionsStructure($ahmed);
        if (!empty($suggestionErrors)) {
            $warnings = array_merge($warnings, $suggestionErrors);
        }

        // Rapport final
        $this->generateErrorReport($errors, $warnings);
    }

    /**
     * Vérifier les relations bidirectionnelles
     */
    private function checkBidirectionalRelations(): array
    {
        $errors = [];
        $relations = FamilyRelationship::where('status', 'accepted')->get();
        $missingReciprocalCount = 0;

        foreach ($relations as $relation) {
            $reciprocal = FamilyRelationship::where('user_id', $relation->related_user_id)
                ->where('related_user_id', $relation->user_id)
                ->where('status', 'accepted')
                ->exists();

            if (!$reciprocal) {
                $missingReciprocalCount++;
                $errors[] = "❌ Relation réciproque manquante: {$relation->user->name} → {$relation->relatedUser->name}";
            }
        }

        if ($missingReciprocalCount === 0) {
            echo "✅ Toutes les relations ont leur réciproque\n";
        }

        return $errors;
    }

    /**
     * Vérifier la structure des données pour Networks.tsx
     */
    private function checkNetworkDataStructure(User $ahmed, User $fatima, User $youssef, User $leila): array
    {
        $errors = [];

        // Simuler les données que Networks.tsx recevrait
        $users = [$ahmed, $fatima, $youssef, $leila];

        foreach ($users as $user) {
            // Vérifier les demandes reçues
            $pendingRequests = RelationshipRequest::where('target_user_id', $user->id)
                ->where('status', 'pending')
                ->with(['requester.profile', 'relationshipType'])
                ->get();

            foreach ($pendingRequests as $request) {
                if (!$request->requester) {
                    $errors[] = "❌ Demande {$request->id}: requester manquant";
                }
                if (!$request->relationshipType) {
                    $errors[] = "❌ Demande {$request->id}: relationshipType manquant";
                }
                if ($request->requester && !$request->requester->name) {
                    $errors[] = "❌ Demande {$request->id}: nom du requester manquant";
                }
            }

            // Vérifier les demandes envoyées
            $sentRequests = RelationshipRequest::where('requester_id', $user->id)
                ->where('status', 'pending')
                ->with(['targetUser.profile', 'relationshipType'])
                ->get();

            foreach ($sentRequests as $request) {
                if (!$request->targetUser) {
                    $errors[] = "❌ Demande envoyée {$request->id}: targetUser manquant";
                }
                if (!$request->relationshipType) {
                    $errors[] = "❌ Demande envoyée {$request->id}: relationshipType manquant";
                }
                if ($request->targetUser && !$request->targetUser->name) {
                    $errors[] = "❌ Demande envoyée {$request->id}: nom du targetUser manquant";
                }
            }
        }

        if (empty($errors)) {
            echo "✅ Structure des données Networks.tsx correcte\n";
        }

        return $errors;
    }

    /**
     * Vérifier la structure des demandes en attente
     */
    private function checkPendingRequestsStructure(): array
    {
        $errors = [];
        $pendingRequests = RelationshipRequest::where('status', 'pending')->get();

        foreach ($pendingRequests as $request) {
            if (!$request->requester_id) {
                $errors[] = "❌ Demande {$request->id}: requester_id manquant";
            }
            if (!$request->target_user_id) {
                $errors[] = "❌ Demande {$request->id}: target_user_id manquant";
            }
            if (!$request->relationship_type_id) {
                $errors[] = "❌ Demande {$request->id}: relationship_type_id manquant";
            }
        }

        if (empty($errors)) {
            echo "✅ Structure des demandes en attente correcte\n";
        }

        return $errors;
    }

    /**
     * Vérifier la structure des suggestions
     */
    private function checkSuggestionsStructure(User $user): array
    {
        $warnings = [];

        try {
            $suggestions = $this->suggestionService->getUserSuggestions($user);
            echo "✅ Service de suggestions fonctionne\n";
        } catch (\Exception $e) {
            $warnings[] = "⚠️ Erreur dans le service de suggestions: " . $e->getMessage();
        }

        return $warnings;
    }

    /**
     * Générer le rapport d'erreurs final
     */
    private function generateErrorReport(array $errors, array $warnings): void
    {
        echo "\n=== RAPPORT FINAL ===\n";

        if (empty($errors) && empty($warnings)) {
            echo "🎉 AUCUNE ERREUR DÉTECTÉE ! Le système fonctionne parfaitement.\n";
            return;
        }

        if (!empty($errors)) {
            echo "❌ ERREURS CRITIQUES DÉTECTÉES:\n";
            foreach ($errors as $error) {
                echo "  {$error}\n";
            }
        }

        if (!empty($warnings)) {
            echo "\n⚠️ AVERTISSEMENTS:\n";
            foreach ($warnings as $warning) {
                echo "  {$warning}\n";
            }
        }

        echo "\nTotal: " . count($errors) . " erreurs, " . count($warnings) . " avertissements\n";

        if (!empty($errors)) {
            $this->fail("Test échoué avec " . count($errors) . " erreurs critiques");
        }
    }
}
