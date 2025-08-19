<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InverseRelationTest extends TestCase
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
     * Test direct de la méthode getPublicInverseRelationshipType
     */
    public function test_inverse_relationship_calculation_direct(): void
    {
        echo "\n=== TEST DIRECT RELATION INVERSE ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Obtenir le type de relation "wife"
        $wifeType = RelationshipType::where('name', 'wife')->first();
        $this->assertNotNull($wifeType, 'Le type de relation "wife" doit exister');
        
        echo "✅ Type de relation trouvé: {$wifeType->name} ({$wifeType->display_name_fr})\n";

        // Tester la méthode directement
        $inverseType = $this->familyRelationService->getPublicInverseRelationshipType(
            $wifeType->id,
            $fatima,
            $karim
        );

        $this->assertNotNull($inverseType, 'La relation inverse ne doit pas être null');
        echo "✅ Relation inverse calculée: {$inverseType->name} ({$inverseType->display_name_fr})\n";
        
        $this->assertEquals('husband', $inverseType->name, 'La relation inverse de "wife" doit être "husband"');
        $this->assertEquals('Mari', $inverseType->display_name_fr, 'Le nom français doit être "Mari"');

        echo "\n🎉 TEST DIRECT TERMINÉ\n";
    }

    /**
     * Test via la création d'une demande et l'API
     */
    public function test_inverse_relationship_via_api(): void
    {
        echo "\n=== TEST VIA API ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Créer une demande de relation
        $relationshipType = RelationshipType::where('name', 'wife')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $relationshipType->id, 'Test relation inverse'
        );

        echo "✅ Demande créée: {$fatima->name} veut être {$relationshipType->display_name_fr} de {$karim->name}\n";

        // Appeler l'endpoint pour obtenir les données
        $response = $this->actingAs($karim)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Récupérer les données Inertia
        $props = $response->getOriginalContent()->getData()['page']['props'];
        
        $this->assertArrayHasKey('pendingRequests', $props);
        $this->assertCount(1, $props['pendingRequests']);
        
        $pendingRequest = $props['pendingRequests'][0];
        
        echo "Données de la demande reçue:\n";
        echo "- requester_name: {$pendingRequest['requester_name']}\n";
        echo "- relationship_name: {$pendingRequest['relationship_name']}\n";
        echo "- inverse_relationship_name: " . ($pendingRequest['inverse_relationship_name'] ?? 'null') . "\n";
        
        $this->assertArrayHasKey('inverse_relationship_name', $pendingRequest);
        $this->assertNotNull($pendingRequest['inverse_relationship_name']);
        $this->assertEquals('Mari', $pendingRequest['inverse_relationship_name']);

        echo "✅ Relation inverse présente dans l'API: {$pendingRequest['inverse_relationship_name']}\n";

        echo "\n🎉 TEST VIA API TERMINÉ\n";
    }
}
