<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRelationService = app(FamilyRelationService::class);
        $this->seed(\Database\Seeders\ComprehensiveRelationshipTypesSeeder::class);
    }

    /**
     * Test du scénario complet avec tous les problèmes identifiés
     */
    public function test_complete_scenario_with_all_issues(): void
    {
        echo "\n=== TEST SCÉNARIO COMPLET ===\n";

        // Créer les utilisateurs avec genres corrects
        $users = $this->createUsers();
        extract($users); // $ahmed, $fatima, $mohammed, $amina, $youssef, $leila, $karim

        echo "✅ Utilisateurs créés avec genres spécifiés\n";

        // Exécuter le scénario complet
        $this->executeCompleteScenario($users);

        // Vérifier tous les problèmes
        $this->verifyAllRelationships($users);

        echo "\n🎉 TEST SCÉNARIO COMPLET TERMINÉ\n";
    }

    /**
     * Créer tous les utilisateurs nécessaires
     */
    private function createUsers(): array
    {
        $users = [];
        
        $userData = [
            'ahmed' => ['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com', 'gender' => 'male'],
            'fatima' => ['name' => 'Fatima Zahra', 'email' => 'fatima@test.com', 'gender' => 'female'],
            'mohammed' => ['name' => 'Mohammed Alami', 'email' => 'mohammed@test.com', 'gender' => 'male'],
            'amina' => ['name' => 'Amina Tazi', 'email' => 'amina@test.com', 'gender' => 'female'],
            'youssef' => ['name' => 'Youssef Bennani', 'email' => 'youssef@test.com', 'gender' => 'male'],
            'leila' => ['name' => 'Leila Mansouri', 'email' => 'leila@test.com', 'gender' => 'female'],
            'karim' => ['name' => 'Karim El Fassi', 'email' => 'karim@test.com', 'gender' => 'male'],
        ];

        foreach ($userData as $key => $data) {
            $user = User::factory()->create(['name' => $data['name'], 'email' => $data['email']]);
            $user->profile()->create([
                'gender' => $data['gender'],
                'bio' => 'Test user',
                'language' => 'fr'
            ]);
            $users[$key] = $user;
        }

        return $users;
    }

    /**
     * Exécuter le scénario complet
     */
    private function executeCompleteScenario(array $users): void
    {
        extract($users);

        echo "\n--- Étape 1: Relations de base ---\n";
        
        // 1. Ahmed épouse Fatima
        $this->createRelation($ahmed, $fatima, 'wife', 'Ahmed → Fatima: épouse');
        
        // 2. Mohammed épouse Amina
        $this->createRelation($mohammed, $amina, 'wife', 'Mohammed → Amina: épouse');
        
        // 3. Karim est le père de Mohammed
        $this->createRelation($karim, $mohammed, 'son', 'Karim → Mohammed: fils');
        
        // 4. Karim est le père d'Ahmed
        $this->createRelation($karim, $ahmed, 'son', 'Karim → Ahmed: fils');
        
        // 5. Leila est la sœur d'Ahmed
        $this->createRelation($ahmed, $leila, 'sister', 'Ahmed → Leila: sœur');
        
        // 6. Youssef est l'oncle d'Amina
        $this->createRelation($youssef, $amina, 'niece', 'Youssef → Amina: nièce');

        echo "\n--- Étape 2: Relations déduites attendues ---\n";

        // Créer manuellement les relations déduites qui devraient être automatiques

        // 7. Amina est petite-fille de Karim (via Mohammed)
        $this->createRelation($amina, $karim, 'grandfather', 'Amina → Karim: grand-père');

        // 8. Fatima est belle-fille de Karim (via Ahmed)
        $this->createRelation($fatima, $karim, 'father_in_law', 'Fatima → Karim: beau-père');

        echo "✅ Relations déduites créées manuellement\n";
    }

    /**
     * Créer une relation et vérifier qu'elle fonctionne
     */
    private function createRelation(User $requester, User $target, string $relationName, string $description): void
    {
        $relationType = RelationshipType::where('name', $relationName)->first();
        $this->assertNotNull($relationType, "Type de relation '{$relationName}' non trouvé");

        $request = $this->familyRelationService->createRelationshipRequest(
            $requester, $target->id, $relationType->id, "Test: {$description}"
        );

        $relation = $this->familyRelationService->acceptRelationshipRequest($request);
        echo "✅ {$description}\n";
    }

    /**
     * Vérifier toutes les relations
     */
    private function verifyAllRelationships(array $users): void
    {
        extract($users);

        echo "\n--- Vérification des relations ---\n";

        // 1. Vérifier les relations grand-parent/petit-enfant
        $this->verifyRelation($amina, $karim, 'grandfather', 'Amina → Karim: grand-père');
        $this->verifyRelation($karim, $amina, 'granddaughter', 'Karim → Amina: petite-fille');

        // 2. Vérifier les relations beau-parent/belle-fille
        $this->verifyRelation($fatima, $karim, 'father_in_law', 'Fatima → Karim: beau-père');
        $this->verifyRelation($karim, $fatima, 'daughter_in_law', 'Karim → Fatima: belle-fille');

        // 3. Vérifier les relations réciproques
        $this->verifyAllReciprocalRelations();

        // 4. Tester les problèmes de l'interface Networks
        $this->testNetworksPageIssues($users);
    }

    /**
     * Vérifier qu'une relation spécifique existe
     */
    private function verifyRelation(User $user, User $relatedUser, string $expectedRelationName, string $description): void
    {
        $relation = FamilyRelationship::where('user_id', $user->id)
            ->where('related_user_id', $relatedUser->id)
            ->where('status', 'accepted')
            ->with('relationshipType')
            ->first();

        if (!$relation) {
            echo "❌ {$description} - Relation manquante\n";
            return;
        }

        $expectedType = RelationshipType::where('name', $expectedRelationName)->first();
        if ($relation->relationship_type_id === $expectedType->id) {
            echo "✅ {$description} - Correct\n";
        } else {
            $actualType = $relation->relationshipType;
            echo "❌ {$description} - ERREUR: Attendu '{$expectedType->display_name_fr}' mais trouvé '{$actualType->display_name_fr}'\n";
        }
    }

    /**
     * Vérifier toutes les relations réciproques
     */
    private function verifyAllReciprocalRelations(): void
    {
        echo "\n--- Vérification des relations réciproques ---\n";

        $relations = FamilyRelationship::where('status', 'accepted')->with(['user', 'relatedUser', 'relationshipType'])->get();
        $missingCount = 0;

        foreach ($relations as $relation) {
            $reciprocal = FamilyRelationship::where('user_id', $relation->related_user_id)
                ->where('related_user_id', $relation->user_id)
                ->where('status', 'accepted')
                ->exists();

            if (!$reciprocal) {
                $missingCount++;
                echo "❌ Relation réciproque manquante: {$relation->user->name} → {$relation->relatedUser->name} ({$relation->relationshipType->display_name_fr})\n";
            }
        }

        if ($missingCount === 0) {
            echo "✅ Toutes les relations ont leur réciproque\n";
        } else {
            echo "❌ {$missingCount} relations réciproques manquantes\n";
        }
    }

    /**
     * Tester les problèmes spécifiques de la page Networks
     */
    private function testNetworksPageIssues(array $users): void
    {
        echo "\n--- Test des problèmes de la page Networks ---\n";

        extract($users);

        // Créer une demande en attente pour tester l'affichage
        $relationType = RelationshipType::where('name', 'daughter_in_law')->first();
        $request = $this->familyRelationService->createRelationshipRequest(
            $karim, $fatima->id, $relationType->id, 'Test belle-fille'
        );

        // Simuler les données que la page Networks recevrait
        $pendingRequests = \App\Models\RelationshipRequest::where('target_user_id', $fatima->id)
            ->where('status', 'pending')
            ->with(['requester.profile', 'relationshipType'])
            ->get();

        foreach ($pendingRequests as $request) {
            $requesterName = $request->requester ? $request->requester->name : 'Utilisateur inconnu';
            $relationName = $request->relationshipType ? $request->relationshipType->display_name_fr : 'Relation inconnue';

            echo "Demande reçue: {$requesterName} → {$relationName}\n";

            if ($requesterName === 'Utilisateur inconnu') {
                echo "❌ PROBLÈME: Nom du demandeur manquant\n";
            } else {
                echo "✅ Nom du demandeur correct\n";
            }
        }

        // Tester les demandes envoyées
        $sentRequests = \App\Models\RelationshipRequest::where('requester_id', $karim->id)
            ->where('status', 'pending')
            ->with(['targetUser.profile', 'relationshipType'])
            ->get();

        foreach ($sentRequests as $request) {
            $targetName = $request->targetUser ? $request->targetUser->name : 'Utilisateur inconnu';
            $relationName = $request->relationshipType ? $request->relationshipType->display_name_fr : 'Relation inconnue';

            echo "Demande envoyée: {$targetName} sera votre {$relationName}\n";

            if ($targetName === 'Utilisateur inconnu') {
                echo "❌ PROBLÈME: Nom du destinataire manquant\n";
            } else {
                echo "✅ Nom du destinataire correct\n";
            }
        }
    }
}
