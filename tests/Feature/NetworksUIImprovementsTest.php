<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworksUIImprovementsTest extends TestCase
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
     * Test que le bouton annuler fonctionne pour les demandes envoyées
     */
    public function test_cancel_sent_request(): void
    {
        echo "\n=== TEST ANNULATION DEMANDE ENVOYÉE ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Créer une demande de relation
        $relationshipType = RelationshipType::where('name', 'wife')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $relationshipType->id, 'Test: Fatima demande à être épouse de Karim'
        );

        echo "✅ Demande créée: {$fatima->name} → {$karim->name} (ID: {$request->id})\n";

        // Vérifier que la demande existe
        $this->assertDatabaseHas('relationship_requests', [
            'id' => $request->id,
            'requester_id' => $fatima->id,
            'target_user_id' => $karim->id,
            'status' => 'pending'
        ]);

        // Tester l'annulation de la demande
        $response = $this->actingAs($fatima)->delete("/family-relations/{$request->id}");
        
        $response->assertRedirect();
        
        // Vérifier que la demande a été supprimée
        $this->assertDatabaseMissing('relationship_requests', [
            'id' => $request->id
        ]);

        echo "✅ Demande annulée avec succès\n";

        echo "\n🎉 TEST ANNULATION TERMINÉ\n";
    }

    /**
     * Test que la relation inverse est correctement calculée
     */
    public function test_inverse_relationship_calculation(): void
    {
        echo "\n=== TEST RELATION INVERSE ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Créer une demande de relation (Fatima veut être épouse de Karim)
        $relationshipType = RelationshipType::where('name', 'wife')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $relationshipType->id, 'Test relation inverse'
        );

        echo "✅ Demande créée: {$fatima->name} veut être {$relationshipType->display_name_fr} de {$karim->name}\n";

        // Tester la page Networks en tant que Karim (qui reçoit la demande)
        $response = $this->actingAs($karim)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données incluent la relation inverse
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Fatima Zahra')
                ->where('pendingRequests.0.relationship_name', 'Épouse')
                ->has('pendingRequests.0.inverse_relationship_name')
        );

        echo "✅ Relation inverse calculée et incluse dans les données\n";

        echo "\n🎉 TEST RELATION INVERSE TERMINÉ\n";
    }

    /**
     * Test que les pages sont accessibles
     */
    public function test_pages_accessibility(): void
    {
        echo "\n=== TEST ACCESSIBILITÉ PAGES ===\n";

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        // Tester la page d'accueil
        $response = $this->get('/');
        $response->assertStatus(200);
        echo "✅ Page d'accueil accessible\n";

        // Tester la page Dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        echo "✅ Page Dashboard accessible\n";

        // Tester la page Networks
        $response = $this->actingAs($user)->get('/reseaux');
        $response->assertStatus(200);
        echo "✅ Page Networks accessible\n";

        echo "\n🎉 TEST ACCESSIBILITÉ TERMINÉ\n";
    }
}
