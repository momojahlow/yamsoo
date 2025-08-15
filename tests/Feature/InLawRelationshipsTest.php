<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InLawRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->familyRelationService = app(FamilyRelationService::class);
        
        // Créer les types de relations nécessaires
        $this->createRelationshipTypes();
    }

    private function createRelationshipTypes(): void
    {
        $relationshipTypes = [
            ['name' => 'husband', 'display_name_fr' => 'Mari', 'display_name_ar' => 'زوج', 'display_name_en' => 'Husband', 'category' => 'spouse'],
            ['name' => 'wife', 'display_name_fr' => 'Épouse', 'display_name_ar' => 'زوجة', 'display_name_en' => 'Wife', 'category' => 'spouse'],
            ['name' => 'brother', 'display_name_fr' => 'Frère', 'display_name_ar' => 'أخ', 'display_name_en' => 'Brother', 'category' => 'sibling'],
            ['name' => 'sister', 'display_name_fr' => 'Sœur', 'display_name_ar' => 'أخت', 'display_name_en' => 'Sister', 'category' => 'sibling'],
            ['name' => 'brother_in_law', 'display_name_fr' => 'Beau-frère', 'display_name_ar' => 'صهر', 'display_name_en' => 'Brother-in-law', 'category' => 'in_law'],
            ['name' => 'sister_in_law', 'display_name_fr' => 'Belle-sœur', 'display_name_ar' => 'كنة', 'display_name_en' => 'Sister-in-law', 'category' => 'in_law'],
        ];

        foreach ($relationshipTypes as $type) {
            RelationshipType::create($type);
        }
    }

    /**
     * Test complet du scénario belle-famille
     */
    public function test_complete_in_law_relationships_scenario(): void
    {
        // Créer les utilisateurs
        $ahmed = User::factory()->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $youssef = User::factory()->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);
        $leila = User::factory()->create(['name' => 'Leila Mansouri', 'email' => 'leila@test.com']);

        $this->info("=== ÉTAPE 1: Ahmed → Fatima (Mari) ===");
        
        // 1. Ahmed crée une demande vers Fatima avec relation "Mari"
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed,
            $fatima->id,
            $husbandType->id,
            'Demande de mariage'
        );
        
        $this->assertDatabaseHas('relationship_requests', [
            'id' => $request1->id,
            'requester_id' => $ahmed->id,
            'target_user_id' => $fatima->id,
            'status' => 'pending'
        ]);

        // Fatima accepte
        $this->familyRelationService->acceptRelationshipRequest($request1);
        
        // Vérifier les relations créées
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $ahmed->id,
            'related_user_id' => $fatima->id,
            'relationship_type_id' => $husbandType->id,
            'status' => 'accepted'
        ]);

        $wifeType = RelationshipType::where('name', 'wife')->first();
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $fatima->id,
            'related_user_id' => $ahmed->id,
            'relationship_type_id' => $wifeType->id,
            'status' => 'accepted'
        ]);

        $this->info("✅ Ahmed et Fatima sont maintenant mariés");

        $this->info("=== ÉTAPE 2: Fatima → Youssef (Frère) ===");

        // 2. Fatima crée une demande vers Youssef avec relation "Frère"
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima,
            $youssef->id,
            $brotherType->id,
            'Tu es mon frère'
        );

        // Youssef accepte
        $this->familyRelationService->acceptRelationshipRequest($request2);

        // Vérifier les relations créées
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $fatima->id,
            'related_user_id' => $youssef->id,
            'relationship_type_id' => $brotherType->id,
            'status' => 'accepted'
        ]);

        $sisterType = RelationshipType::where('name', 'sister')->first();
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $youssef->id,
            'related_user_id' => $fatima->id,
            'relationship_type_id' => $sisterType->id,
            'status' => 'accepted'
        ]);

        $this->info("✅ Fatima et Youssef sont maintenant frère et sœur");

        $this->info("=== ÉTAPE 3: Youssef → Ahmed (Beau-frère) ===");

        // 3. Youssef crée une demande vers Ahmed avec relation "Beau-frère"
        $brotherInLawType = RelationshipType::where('name', 'brother_in_law')->first();
        $request3 = $this->familyRelationService->createRelationshipRequest(
            $youssef,
            $ahmed->id,
            $brotherInLawType->id,
            'Tu es mon beau-frère'
        );

        // Ahmed accepte
        $this->familyRelationService->acceptRelationshipRequest($request3);

        // Vérifier la relation créée (principale)
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $youssef->id,
            'related_user_id' => $ahmed->id,
            'relationship_type_id' => $brotherInLawType->id,
            'status' => 'accepted'
        ]);

        // Note: La relation inverse devrait être créée automatiquement
        // mais nous allons vérifier cela dans la vérification finale

        $this->info("✅ Ahmed et Youssef sont maintenant beaux-frères");

        $this->info("=== ÉTAPE 4: Ahmed → Leila (Sœur) ===");

        // 4. Ahmed crée une demande vers Leila avec relation "Sœur"
        $request4 = $this->familyRelationService->createRelationshipRequest(
            $ahmed,
            $leila->id,
            $sisterType->id,
            'Tu es ma sœur'
        );

        // Leila accepte
        $this->familyRelationService->acceptRelationshipRequest($request4);

        // Vérifier les relations créées
        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $ahmed->id,
            'related_user_id' => $leila->id,
            'relationship_type_id' => $sisterType->id,
            'status' => 'accepted'
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'user_id' => $leila->id,
            'related_user_id' => $ahmed->id,
            'relationship_type_id' => $brotherType->id,
            'status' => 'accepted'
        ]);

        $this->info("✅ Ahmed et Leila sont maintenant frère et sœur");

        $this->info("=== ÉTAPE 5: Leila → Youssef (Beau-frère) ===");

        // 5a. Leila crée une demande vers Youssef avec relation "Beau-frère"
        $request5a = $this->familyRelationService->createRelationshipRequest(
            $leila,
            $youssef->id,
            $brotherInLawType->id,
            'Tu es mon beau-frère'
        );

        // Youssef accepte
        $this->familyRelationService->acceptRelationshipRequest($request5a);

        $this->info("=== ÉTAPE 6: Leila → Fatima (Belle-sœur) ===");

        // 5b. Leila crée une demande vers Fatima avec relation "Belle-sœur"
        $sisterInLawType = RelationshipType::where('name', 'sister_in_law')->first();
        $request5b = $this->familyRelationService->createRelationshipRequest(
            $leila,
            $fatima->id,
            $sisterInLawType->id,
            'Tu es ma belle-sœur'
        );

        // Fatima accepte
        $this->familyRelationService->acceptRelationshipRequest($request5b);

        $this->info("=== VÉRIFICATION FINALE ===");

        // Vérifier toutes les relations finales
        $this->verifyFinalRelationships($ahmed, $fatima, $youssef, $leila);
    }

    private function verifyFinalRelationships($ahmed, $fatima, $youssef, $leila): void
    {
        // Récupérer toutes les relations d'Ahmed
        $ahmedRelations = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $this->info("Relations d'Ahmed :");
        foreach ($ahmedRelations as $relation) {
            $this->info("  - {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}");
        }

        // Vérifications spécifiques
        $this->assertTrue(
            $ahmedRelations->contains(function ($relation) use ($fatima) {
                return $relation->related_user_id === $fatima->id && 
                       $relation->relationshipType->name === 'wife';
            }),
            "Ahmed devrait avoir Fatima comme épouse"
        );

        $this->assertTrue(
            $ahmedRelations->contains(function ($relation) use ($youssef) {
                return $relation->related_user_id === $youssef->id && 
                       $relation->relationshipType->name === 'brother_in_law';
            }),
            "Ahmed devrait avoir Youssef comme beau-frère"
        );

        $this->assertTrue(
            $ahmedRelations->contains(function ($relation) use ($leila) {
                return $relation->related_user_id === $leila->id && 
                       $relation->relationshipType->name === 'sister';
            }),
            "Ahmed devrait avoir Leila comme sœur"
        );

        // Vérifier que Ahmed voit bien ses relations dans la page famille
        $response = $this->actingAs($ahmed)->get('/famille');
        $response->assertStatus(200);
        
        $members = $response->getOriginalContent()->getData()['page']['props']['members'];
        $this->info("Membres visibles par Ahmed dans la page famille :");
        foreach ($members as $member) {
            $this->info("  - {$member['name']} : {$member['relation']} (code: {$member['relation_code']})");
        }

        // Vérifier que les beaux-frères/belles-sœurs sont bien présents
        $inLawMembers = collect($members)->filter(function ($member) {
            return str_contains($member['relation_code'], '_in_law');
        });

        $this->assertGreaterThan(0, $inLawMembers->count(), 
            "Ahmed devrait voir au moins une relation de belle-famille");
    }

    private function info(string $message): void
    {
        echo "\n" . $message;
    }
}
