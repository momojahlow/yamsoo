<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\Profile;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class YamsooAnalysisTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRelationService = app(FamilyRelationService::class);
    }

    /** @test */
    public function it_can_analyze_self_relation()
    {
        $user = User::factory()->create();
        
        $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($user, $user);
        
        $this->assertFalse($analysis['has_relation']);
        $this->assertEquals('self', $analysis['relation_type']);
        $this->assertEquals('C\'est vous !', $analysis['relation_name']);
        $this->assertStringContainsString('🤳', $analysis['yamsoo_message']);
    }

    /** @test */
    public function it_can_analyze_direct_relation()
    {
        // Créer les utilisateurs
        $father = User::factory()->create();
        $son = User::factory()->create();

        // Créer les profils
        Profile::factory()->create(['user_id' => $father->id, 'gender' => 'male']);
        Profile::factory()->create(['user_id' => $son->id, 'gender' => 'male']);

        // Utiliser un type de relation existant ou en créer un avec un code unique
        $fatherType = RelationshipType::where('code', 'father')->first() ?:
            RelationshipType::factory()->create([
                'code' => 'father_test_' . uniqid(),
                'name_fr' => 'Père',
                'name_ar' => 'أب',
                'name_en' => 'Father',
                'gender' => 'male',
            ]);

        // Créer la relation
        FamilyRelationship::factory()->create([
            'user_id' => $father->id,
            'related_user_id' => $son->id,
            'relationship_type_id' => $fatherType->id,
            'status' => 'accepted',
        ]);

        $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($son, $father);

        $this->assertTrue($analysis['has_relation']);
        $this->assertEquals('direct', $analysis['relation_type']);
        $this->assertEquals(100, $analysis['confidence']);
        $this->assertStringContainsString('🎯', $analysis['yamsoo_message']);
    }

    /** @test */
    public function it_can_analyze_no_relation()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($user1, $user2);
        
        $this->assertFalse($analysis['has_relation']);
        $this->assertEquals('none', $analysis['relation_type']);
        $this->assertEquals('Aucune relation', $analysis['relation_name']);
        $this->assertEquals(0, $analysis['confidence']);
        $this->assertStringContainsString('❌', $analysis['yamsoo_message']);
        $this->assertArrayHasKey('suggestion', $analysis);
    }

    /** @test */
    public function it_can_analyze_indirect_relation()
    {
        // Créer trois utilisateurs : A, B, C
        // A est le père de B, B est le père de C
        // Donc A est le grand-père de C
        $grandfather = User::factory()->create();
        $father = User::factory()->create();
        $grandson = User::factory()->create();

        // Créer les profils
        Profile::factory()->create(['user_id' => $grandfather->id, 'gender' => 'male']);
        Profile::factory()->create(['user_id' => $father->id, 'gender' => 'male']);
        Profile::factory()->create(['user_id' => $grandson->id, 'gender' => 'male']);

        // Utiliser un type de relation existant ou en créer un avec un code unique
        $fatherType = RelationshipType::where('code', 'father')->first() ?:
            RelationshipType::factory()->create([
                'code' => 'father_test_indirect_' . uniqid(),
                'name_fr' => 'Père',
                'name_ar' => 'أب',
                'name_en' => 'Father',
                'gender' => 'male',
            ]);

        // Créer les relations
        // Grandfather -> Father
        FamilyRelationship::factory()->create([
            'user_id' => $grandfather->id,
            'related_user_id' => $father->id,
            'relationship_type_id' => $fatherType->id,
            'status' => 'accepted',
        ]);

        // Father -> Grandson
        FamilyRelationship::factory()->create([
            'user_id' => $father->id,
            'related_user_id' => $grandson->id,
            'relationship_type_id' => $fatherType->id,
            'status' => 'accepted',
        ]);

        $analysis = $this->familyRelationService->analyzeRelationshipBetweenUsers($grandfather, $grandson);

        $this->assertTrue($analysis['has_relation']);
        $this->assertEquals('indirect', $analysis['relation_type']);
        $this->assertGreaterThan(0, $analysis['confidence']);
        $this->assertStringContainsString('🔗', $analysis['yamsoo_message']);
        $this->assertArrayHasKey('intermediate_users', $analysis);
    }

    /** @test */
    public function yamsoo_api_endpoint_works()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $response = $this->actingAs($user1)->postJson('/yamsoo/analyze-relation', [
            'target_user_id' => $user2->id,
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'success',
                    'analysis' => [
                        'has_relation',
                        'relation_type',
                        'relation_name',
                        'relation_description',
                        'relation_path',
                        'confidence',
                        'yamsoo_message',
                    ],
                    'target_user' => [
                        'id',
                        'name',
                    ],
                    'current_user' => [
                        'id',
                        'name',
                    ],
                ]);
    }

    /** @test */
    public function yamsoo_api_requires_authentication()
    {
        $user = User::factory()->create();
        
        $response = $this->postJson('/yamsoo/analyze-relation', [
            'target_user_id' => $user->id,
        ]);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function yamsoo_api_validates_target_user_id()
    {
        $user = User::factory()->create();
        
        // Test sans target_user_id
        $response = $this->actingAs($user)->postJson('/yamsoo/analyze-relation', []);
        $response->assertStatus(422);
        
        // Test avec target_user_id invalide
        $response = $this->actingAs($user)->postJson('/yamsoo/analyze-relation', [
            'target_user_id' => 99999,
        ]);
        $response->assertStatus(422);
    }

    /** @test */
    public function relations_summary_endpoint_works()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson('/yamsoo/relations-summary');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'success',
                    'statistics',
                    'relationships',
                    'total_family_members',
                ]);
    }

    /** @test */
    public function multiple_analysis_endpoint_works()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();
        
        $response = $this->actingAs($user1)->postJson('/yamsoo/analyze-multiple', [
            'user_ids' => [$user2->id, $user3->id],
        ]);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'analyzed_count' => 2,
                ])
                ->assertJsonStructure([
                    'success',
                    'results' => [
                        '*' => [
                            'user_id',
                            'user_name',
                            'analysis',
                        ],
                    ],
                    'analyzed_count',
                ]);
    }

    /** @test */
    public function suggestions_endpoint_works()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson('/yamsoo/suggestions');
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'success',
                    'suggestions',
                    'total_suggestions',
                ]);
    }
}
