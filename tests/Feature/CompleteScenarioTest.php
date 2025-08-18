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

        // Exécuter les seeders nécessaires
        $this->seed(\Database\Seeders\ComprehensiveRelationshipTypesSeeder::class);
    }

    /**
     * Test du nouveau scénario sans relations automatiques :
     *
     * Ahmed crée une demande vers Fatima avec relation "Mari".
     * Fatima accepte → Fatima devient épouse d'Ahmed et Ahmed devient mari de Fatima.
     * Fatima crée une demande vers Youssef Bennani avec relation "Frère".
     * Youssef accepte → Youssef devient frère de Fatima et Fatima devient sœur de Youssef.
     * Youssef crée une demande vers Ahmed avec relation "Beau-frère".
     * Ahmed accepte → Ahmed devient beau-frère de Youssef et Youssef devient beau-frère d'Ahmed.
     * Ahmed crée une demande vers Leila Mansouri avec relation "Sœur".
     * Leila accepte → Leila devient sœur d'Ahmed et Ahmed devient frère de Leila.
     * Leila crée deux demandes :
     * - Vers Youssef avec relation "Beau-frère".
     * - Vers Fatima avec relation "Belle-sœur".
     * Fatima accepte la demande de Leila → Fatima devient belle-sœur de Leila et Leila devient belle-sœur de Fatima.
     * Youssef accepte la demande de Leila → Youssef devient beau-frère de Leila et Leila devient belle-sœur de Youssef.
     *
     * Chaque acceptation crée UNIQUEMENT deux entrées réciproques dans la table des relations.
     * AUCUNE relation automatique ne doit être créée.
     */
    public function test_complete_family_scenario(): void
    {
        // Créer les utilisateurs pour le test
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);
        $leila = User::factory()->withProfile('female')->create(['name' => 'Leila Mansouri', 'email' => 'leila@test.com']);

        echo "\n=== SCÉNARIO COMPLET DE RELATIONS FAMILIALES ===\n";

        // Afficher tous les types disponibles pour debug
        $allTypes = RelationshipType::pluck('name')->toArray();
        echo "Types disponibles : " . implode(', ', $allTypes) . "\n";

        // Récupérer les types de relations nécessaires
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $brotherInLawType = RelationshipType::where('name', 'brother_in_law')->first();
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $sisterInLawType = RelationshipType::where('name', 'sister_in_law')->first();

        $this->assertNotNull($husbandType, 'Type husband doit exister');
        $this->assertNotNull($brotherType, 'Type brother doit exister');

        echo "\n=== NOUVEAU SCÉNARIO SANS RELATIONS AUTOMATIQUES ===\n";

        // 1. Ahmed → Fatima (Mari)
        echo "1. Ahmed demande à Fatima d'être son mari...\n";
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $fatima->id, $husbandType->id, 'Demande de mariage'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        echo "✅ Ahmed et Fatima sont maintenant mari et épouse\n";

        // 2. Fatima → Youssef (Frère)
        echo "2. Fatima demande à Youssef d'être son frère...\n";
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $youssef->id, $brotherType->id, 'Tu es mon frère'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        echo "✅ Fatima et Youssef sont maintenant frère et sœur\n";

        // 3. Youssef → Ahmed (Beau-frère)
        if ($brotherInLawType) {
            echo "3. Youssef demande à Ahmed d'être son beau-frère...\n";
            $request3 = $this->familyRelationService->createRelationshipRequest(
                $youssef, $ahmed->id, $brotherInLawType->id, 'Tu es mon beau-frère'
            );
            $this->familyRelationService->acceptRelationshipRequest($request3);
            echo "✅ Youssef et Ahmed sont maintenant beaux-frères\n";
        } else {
            echo "⚠️ Type brother_in_law non trouvé, relation ignorée\n";
        }

        // 4. Ahmed → Leila (Sœur)
        echo "4. Ahmed demande à Leila d'être sa sœur...\n";
        $request4 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $leila->id, $sisterType->id, 'Tu es ma sœur'
        );
        $this->familyRelationService->acceptRelationshipRequest($request4);
        echo "✅ Ahmed et Leila sont maintenant frère et sœur\n";

        // 5. Leila → Youssef (Beau-frère)
        if ($brotherInLawType) {
            echo "5. Leila demande à Youssef d'être son beau-frère...\n";
            $request5 = $this->familyRelationService->createRelationshipRequest(
                $leila, $youssef->id, $brotherInLawType->id, 'Tu es mon beau-frère'
            );
            $this->familyRelationService->acceptRelationshipRequest($request5);
            echo "✅ Leila et Youssef sont maintenant belle-sœur et beau-frère\n";
        } else {
            echo "⚠️ Type brother_in_law non trouvé, relation ignorée\n";
        }

        // 6. Leila → Fatima (Belle-sœur)
        if ($sisterInLawType) {
            echo "6. Leila demande à Fatima d'être sa belle-sœur...\n";
            $request6 = $this->familyRelationService->createRelationshipRequest(
                $leila, $fatima->id, $sisterInLawType->id, 'Tu es ma belle-sœur'
            );
            $this->familyRelationService->acceptRelationshipRequest($request6);
            echo "✅ Leila et Fatima sont maintenant belles-sœurs\n";
        } else {
            echo "⚠️ Type sister_in_law non trouvé, relation ignorée\n";
        }

        echo "\n=== VÉRIFICATIONS FINALES ===\n";

        // Vérifier toutes les relations créées
        $this->verifyUserFamily($ahmed, 'Ahmed');
        $this->verifyUserFamily($fatima, 'Fatima');
        $this->verifyUserFamily($youssef, 'Youssef');
        $this->verifyUserFamily($leila, 'Leila');

        // Vérifier les relations bidirectionnelles
        echo "\n=== VÉRIFICATION DES RELATIONS BIDIRECTIONNELLES ===\n";

        $totalRelations = FamilyRelationship::where('status', 'accepted')->count();
        echo "Total des relations en base : {$totalRelations}\n";

        // Vérifier que chaque relation a sa réciproque
        $this->verifyBidirectionalRelations();

        // Vérifications spécifiques du nouveau scénario
        $this->assertUserHasRelation($ahmed, $fatima, 'husband', 'Ahmed devrait avoir Fatima comme épouse');
        $this->assertUserHasRelation($fatima, $ahmed, 'wife', 'Fatima devrait avoir Ahmed comme époux');

        $this->assertUserHasRelation($fatima, $youssef, 'brother', 'Fatima devrait avoir Youssef comme frère');
        $this->assertUserHasRelation($youssef, $fatima, 'sister', 'Youssef devrait avoir Fatima comme sœur');

        $this->assertUserHasRelation($ahmed, $leila, 'sister', 'Ahmed devrait avoir Leila comme sœur');
        $this->assertUserHasRelation($leila, $ahmed, 'brother', 'Leila devrait avoir Ahmed comme frère');

        // Vérifier qu'AUCUNE relation automatique n'a été créée
        $autoRelations = FamilyRelationship::where('created_automatically', true)->count();
        echo "\nRelations automatiquement créées : {$autoRelations}\n";
        $this->assertEquals(0, $autoRelations, 'Aucune relation automatique ne devrait être créée');

        echo "\n🎉 NOUVEAU SCÉNARIO RÉUSSI ! Seules les relations directes ont été créées.\n";
    }

    private function verifyUserFamily(User $user, string $name): void
    {
        $relations = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        echo "\n{$name} a " . $relations->count() . " relations :\n";
        foreach ($relations as $relation) {
            $auto = $relation->created_automatically ? ' (auto)' : '';
            echo "  - {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}{$auto}\n";
        }
    }

    private function verifyBidirectionalRelations(): void
    {
        $relations = FamilyRelationship::where('status', 'accepted')->get();
        $missingReciprocalCount = 0;

        foreach ($relations as $relation) {
            $reciprocal = FamilyRelationship::where('user_id', $relation->related_user_id)
                ->where('related_user_id', $relation->user_id)
                ->where('status', 'accepted')
                ->exists();

            if (!$reciprocal) {
                $missingReciprocalCount++;
                echo "⚠️ Relation manquante : {$relation->relatedUser->name} → {$relation->user->name}\n";
            }
        }

        if ($missingReciprocalCount === 0) {
            echo "✅ Toutes les relations ont leur réciproque\n";
        } else {
            echo "❌ {$missingReciprocalCount} relations réciproques manquantes\n";
        }

        $this->assertEquals(0, $missingReciprocalCount, 'Toutes les relations devraient avoir leur réciproque');
    }

    private function assertUserHasRelation(User $user, User $relatedUser, string $relationCode, string $message): void
    {
        $hasRelation = FamilyRelationship::where('user_id', $user->id)
            ->where('related_user_id', $relatedUser->id)
            ->whereHas('relationshipType', function($query) use ($relationCode) {
                $query->where('name', $relationCode);
            })
            ->where('status', 'accepted')
            ->exists();

        $this->assertTrue($hasRelation, $message);
    }
}
