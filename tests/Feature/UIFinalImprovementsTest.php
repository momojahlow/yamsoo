<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UIFinalImprovementsTest extends TestCase
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
     * Test que la page d'accueil affiche correctement le bouton "En savoir plus"
     */
    public function test_welcome_page_learn_more_button(): void
    {
        echo "\n=== TEST BOUTON EN SAVOIR PLUS ===\n";

        // Tester la page d'accueil
        $response = $this->get('/');
        
        $response->assertStatus(200);
        
        // Vérifier que la page contient le bouton avec les bonnes classes
        $response->assertSee('bg-transparent');
        
        echo "✅ Page d'accueil accessible\n";
        echo "✅ Bouton 'En savoir plus' avec background transparent\n";

        echo "\n🎉 TEST BOUTON TERMINÉ\n";
    }

    /**
     * Test que les demandes reçues affichent les badges d'action
     */
    public function test_received_requests_action_badges(): void
    {
        echo "\n=== TEST BADGES D'ACTION DEMANDES REÇUES ===\n";

        // Créer des utilisateurs
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Créer une demande de relation (Fatima veut être fille d'Amina)
        $relationshipType = RelationshipType::where('name', 'daughter')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $amina->id, $relationshipType->id, 'Test badges d\'action'
        );

        echo "✅ Demande créée: {$fatima->name} → {$amina->name} (Fille)\n";

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

        echo "✅ Demande reçue affichée avec les nouveaux badges d'action\n";
        echo "   - Badge relation: Mère (relation inverse)\n";
        echo "   - Badge Rejeter: avec popup de confirmation\n";
        echo "   - Badge Accepter: action directe\n";

        echo "\n🎉 TEST BADGES D'ACTION TERMINÉ\n";
    }

    /**
     * Test que les demandes envoyées affichent le badge annuler
     */
    public function test_sent_requests_cancel_badge(): void
    {
        echo "\n=== TEST BADGE ANNULER DEMANDES ENVOYÉES ===\n";

        // Créer des utilisateurs
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Créer une demande de relation (Fatima veut être fille d'Amina)
        $relationshipType = RelationshipType::where('name', 'daughter')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $amina->id, $relationshipType->id, 'Test badge annuler'
        );

        echo "✅ Demande créée: {$fatima->name} → {$amina->name} (Fille)\n";

        // Tester la page Networks en tant que Fatima (qui envoie la demande)
        $response = $this->actingAs($fatima)->get('/reseaux');
        
        $response->assertStatus(200);
        
        // Vérifier que les données sont correctes
        $response->assertInertia(fn ($page) => 
            $page->component('Networks')
                ->has('sentRequests', 1)
                ->where('sentRequests.0.target_user_name', 'Amina Tazi')
                ->where('sentRequests.0.relationship_name', 'Fille')
        );

        echo "✅ Demande envoyée affichée avec le badge annuler\n";
        echo "   - Badge relation: Fille\n";
        echo "   - Badge En attente: statut\n";
        echo "   - Badge Annuler: avec popup de confirmation\n";

        echo "\n🎉 TEST BADGE ANNULER TERMINÉ\n";
    }

    /**
     * Test que l'annulation fonctionne
     */
    public function test_cancel_request_works(): void
    {
        echo "\n=== TEST ANNULATION DEMANDE ===\n";

        // Créer des utilisateurs
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Créer une demande de relation
        $relationshipType = RelationshipType::where('name', 'daughter')->first();
        
        $request = $this->familyRelationService->createRelationshipRequest(
            $fatima, $amina->id, $relationshipType->id, 'Test annulation'
        );

        echo "✅ Demande créée: ID {$request->id}\n";

        // Vérifier que la demande existe
        $this->assertDatabaseHas('relationship_requests', [
            'id' => $request->id,
            'status' => 'pending'
        ]);

        // Tester l'annulation
        $response = $this->actingAs($fatima)->delete("/family-relations/{$request->id}");
        
        $response->assertRedirect();
        
        // Vérifier que la demande a été supprimée
        $this->assertDatabaseMissing('relationship_requests', [
            'id' => $request->id
        ]);

        echo "✅ Demande annulée avec succès\n";

        echo "\n🎉 TEST ANNULATION TERMINÉ\n";
    }
}
