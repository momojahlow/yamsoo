<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationInverseLogicTest extends TestCase
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
     * Test de la logique de relation inverse dans les demandes reçues
     */
    public function test_relation_inverse_logic_in_received_requests(): void
    {
        echo "\n=== TEST LOGIQUE RELATION INVERSE ===\n";

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

        echo "📝 DEMANDE CRÉÉE:\n";
        echo "   - Demandeur: {$amina->name}\n";
        echo "   - Cible: {$fatima->name}\n";
        echo "   - Relation demandée: {$relationshipType->display_name_fr} (mother)\n";
        echo "   - Message: Amina demande à Fatima d'être sa mère\n\n";

        // Tester la page Networks en tant que Fatima (qui reçoit la demande)
        $response = $this->actingAs($fatima)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont correctes
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Amina Tazi')
                ->where('pendingRequests.0.relationship_name', 'Mère')
        );

        echo "🔍 VÉRIFICATION CÔTÉ FATIMA (qui reçoit la demande):\n";
        echo "   ✅ Fatima voit une demande de: Amina Tazi\n";
        echo "   ✅ Relation affichée: 'Mère' (relation demandée)\n";
        echo "   📋 Logique: Amina veut que Fatima soit sa 'Mère'\n";
        echo "   💡 Dans l'interface: Fatima verra 'Fille' (relation inverse calculée côté client)\n\n";

        // Tester la page Networks en tant qu'Amina (qui envoie la demande)
        $response = $this->actingAs($amina)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les demandes envoyées sont affichées correctement
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('sentRequests', 1)
                ->where('sentRequests.0.target_user_name', 'Fatima Zahra')
                ->where('sentRequests.0.relationship_name', 'Mère')
        );

        echo "🔍 VÉRIFICATION CÔTÉ AMINA (qui envoie la demande):\n";
        echo "   ✅ Amina voit sa demande envoyée à: Fatima Zahra\n";
        echo "   ✅ Relation affichée: 'Mère' (relation demandée)\n";
        echo "   📋 Logique: Amina a demandé à Fatima d'être sa 'Mère'\n\n";

        echo "🎯 RÉSUMÉ DE LA LOGIQUE:\n";
        echo "   1. Amina demande à Fatima d'être sa 'Mère'\n";
        echo "   2. Fatima reçoit: 'Amina veut que vous soyez sa Mère'\n";
        echo "   3. Interface côté client: Fatima voit 'Fille' (relation inverse)\n";
        echo "   4. Quand acceptée: Amina sera 'Fille' de Fatima\n";

        echo "\n🎉 TEST LOGIQUE RELATION INVERSE TERMINÉ\n";
    }

    /**
     * Test avec différents types de relations
     */
    public function test_multiple_relation_types_inverse_logic(): void
    {
        echo "\n=== TEST RELATIONS MULTIPLES ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Test 1: Karim demande à être "mari" de Fatima
        $wifeType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $karim, $fatima->id, $wifeType->id, 'Karim demande à être mari de Fatima'
        );

        echo "📝 TEST 1 - RELATION MARI/ÉPOUSE:\n";
        echo "   - Karim demande à être 'Mari' de Fatima\n";
        echo "   - Fatima devrait voir 'Mari' dans les demandes reçues\n";
        echo "   - Interface: Fatima verra 'Épouse' (relation inverse)\n\n";

        // Test 2: Fatima demande à être "sœur" de Karim
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $sisterType->id, 'Fatima demande à être sœur de Karim'
        );

        echo "📝 TEST 2 - RELATION FRÈRE/SŒUR:\n";
        echo "   - Fatima demande à être 'Sœur' de Karim\n";
        echo "   - Karim devrait voir 'Sœur' dans les demandes reçues\n";
        echo "   - Interface: Karim verra 'Frère' (relation inverse)\n\n";

        // Vérifier les demandes reçues par Fatima
        $response = $this->actingAs($fatima)->get('/reseaux');
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.relationship_name', 'Mari')
        );

        // Vérifier les demandes reçues par Karim
        $response = $this->actingAs($karim)->get('/reseaux');
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.relationship_name', 'Sœur')
        );

        echo "✅ Toutes les relations testées avec succès\n";
        echo "\n🎉 TEST RELATIONS MULTIPLES TERMINÉ\n";
    }
}
