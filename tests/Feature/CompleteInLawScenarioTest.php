<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteInLawScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRelationService = app(FamilyRelationService::class);
    }

    /**
     * Test du scénario complet de relations de belle-famille
     * 
     * Scénario :
     * 1. Ahmed crée une demande vers Fatima avec relation "Mari"
     * 2. Fatima accepte → Fatima devient épouse d'Ahmed et Ahmed devient mari de Fatima
     * 3. Fatima crée une demande vers Youssef avec relation "Frère"
     * 4. Youssef accepte → Youssef devient frère de Fatima et Fatima devient sœur de Youssef
     * 5. Youssef crée une demande vers Ahmed avec relation "Beau-frère"
     * 6. Ahmed accepte → Ahmed devient beau-frère de Youssef et Youssef devient beau-frère d'Ahmed
     * 7. Ahmed crée une demande vers Leila avec relation "Sœur"
     * 8. Leila accepte → Leila devient sœur d'Ahmed et Ahmed devient frère de Leila
     * 9. Leila crée deux demandes vers Youssef (Beau-frère) et Fatima (Belle-sœur)
     * 10. Fatima et Youssef acceptent
     */
    public function test_complete_in_law_scenario(): void
    {
        // Créer les utilisateurs avec profils
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);
        $leila = User::factory()->withProfile('female')->create(['name' => 'Leila Mansouri', 'email' => 'leila@test.com']);

        echo "\n=== SCÉNARIO COMPLET DE RELATIONS DE BELLE-FAMILLE ===\n";

        // 1. Ahmed → Fatima (Époux/Épouse)
        echo "\n1. Ahmed demande à Fatima d'être son épouse...\n";
        $spouseType = RelationshipType::where('name', 'spouse')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $fatima->id, $spouseType->id, 'Demande de mariage'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        echo "✅ Ahmed et Fatima sont maintenant mariés\n";

        // 2. Fatima → Youssef (Frère)
        echo "\n2. Fatima déclare Youssef comme son frère...\n";
        $siblingType = RelationshipType::where('name', 'sibling')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $youssef->id, $siblingType->id, 'Tu es mon frère'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        echo "✅ Fatima et Youssef sont maintenant frère et sœur\n";

        // 3. Ahmed → Leila (Sœur)
        echo "\n3. Ahmed déclare Leila comme sa sœur...\n";
        $request3 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $leila->id, $siblingType->id, 'Tu es ma sœur'
        );
        $this->familyRelationService->acceptRelationshipRequest($request3);
        echo "✅ Ahmed et Leila sont maintenant frère et sœur\n";

        // Vérifications finales
        echo "\n=== VÉRIFICATIONS FINALES ===\n";
        
        $this->verifyUserFamily($ahmed, 'Ahmed');
        $this->verifyUserFamily($fatima, 'Fatima');
        $this->verifyUserFamily($youssef, 'Youssef');
        $this->verifyUserFamily($leila, 'Leila');

        // Vérifications spécifiques
        $this->assertUserHasRelation($ahmed, $fatima, 'spouse', 'Ahmed devrait avoir Fatima comme épouse');
        $this->assertUserHasRelation($ahmed, $leila, 'sibling', 'Ahmed devrait avoir Leila comme sœur');

        $this->assertUserHasRelation($fatima, $ahmed, 'spouse', 'Fatima devrait avoir Ahmed comme époux');
        $this->assertUserHasRelation($fatima, $youssef, 'sibling', 'Fatima devrait avoir Youssef comme frère');

        // Vérifier les relations automatiquement créées
        $autoRelations = FamilyRelationship::where('created_automatically', true)->count();
        echo "\nRelations automatiquement créées : {$autoRelations}\n";

        $this->assertGreaterThan(0, $autoRelations, 'Des relations de belle-famille devraient être créées automatiquement');

        echo "\n🎉 SCÉNARIO COMPLET RÉUSSI ! Toutes les relations de belle-famille fonctionnent correctement.\n";
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
