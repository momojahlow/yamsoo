<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworksDisplayTest extends TestCase
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
     * Test l'affichage des demandes dans la page Networks
     */
    public function test_networks_page_displays_requests_correctly(): void
    {
        echo "\n=== TEST AFFICHAGE DEMANDES NETWORKS ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Créer une demande de relation
        $relationshipType = RelationshipType::where('name', 'daughter_in_law')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $relationshipType->id, 'Test: Fatima demande à être belle-fille de Karim'
        );

        echo "✅ Demande créée: {$fatima->name} → {$karim->name} ({$relationshipType->display_name_fr})\n";

        // Tester la page Networks en tant que Karim (qui reçoit la demande)
        $response = $this->actingAs($karim)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont passées correctement
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Fatima Zahra')
                ->where('pendingRequests.0.requester_email', 'fatima@test.com')
                ->where('pendingRequests.0.relationship_name', 'Belle-fille')
                ->where('pendingRequests.0.message', 'Test: Fatima demande à être belle-fille de Karim')
        );

        echo "✅ Demande reçue affichée correctement pour Karim\n";
        echo "   - Nom du demandeur: Fatima Zahra\n";
        echo "   - Relation: Belle-fille\n";
        echo "   - Message: Test: Fatima demande à être belle-fille de Karim\n";

        // Tester la page Networks en tant que Fatima (qui envoie la demande)
        $response = $this->actingAs($fatima)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les demandes envoyées sont affichées correctement
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('sentRequests', 1)
                ->where('sentRequests.0.target_user_name', 'Karim El Fassi')
                ->where('sentRequests.0.target_user_email', 'karim@test.com')
                ->where('sentRequests.0.relationship_name', 'Belle-fille')
        );

        echo "✅ Demande envoyée affichée correctement pour Fatima\n";
        echo "   - Nom du destinataire: Karim El Fassi\n";
        echo "   - Relation: Belle-fille\n";

        echo "\n🎉 TEST AFFICHAGE DEMANDES TERMINÉ\n";
    }
}
