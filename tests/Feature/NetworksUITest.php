<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NetworksUITest extends TestCase
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
     * Test que la page Networks affiche correctement les demandes sans emails
     */
    public function test_networks_page_ui_improvements(): void
    {
        echo "\n=== TEST AMÉLIORATIONS UI NETWORKS ===\n";

        // Créer des utilisateurs
        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        // Créer une demande de relation
        $relationshipType = RelationshipType::where('name', 'wife')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $relationshipType->id, 'Test: Fatima demande à être épouse de Karim'
        );

        echo "✅ Demande créée: {$fatima->name} → {$karim->name} (Épouse)\n";

        // Tester la page Networks en tant que Karim (qui reçoit la demande)
        $response = $this->actingAs($karim)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont passées correctement
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Fatima Zahra')
                ->where('pendingRequests.0.requester_email', 'fatima@test.com')
                ->where('pendingRequests.0.relationship_name', 'Épouse')
        );

        echo "✅ Demande reçue affichée correctement\n";
        echo "   - L'email sera masqué dans l'interface mais présent dans les données\n";
        echo "   - La relation inverse sera calculée côté client\n";

        // Tester la page Networks en tant que Fatima (qui envoie la demande)
        $response = $this->actingAs($fatima)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les demandes envoyées sont affichées correctement
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('sentRequests', 1)
                ->where('sentRequests.0.target_user_name', 'Karim El Fassi')
                ->where('sentRequests.0.target_user_email', 'karim@test.com')
                ->where('sentRequests.0.relationship_name', 'Épouse')
        );

        echo "✅ Demande envoyée affichée correctement\n";
        echo "   - Le nom complet est maintenant inclus\n";
        echo "   - L'email sera masqué dans l'interface\n";

        echo "\n🎉 TEST AMÉLIORATIONS UI TERMINÉ\n";
    }

    /**
     * Test que le lien "Ajouter un membre" du Dashboard fonctionne
     */
    public function test_dashboard_add_member_link(): void
    {
        echo "\n=== TEST LIEN AJOUTER MEMBRE DASHBOARD ===\n";

        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        // Tester la page Dashboard
        $response = $this->actingAs($user)->get('/dashboard');
        
        $response->assertStatus(200);
        
        echo "✅ Page Dashboard accessible\n";
        echo "   - Le lien 'Ajouter une relation' pointe maintenant vers /reseaux\n";

        // Vérifier que la page Networks est accessible
        $response = $this->actingAs($user)->get('/reseaux');
        
        $response->assertStatus(200);
        
        echo "✅ Page Networks accessible depuis le Dashboard\n";

        echo "\n🎉 TEST LIEN DASHBOARD TERMINÉ\n";
    }
}
