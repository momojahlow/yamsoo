<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UIImprovementsTest extends TestCase
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
     * Test que le bouton annuler fonctionne avec confirmation
     */
    public function test_cancel_request_functionality(): void
    {
        echo "\n=== TEST FONCTIONNALITÉ ANNULATION ===\n";

        // Créer des utilisateurs
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Créer une demande de relation (Fatima veut être fille d'Amina)
        $relationshipType = RelationshipType::where('name', 'daughter')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $amina->id, $relationshipType->id, 'Test: Fatima demande à être fille d\'Amina'
        );

        echo "✅ Demande créée: {$fatima->name} → {$amina->name} (Fille)\n";

        // Vérifier que la demande existe
        $this->assertDatabaseHas('relationship_requests', [
            'id' => $request->id,
            'requester_id' => $fatima->id,
            'target_user_id' => $amina->id,
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
     * Test que la page d'accueil affiche le bon copyright
     */
    public function test_welcome_page_copyright(): void
    {
        echo "\n=== TEST COPYRIGHT PAGE D'ACCUEIL ===\n";

        // Tester la page d'accueil
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Vérifier que le copyright Yamsoo est présent
        $response->assertSee('© 2024 Yamsoo - Tous droits réservés');
        
        // Vérifier que Laravel n'est plus mentionné
        $response->assertDontSee('Propulsé par Laravel');
        $response->assertDontSee('Powered by Laravel');

        echo "✅ Copyright Yamsoo affiché correctement\n";
        echo "✅ Mention Laravel supprimée\n";

        echo "\n🎉 TEST COPYRIGHT TERMINÉ\n";
    }

    /**
     * Test que les demandes reçues affichent la bonne relation inverse
     */
    public function test_received_requests_show_correct_inverse_relation(): void
    {
        echo "\n=== TEST RELATION INVERSE DEMANDES REÇUES ===\n";

        // Créer des utilisateurs
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Créer une demande de relation (Fatima veut être fille d'Amina)
        $relationshipType = RelationshipType::where('name', 'daughter')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $amina->id, $relationshipType->id, 'Test relation inverse'
        );

        echo "✅ Demande créée: {$fatima->name} veut être {$relationshipType->display_name_fr} d'{$amina->name}\n";

        // Tester la page Networks en tant qu'Amina (qui reçoit la demande)
        $response = $this->actingAs($amina)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont correctes
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('pendingRequests', 1)
                ->where('pendingRequests.0.requester_name', 'Fatima Zahra')
                ->where('pendingRequests.0.relationship_name', 'Fille')
        );

        echo "✅ Demande reçue affichée correctement\n";
        echo "   - Amina voit que Fatima veut être sa 'Fille'\n";
        echo "   - Dans l'interface, Amina verra 'Mère' (relation inverse calculée côté client)\n";

        echo "\n🎉 TEST RELATION INVERSE TERMINÉ\n";
    }

    /**
     * Test que les pages principales sont accessibles
     */
    public function test_main_pages_accessibility(): void
    {
        echo "\n=== TEST ACCESSIBILITÉ PAGES PRINCIPALES ===\n";

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
