<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\Suggestion;
use App\Models\RelationshipRequest;
use App\Services\SuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionRequestTest extends TestCase
{
    use RefreshDatabase;

    protected SuggestionService $suggestionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->suggestionService = app(SuggestionService::class);
        
        // Exécuter les seeders nécessaires
        $this->seed(\Database\Seeders\ComprehensiveRelationshipTypesSeeder::class);
    }

    /**
     * Test que les demandes créées via suggestions apparaissent bien chez le destinataire
     */
    public function test_suggestion_request_appears_in_pending_requests(): void
    {
        // Créer les utilisateurs
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);

        echo "\n=== TEST DEMANDES VIA SUGGESTIONS ===\n";

        // 1. Créer une suggestion
        echo "1. Création d'une suggestion...\n";
        $suggestion = $this->suggestionService->createSuggestion(
            $ahmed,
            $fatima->id,
            'family',
            'Suggestion de relation familiale'
        );

        $this->assertNotNull($suggestion, 'La suggestion doit être créée');
        echo "✅ Suggestion créée avec ID: {$suggestion->id}\n";

        // 2. Envoyer une demande de relation via la suggestion
        echo "2. Envoi d'une demande de relation via suggestion...\n";
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $this->assertNotNull($husbandType, 'Type husband doit exister');

        $this->suggestionService->sendRelationRequestFromSuggestion($suggestion, 'husband');
        echo "✅ Demande envoyée via suggestion\n";

        // 3. Vérifier que la demande apparaît dans les demandes en attente de Fatima
        echo "3. Vérification des demandes en attente...\n";
        $pendingRequests = RelationshipRequest::where('target_user_id', $fatima->id)
            ->where('status', 'pending')
            ->with(['requester.profile', 'relationshipType'])
            ->get();

        echo "Nombre de demandes en attente pour Fatima: " . $pendingRequests->count() . "\n";

        $this->assertGreaterThan(0, $pendingRequests->count(), 'Fatima devrait avoir au moins une demande en attente');

        $request = $pendingRequests->first();
        $this->assertEquals($ahmed->id, $request->requester_id, 'La demande devrait venir d\'Ahmed');
        $this->assertEquals($fatima->id, $request->target_user_id, 'La demande devrait être pour Fatima');
        $this->assertEquals($husbandType->id, $request->relationship_type_id, 'La demande devrait être de type husband');

        echo "✅ Demande trouvée:\n";
        echo "   - De: {$request->requester->name}\n";
        echo "   - Vers: {$request->targetUser->name}\n";
        echo "   - Type: {$request->relationshipType->display_name_fr}\n";
        echo "   - Statut: {$request->status}\n";

        // 4. Vérifier que la suggestion est marquée comme acceptée
        $suggestion->refresh();
        $this->assertEquals('accepted', $suggestion->status, 'La suggestion devrait être marquée comme acceptée');
        echo "✅ Suggestion marquée comme acceptée\n";

        // 5. Vérifier via le service FamilyRelationService
        $familyRelationService = app(\App\Services\FamilyRelationService::class);
        $fatimasPendingRequests = $familyRelationService->getPendingRequests($fatima);

        echo "Demandes via FamilyRelationService: " . $fatimasPendingRequests->count() . "\n";
        $this->assertGreaterThan(0, $fatimasPendingRequests->count(), 'Le service devrait retourner les demandes en attente');

        echo "\n🎉 TEST RÉUSSI ! Les demandes via suggestions apparaissent correctement.\n";
    }

    /**
     * Test que les erreurs de type null sont corrigées
     */
    public function test_no_null_type_errors(): void
    {
        // Créer les utilisateurs
        $user1 = User::factory()->withProfile('male')->create(['name' => 'User 1']);
        $user2 = User::factory()->withProfile('female')->create(['name' => 'User 2']);

        // Créer une suggestion
        $suggestion = $this->suggestionService->createSuggestion(
            $user1,
            $user2->id,
            'family',
            '' // Message vide - ne devrait pas causer d'erreur
        );

        $this->assertNotNull($suggestion);

        // Envoyer une demande - ne devrait pas causer d'erreur de type null
        $this->expectNotToPerformAssertions();
        $this->suggestionService->sendRelationRequestFromSuggestion($suggestion, 'brother');

        echo "\n✅ Aucune erreur de type null détectée\n";
    }
}
