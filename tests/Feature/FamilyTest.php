<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer les types de relations de base
        RelationshipType::factory()->create(['name' => 'father', 'display_name_fr' => 'Père']);
        RelationshipType::factory()->create(['name' => 'mother', 'display_name_fr' => 'Mère']);
        RelationshipType::factory()->create(['name' => 'son', 'display_name_fr' => 'Fils']);
        RelationshipType::factory()->create(['name' => 'daughter', 'display_name_fr' => 'Fille']);
        RelationshipType::factory()->create(['name' => 'brother', 'display_name_fr' => 'Frère']);
        RelationshipType::factory()->create(['name' => 'sister', 'display_name_fr' => 'Sœur']);
        RelationshipType::factory()->create(['name' => 'cousin', 'display_name_fr' => 'Cousin']);
        RelationshipType::factory()->create(['name' => 'uncle', 'display_name_fr' => 'Oncle']);
        RelationshipType::factory()->create(['name' => 'nephew', 'display_name_fr' => 'Neveu']);
        RelationshipType::factory()->create(['name' => 'father_in_law', 'display_name_fr' => 'Beau-père']);
        RelationshipType::factory()->create(['name' => 'brother_in_law', 'display_name_fr' => 'Beau-frère']);
    }

    /**
     * Test d'accès à la page famille pour un utilisateur authentifié
     */
    public function test_authenticated_user_can_access_family_page(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Family'));
    }

    /**
     * Test d'affichage des membres de famille
     */
    public function test_family_page_displays_family_members(): void
    {
        $user = User::factory()->create();
        $father = User::factory()->create(['name' => 'Papa Test']);
        $brother = User::factory()->create(['name' => 'Frère Test']);
        
        // Créer des relations
        FamilyRelationship::factory()->create([
            'user_id' => $user->id,
            'related_user_id' => $father->id,
            'relationship_type_id' => RelationshipType::where('name', 'father')->first()->id,
            'status' => 'accepted',
        ]);
        
        FamilyRelationship::factory()->create([
            'user_id' => $user->id,
            'related_user_id' => $brother->id,
            'relationship_type_id' => RelationshipType::where('name', 'brother')->first()->id,
            'status' => 'accepted',
        ]);
        
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('members', 2)
                 ->where('members.0.name', 'Papa Test')
                 ->where('members.1.name', 'Frère Test')
        );
    }

    /**
     * Test de catégorisation des relations familiales
     */
    public function test_family_members_are_categorized_correctly(): void
    {
        $user = User::factory()->create();
        
        // Famille immédiate
        $father = User::factory()->create(['name' => 'Père']);
        $mother = User::factory()->create(['name' => 'Mère']);
        
        // Frères et sœurs
        $brother = User::factory()->create(['name' => 'Frère']);
        
        // Famille élargie
        $cousin = User::factory()->create(['name' => 'Cousin']);
        $uncle = User::factory()->create(['name' => 'Oncle']);
        
        // Belle-famille
        $fatherInLaw = User::factory()->create(['name' => 'Beau-père']);
        $brotherInLaw = User::factory()->create(['name' => 'Beau-frère']);
        
        // Créer les relations
        $relations = [
            [$father->id, 'father'],
            [$mother->id, 'mother'],
            [$brother->id, 'brother'],
            [$cousin->id, 'cousin'],
            [$uncle->id, 'uncle'],
            [$fatherInLaw->id, 'father_in_law'],
            [$brotherInLaw->id, 'brother_in_law'],
        ];
        
        foreach ($relations as [$relatedUserId, $relationshipName]) {
            FamilyRelationship::factory()->create([
                'user_id' => $user->id,
                'related_user_id' => $relatedUserId,
                'relationship_type_id' => RelationshipType::where('name', $relationshipName)->first()->id,
                'status' => 'accepted',
            ]);
        }
        
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('members', 7));
        
        // Vérifier que les relations sont correctement catégorisées
        $members = $response->getOriginalContent()->getData()['page']['props']['members'];
        
        $immediateFamily = collect($members)->filter(fn($member) => 
            in_array($member['relation_code'], ['father', 'mother'])
        );
        $this->assertCount(2, $immediateFamily);
        
        $siblings = collect($members)->filter(fn($member) => 
            in_array($member['relation_code'], ['brother'])
        );
        $this->assertCount(1, $siblings);
        
        $extended = collect($members)->filter(fn($member) => 
            in_array($member['relation_code'], ['cousin', 'uncle'])
        );
        $this->assertCount(2, $extended);
        
        $inLaws = collect($members)->filter(fn($member) => 
            str_contains($member['relation_code'], '_in_law')
        );
        $this->assertCount(2, $inLaws);
    }

    /**
     * Test des relations bidirectionnelles
     */
    public function test_bidirectional_relationships_are_handled(): void
    {
        $user = User::factory()->create();
        $father = User::factory()->create(['name' => 'Papa']);
        
        // Créer une relation père -> fils
        FamilyRelationship::factory()->create([
            'user_id' => $father->id,
            'related_user_id' => $user->id,
            'relationship_type_id' => RelationshipType::where('name', 'son')->first()->id,
            'status' => 'accepted',
        ]);
        
        // L'utilisateur devrait voir son père dans sa liste
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('members', 1)
                 ->where('members.0.name', 'Papa')
                 ->where('members.0.relation_code', 'father') // Relation inverse
        );
    }

    /**
     * Test d'affichage vide pour un utilisateur sans famille
     */
    public function test_empty_family_page_for_user_without_relations(): void
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('members', 0)
        );
    }

    /**
     * Test que seules les relations acceptées sont affichées
     */
    public function test_only_accepted_relationships_are_displayed(): void
    {
        $user = User::factory()->create();
        $father = User::factory()->create(['name' => 'Papa']);
        $brother = User::factory()->create(['name' => 'Frère']);
        
        // Relation acceptée
        FamilyRelationship::factory()->create([
            'user_id' => $user->id,
            'related_user_id' => $father->id,
            'relationship_type_id' => RelationshipType::where('name', 'father')->first()->id,
            'status' => 'accepted',
        ]);
        
        // Relation en attente
        FamilyRelationship::factory()->create([
            'user_id' => $user->id,
            'related_user_id' => $brother->id,
            'relationship_type_id' => RelationshipType::where('name', 'brother')->first()->id,
            'status' => 'pending',
        ]);
        
        $response = $this->actingAs($user)->get('/famille');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => 
            $page->has('members', 1) // Seule la relation acceptée
                 ->where('members.0.name', 'Papa')
        );
    }

    /**
     * Test de performance avec une grande famille
     */
    public function test_family_page_performance_with_large_family(): void
    {
        $user = User::factory()->create();
        
        // Créer 50 relations familiales
        $relationshipType = RelationshipType::where('name', 'cousin')->first();
        
        for ($i = 0; $i < 50; $i++) {
            $cousin = User::factory()->create(['name' => "Cousin $i"]);
            FamilyRelationship::factory()->create([
                'user_id' => $user->id,
                'related_user_id' => $cousin->id,
                'relationship_type_id' => $relationshipType->id,
                'status' => 'accepted',
            ]);
        }
        
        $startTime = microtime(true);
        
        $response = $this->actingAs($user)->get('/famille');
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->has('members', 50));
        
        // Vérifier que la page se charge en moins de 2 secondes
        $this->assertLessThan(2.0, $executionTime, 'La page famille prend trop de temps à charger avec une grande famille');
    }
}
