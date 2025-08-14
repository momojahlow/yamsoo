<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationshipInferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FamilyRelationshipInferenceTest extends TestCase
{
    use RefreshDatabase;

    private FamilyRelationshipInferenceService $inferenceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->inferenceService = new FamilyRelationshipInferenceService();
        
        // Créer quelques types de relations pour les tests
        $this->createRelationshipTypes();
    }

    private function createRelationshipTypes(): void
    {
        $types = [
            ['name' => 'father', 'display_name_fr' => 'Père', 'category' => 'direct', 'generation_level' => -1],
            ['name' => 'mother', 'display_name_fr' => 'Mère', 'category' => 'direct', 'generation_level' => -1],
            ['name' => 'son', 'display_name_fr' => 'Fils', 'category' => 'direct', 'generation_level' => 1],
            ['name' => 'daughter', 'display_name_fr' => 'Fille', 'category' => 'direct', 'generation_level' => 1],
            ['name' => 'wife', 'display_name_fr' => 'Épouse', 'category' => 'marriage', 'generation_level' => 0],
            ['name' => 'husband', 'display_name_fr' => 'Mari', 'category' => 'marriage', 'generation_level' => 0],
            ['name' => 'daughter_in_law', 'display_name_fr' => 'Belle-fille', 'category' => 'marriage', 'generation_level' => 1],
            ['name' => 'son_in_law', 'display_name_fr' => 'Gendre', 'category' => 'marriage', 'generation_level' => 1],
            ['name' => 'brother', 'display_name_fr' => 'Frère', 'category' => 'direct', 'generation_level' => 0],
            ['name' => 'sister', 'display_name_fr' => 'Sœur', 'category' => 'direct', 'generation_level' => 0],
        ];

        foreach ($types as $type) {
            RelationshipType::create(array_merge($type, [
                'display_name_ar' => $type['display_name_fr'],
                'display_name_en' => $type['display_name_fr'],
                'description' => 'Test relation',
                'reverse_relationship' => 'test',
                'sort_order' => 1
            ]));
        }
    }

    /** @test */
    public function it_correctly_infers_daughter_in_law_relationship()
    {
        // Arrange: Ahmed (father) -> Mohamed (son) -> Leila (wife)
        // Expected: Leila should be Ahmed's daughter-in-law
        
        $ahmed = User::factory()->create(['name' => 'Ahmed Benali']);
        $mohamed = User::factory()->create(['name' => 'Mohamed']);
        $leila = User::factory()->create(['name' => 'Leila Mansouri']);
        
        // Ahmed is Mohamed's father, Leila is Mohamed's wife
        $result = $this->inferenceService->inferRelationship(
            $ahmed,
            $leila,
            $mohamed,
            'father',  // Ahmed -> Mohamed
            'wife'     // Mohamed -> Leila
        );

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('daughter_in_law', $result['code']);
        $this->assertStringContainsString('belle-fille', $result['reason']);
        $this->assertEquals(90, $result['confidence']);
    }

    /** @test */
    public function it_correctly_infers_sibling_relationship()
    {
        // Arrange: Parent -> Child1, Parent -> Child2
        // Expected: Child1 and Child2 should be siblings
        
        $parent = User::factory()->create(['name' => 'Parent']);
        $child1 = User::factory()->create(['name' => 'Child 1']);
        $child2 = User::factory()->create(['name' => 'Child 2']);
        
        $result = $this->inferenceService->inferRelationship(
            $child1,
            $child2,
            $parent,
            'son',      // Child1 -> Parent
            'daughter'  // Parent -> Child2
        );

        // Assert
        $this->assertNotNull($result);
        $this->assertContains($result['code'], ['brother', 'sister']);
    }

    /** @test */
    public function it_returns_null_for_unknown_relationship_types()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $connector = User::factory()->create();
        
        $result = $this->inferenceService->inferRelationship(
            $user1,
            $user2,
            $connector,
            'unknown_relation',
            'another_unknown_relation'
        );

        $this->assertNull($result);
    }

    /** @test */
    public function it_handles_cousin_relationships()
    {
        // Arrange: Two people connected through different parents (siblings)
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $connector = User::factory()->create();
        
        $result = $this->inferenceService->inferRelationship(
            $user1,
            $user2,
            $connector,
            'son',    // User1 is son of connector
            'son'     // User2 is also son of connector (same generation, different parents)
        );

        // Should be siblings, not cousins in this case
        $this->assertNotNull($result);
        $this->assertEquals('brother', $result['code']);
    }
}
