<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComplexFamilyScenarioTest extends TestCase
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
     * Test du scénario complexe complet avec Ahmed, Fatima, Mohammed, Amina et Youssef
     */
    public function test_complex_family_scenario_with_genders(): void
    {
        echo "\n=== TEST SCÉNARIO COMPLEXE FAMILIAL ===\n";

        // Créer les utilisateurs avec genres spécifiés via factory
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Alami', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $mohammed = User::factory()->withProfile('male')->create(['name' => 'Mohammed Bennani', 'email' => 'mohammed@test.com']);
        $amina = User::factory()->withProfile('female')->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);

        echo "✅ Utilisateurs créés avec genres:\n";
        echo "   - Ahmed Alami (ahmed@test.com) - Homme\n";
        echo "   - Fatima Zahra (fatima@test.com) - Femme\n";
        echo "   - Mohammed Bennani (mohammed@test.com) - Homme\n";
        echo "   - Amina Tazi (amina@test.com) - Femme\n";
        echo "   - Youssef Bennani (youssef@test.com) - Homme\n\n";

        // PHASE 1: Ahmed crée ses demandes
        echo "📝 PHASE 1: AHMED CRÉE SES DEMANDES\n";
        
        // Ahmed → Fatima (Épouse)
        $wifeType = RelationshipType::where('name', 'wife')->first();
        $this->createAndVerifyRequest($ahmed, $fatima, $wifeType, 'Ahmed demande à Fatima d\'être son épouse');
        
        // Ahmed → Mohammed (Fils)
        $sonType = RelationshipType::where('name', 'son')->first();
        $this->createAndVerifyRequest($ahmed, $mohammed, $sonType, 'Ahmed demande à Mohammed d\'être son fils');
        
        // Ahmed → Amina (Fille)
        $daughterType = RelationshipType::where('name', 'daughter')->first();
        $this->createAndVerifyRequest($ahmed, $amina, $daughterType, 'Ahmed demande à Amina d\'être sa fille');

        echo "\n📝 PHASE 2: ACCEPTATIONS DES DEMANDES D'AHMED\n";
        
        // Accepter toutes les demandes d'Ahmed
        $this->acceptAllRequestsFrom($ahmed);
        
        // Vérifier les relations créées
        $this->verifyRelationExists($ahmed, $fatima, 'wife', 'husband');
        $this->verifyRelationExists($ahmed, $mohammed, 'son', 'father');
        $this->verifyRelationExists($ahmed, $amina, 'daughter', 'father');

        echo "\n📝 PHASE 3: FATIMA CRÉE SA DEMANDE\n";
        
        // Fatima → Youssef (Frère)
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $this->createAndVerifyRequest($fatima, $youssef, $brotherType, 'Fatima demande à Youssef d\'être son frère');

        echo "\n📝 PHASE 4: YOUSSEF ACCEPTE ET CRÉE SES DEMANDES\n";
        
        // Youssef accepte la demande de Fatima
        $this->acceptAllRequestsFrom($fatima);
        $this->verifyRelationExists($fatima, $youssef, 'brother', 'sister');

        // Youssef crée ses demandes
        $brotherInLawType = RelationshipType::where('name', 'brother_in_law')->first();
        $nephewType = RelationshipType::where('name', 'nephew')->first();
        $nieceType = RelationshipType::where('name', 'niece')->first();

        $this->createAndVerifyRequest($youssef, $ahmed, $brotherInLawType, 'Youssef demande à Ahmed d\'être son beau-frère');
        $this->createAndVerifyRequest($youssef, $mohammed, $nephewType, 'Youssef demande à Mohammed d\'être son neveu');
        $this->createAndVerifyRequest($youssef, $amina, $nieceType, 'Youssef demande à Amina d\'être sa nièce');

        echo "\n📝 PHASE 5: ACCEPTATIONS FINALES\n";
        
        // Accepter toutes les demandes de Youssef
        $this->acceptAllRequestsFrom($youssef);
        
        // Vérifications finales
        $this->verifyRelationExists($youssef, $ahmed, 'brother_in_law', 'brother_in_law');
        $this->verifyRelationExists($youssef, $mohammed, 'nephew', 'uncle');
        $this->verifyRelationExists($youssef, $amina, 'niece', 'uncle');

        echo "\n🎯 RÉSUMÉ FINAL DU RÉSEAU FAMILIAL:\n";
        $this->printFamilyNetwork();

        echo "\n🎉 TEST SCÉNARIO COMPLEXE TERMINÉ AVEC SUCCÈS\n";
    }

    private function createAndVerifyRequest(User $requester, User $target, RelationshipType $relationType, string $message): void
    {
        $response = $this->actingAs($requester)->post('/family-relations', [
            'email' => $target->email,
            'relationship_type_id' => $relationType->id,
            'message' => $message
        ]);

        $response->assertRedirect();
        
        $request = RelationshipRequest::where('requester_id', $requester->id)
            ->where('target_user_id', $target->id)
            ->where('relationship_type_id', $relationType->id)
            ->first();

        $this->assertNotNull($request, "Demande {$requester->name} → {$target->name} ({$relationType->display_name_fr}) doit être créée");
        
        echo "   ✅ {$requester->name} → {$target->name} ({$relationType->display_name_fr})\n";
    }

    private function acceptAllRequestsFrom(User $requester): void
    {
        $requests = RelationshipRequest::where('requester_id', $requester->id)
            ->where('status', 'pending')
            ->get();

        foreach ($requests as $request) {
            $response = $this->actingAs($request->targetUser)->post("/family-relations/{$request->id}/accept");
            $response->assertRedirect();
            echo "   ✅ {$request->targetUser->name} accepte la demande de {$requester->name}\n";
        }
    }

    private function verifyRelationExists(User $user1, User $user2, string $relation1, string $relation2): void
    {
        $relation1Type = RelationshipType::where('name', $relation1)->first();
        $relation2Type = RelationshipType::where('name', $relation2)->first();

        // Vérifier relation user1 → user2
        $familyRelation1 = FamilyRelationship::where('user_id', $user1->id)
            ->where('related_user_id', $user2->id)
            ->where('relationship_type_id', $relation1Type->id)
            ->first();

        // Vérifier relation user2 → user1
        $familyRelation2 = FamilyRelationship::where('user_id', $user2->id)
            ->where('related_user_id', $user1->id)
            ->where('relationship_type_id', $relation2Type->id)
            ->first();

        // Debug si relation manquante
        if (!$familyRelation1) {
            echo "   ❌ DEBUG: Relation manquante {$user1->name} → {$user2->name} ({$relation1})\n";
            $allRelations1 = FamilyRelationship::where('user_id', $user1->id)->where('related_user_id', $user2->id)->get();
            foreach ($allRelations1 as $rel) {
                $type = RelationshipType::find($rel->relationship_type_id);
                echo "      Trouvé: {$user1->name} → {$user2->name} ({$type->name})\n";
            }
        }

        if (!$familyRelation2) {
            echo "   ❌ DEBUG: Relation manquante {$user2->name} → {$user1->name} ({$relation2})\n";
            $allRelations2 = FamilyRelationship::where('user_id', $user2->id)->where('related_user_id', $user1->id)->get();
            foreach ($allRelations2 as $rel) {
                $type = RelationshipType::find($rel->relationship_type_id);
                echo "      Trouvé: {$user2->name} → {$user1->name} ({$type->name})\n";
            }
        }

        $this->assertNotNull($familyRelation1, "Relation {$user1->name} → {$user2->name} ({$relation1}) doit exister");
        $this->assertNotNull($familyRelation2, "Relation {$user2->name} → {$user1->name} ({$relation2}) doit exister");

        echo "   ✅ Relations réciproques: {$user1->name} ↔ {$user2->name} ({$relation1Type->display_name_fr}/{$relation2Type->display_name_fr})\n";
    }

    private function printFamilyNetwork(): void
    {
        $relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        
        echo "   📊 RÉSEAU FAMILIAL COMPLET:\n";
        foreach ($relations as $relation) {
            echo "      • {$relation->user->name} → {$relation->relatedUser->name} ({$relation->relationshipType->display_name_fr})\n";
        }
    }
}
