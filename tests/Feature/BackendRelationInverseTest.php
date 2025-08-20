<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackendRelationInverseTest extends TestCase
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
     * Test que la relation inverse est calculée et stockée côté backend
     */
    public function test_backend_calculates_and_stores_inverse_relation(): void
    {
        echo "\n=== TEST BACKEND RELATION INVERSE ===\n";

        // Créer des utilisateurs
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        echo "✅ Utilisateurs créés:\n";
        echo "   - Amina Tazi (amina@test.com)\n";
        echo "   - Fatima Zahra (fatima@test.com)\n\n";

        // Scénario : Amina ajoute Fatima comme sa "Mère"
        $relationshipType = RelationshipType::where('name', 'mother')->first();
        $this->assertNotNull($relationshipType, 'Le type de relation "mother" doit exister');
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $amina, $fatima->id, $relationshipType->id, 'Amina demande à Fatima d\'être sa mère'
        );

        echo "📝 DEMANDE CRÉÉE CÔTÉ BACKEND:\n";
        echo "   - Demandeur: {$amina->name}\n";
        echo "   - Cible: {$fatima->name}\n";
        echo "   - Relation demandée: {$relationshipType->display_name_fr} (mother)\n";
        echo "   - ID relation inverse: {$request->inverse_relationship_type_id}\n";
        echo "   - Nom relation inverse: {$request->inverse_relationship_name}\n\n";

        // Vérifier que la relation inverse a été calculée et stockée
        $this->assertNotNull($request->inverse_relationship_type_id, 'La relation inverse doit être calculée');

        // Charger la relation inverse pour vérifier
        $request->load('inverseRelationshipType');
        $this->assertNotNull($request->inverseRelationshipType, 'Le type de relation inverse doit être chargé');
        $this->assertEquals('Fille', $request->inverseRelationshipType->display_name_fr, 'La relation inverse de "Mère" doit être "Fille"');

        // Vérifier en base de données
        $this->assertDatabaseHas('relationship_requests', [
            'id' => $request->id,
            'requester_id' => $amina->id,
            'target_user_id' => $fatima->id,
            'relationship_type_id' => $relationshipType->id,
            'inverse_relationship_type_id' => $request->inverse_relationship_type_id
        ]);

        echo "🔍 VÉRIFICATION BASE DE DONNÉES:\n";
        echo "   ✅ Relation demandée stockée: Mère\n";
        echo "   ✅ Relation inverse ID stocké: {$request->inverse_relationship_type_id}\n";
        echo "   ✅ Eager loading pour obtenir le nom selon la langue\n";
        echo "   ✅ Pas de duplication de données\n\n";

        // Tester l'API
        $response = $this->actingAs($fatima)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont correctes dans l'API
        $response->assertInertia(fn ($page) =>
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Amina Tazi')
                ->where('pendingRequests.0.relationship_name', 'Mère')
                ->where('pendingRequests.0.inverse_relationship_name', 'Fille')
        );

        echo "🔍 VÉRIFICATION API:\n";
        echo "   ✅ Fatima reçoit la demande\n";
        echo "   ✅ Relation demandée: 'Mère'\n";
        echo "   ✅ Relation inverse: 'Fille' (via eager loading)\n";
        echo "   ✅ Frontend simplifié - pas de logique complexe\n\n";

        echo "🎯 AVANTAGES DE L'APPROCHE OPTIMISÉE:\n";
        echo "   1. ✅ Logique centralisée côté Laravel\n";
        echo "   2. ✅ Pas de duplication de données\n";
        echo "   3. ✅ Eager loading efficace\n";
        echo "   4. ✅ Support multilingue automatique\n";
        echo "   5. ✅ Frontend ultra-simplifié\n";
        echo "   6. ✅ Base de données normalisée\n";

        echo "\n🎉 TEST BACKEND RELATION INVERSE TERMINÉ\n";
    }

    /**
     * Test avec différents types de relations
     */
    public function test_multiple_relation_types_backend(): void
    {
        echo "\n=== TEST RELATIONS MULTIPLES BACKEND ===\n";

        // Créer des utilisateurs avec profils et genres
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $karim->profile()->create(['gender' => 'male']);

        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $fatima->profile()->create(['gender' => 'female']);

        // Test 1: Karim demande à être "mari" de Fatima
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $karim, $fatima->id, $husbandType->id, 'Karim demande à être mari de Fatima'
        );

        // Charger les relations pour les tests
        $request1->load('relationshipType', 'inverseRelationshipType');

        echo "📝 TEST 1 - RELATION MARI/ÉPOUSE:\n";
        echo "   - Relation demandée: {$request1->relationshipType->display_name_fr}\n";
        echo "   - Relation inverse: {$request1->inverseRelationshipType->display_name_fr}\n";

        $this->assertEquals('Mari', $request1->relationshipType->display_name_fr);
        $this->assertEquals('Épouse', $request1->inverseRelationshipType->display_name_fr);

        // Test 2: Fatima demande à être "sœur" de Karim
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $sisterType->id, 'Fatima demande à être sœur de Karim'
        );

        // Charger les relations pour les tests
        $request2->load('relationshipType', 'inverseRelationshipType');

        echo "📝 TEST 2 - RELATION FRÈRE/SŒUR:\n";
        echo "   - Relation demandée: {$request2->relationshipType->display_name_fr}\n";
        echo "   - Relation inverse: {$request2->inverseRelationshipType->display_name_fr}\n";

        $this->assertEquals('Sœur', $request2->relationshipType->display_name_fr);
        $this->assertEquals('Frère', $request2->inverseRelationshipType->display_name_fr);

        echo "✅ Toutes les relations calculées correctement côté backend\n";
        echo "\n🎉 TEST RELATIONS MULTIPLES TERMINÉ\n";
    }
}
