<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenderRelationshipBugsTest extends TestCase
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
     * Test des problèmes de genre dans les relations familiales
     */
    public function test_gender_relationship_bugs(): void
    {
        echo "\n=== TEST DES PROBLÈMES DE GENRE ===\n";

        // Créer les utilisateurs avec des genres spécifiques (méthode manuelle)
        $ahmed = User::factory()->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $ahmed->profile()->create(['gender' => 'male', 'bio' => 'Test', 'language' => 'fr']);

        $leila = User::factory()->create(['name' => 'Leila Mansouri', 'email' => 'leila@test.com']);
        $leila->profile()->create(['gender' => 'female', 'bio' => 'Test', 'language' => 'fr']);

        $youssef = User::factory()->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);
        $youssef->profile()->create(['gender' => 'male', 'bio' => 'Test', 'language' => 'fr']);

        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);
        $amina->profile()->create(['gender' => 'female', 'bio' => 'Test', 'language' => 'fr']);

        $mohammed = User::factory()->create(['name' => 'Mohammed Alami', 'email' => 'mohammed@test.com']);
        $mohammed->profile()->create(['gender' => 'male', 'bio' => 'Test', 'language' => 'fr']);

        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);
        $karim->profile()->create(['gender' => 'male', 'bio' => 'Test', 'language' => 'fr']);

        echo "✅ Utilisateurs créés avec genres spécifiés\n";

        // Test 1: Leila comme sœur d'Ahmed
        echo "\n--- Test 1: Leila (F) comme sœur d'Ahmed (M) ---\n";
        $this->createAndTestRelation($ahmed, $leila, 'sister', 'Ahmed → Leila: sœur');
        $this->verifyReciprocalRelation($leila, $ahmed, 'brother', 'Leila → Ahmed: frère');

        // Test 2: Youssef voit Amina comme nièce (pas neveu)
        echo "\n--- Test 2: Youssef (M) → Amina (F) comme nièce ---\n";
        $this->createAndTestRelation($youssef, $amina, 'niece', 'Youssef → Amina: nièce');
        $this->verifyReciprocalRelation($amina, $youssef, 'uncle', 'Amina → Youssef: oncle');

        // Test 3: Mohammed voit Leila comme tante (pas oncle)
        echo "\n--- Test 3: Mohammed (M) → Leila (F) comme tante ---\n";
        $this->createAndTestRelation($mohammed, $leila, 'aunt', 'Mohammed → Leila: tante');
        $this->verifyReciprocalRelation($leila, $mohammed, 'nephew', 'Leila → Mohammed: neveu');

        // Test 4: Karim comme grand-père d'Amina (pas grand-mère)
        echo "\n--- Test 4: Karim (M) → Amina (F) comme grand-père ---\n";
        $this->createAndTestRelation($karim, $amina, 'grandfather', 'Karim → Amina: grand-père');
        $this->verifyReciprocalRelation($amina, $karim, 'granddaughter', 'Amina → Karim: petite-fille');

        // Test 5: Relations belle-fille
        echo "\n--- Test 5: Relations belle-fille ---\n";
        $this->testDaughterInLawRelations($ahmed, $amina, $karim);

        echo "\n🎉 TOUS LES TESTS DE GENRE TERMINÉS\n";
    }

    /**
     * Créer et tester une relation
     */
    private function createAndTestRelation(User $requester, User $target, string $relationName, string $description): void
    {
        $relationType = RelationshipType::where('name', $relationName)->first();
        $this->assertNotNull($relationType, "Type de relation '{$relationName}' non trouvé");

        $request = $this->familyRelationService->createRelationshipRequest(
            $requester, $target->id, $relationType->id, "Test relation {$relationName}"
        );

        $relation = $this->familyRelationService->acceptRelationshipRequest($request);
        
        echo "✅ {$description} - Relation créée\n";
        
        // Vérifier que la relation a le bon type
        $this->assertEquals($relationType->id, $relation->relationship_type_id);
        echo "✅ Type de relation correct: {$relationType->display_name_fr}\n";
    }

    /**
     * Vérifier la relation réciproque
     */
    private function verifyReciprocalRelation(User $user, User $relatedUser, string $expectedRelationName, string $description): void
    {
        $reciprocalRelation = FamilyRelationship::where('user_id', $user->id)
            ->where('related_user_id', $relatedUser->id)
            ->where('status', 'accepted')
            ->with('relationshipType')
            ->first();

        $this->assertNotNull($reciprocalRelation, "Relation réciproque manquante: {$description}");

        $expectedType = RelationshipType::where('name', $expectedRelationName)->first();
        $this->assertNotNull($expectedType, "Type de relation attendu '{$expectedRelationName}' non trouvé");

        if ($reciprocalRelation->relationship_type_id === $expectedType->id) {
            echo "✅ {$description} - Relation réciproque correcte: {$expectedType->display_name_fr}\n";
        } else {
            $actualType = $reciprocalRelation->relationshipType;
            echo "❌ {$description} - ERREUR: Attendu '{$expectedType->display_name_fr}' mais trouvé '{$actualType->display_name_fr}'\n";
            $this->fail("Relation réciproque incorrecte pour {$description}");
        }
    }

    /**
     * Tester les relations belle-fille
     */
    private function testDaughterInLawRelations(User $ahmed, User $amina, User $karim): void
    {
        // Ahmed épouse Amina
        $this->createAndTestRelation($ahmed, $amina, 'wife', 'Ahmed → Amina: épouse');
        
        // Karim est le père d'Ahmed, donc Amina devrait être sa belle-fille
        $this->createAndTestRelation($karim, $ahmed, 'son', 'Karim → Ahmed: fils');
        
        // Vérifier que Karim peut voir Amina comme belle-fille
        $daughterInLawType = RelationshipType::where('name', 'daughter_in_law')->first();
        $this->assertNotNull($daughterInLawType, "Type 'daughter_in_law' non trouvé");
        
        echo "✅ Type belle-fille disponible: {$daughterInLawType->display_name_fr}\n";
        
        // Tester la création de la relation belle-fille
        try {
            $this->createAndTestRelation($karim, $amina, 'daughter_in_law', 'Karim → Amina: belle-fille');
            echo "✅ Relation belle-fille créée avec succès\n";
        } catch (\Exception $e) {
            echo "❌ Erreur lors de la création de la relation belle-fille: {$e->getMessage()}\n";
        }
    }

    /**
     * Test spécifique pour les problèmes de genre dans les méthodes helper
     */
    public function test_gender_helper_methods(): void
    {
        echo "\n=== TEST DES MÉTHODES HELPER DE GENRE ===\n";

        // Créer des utilisateurs test
        $maleUser = User::factory()->withProfile('male')->create(['name' => 'Test Male']);
        $femaleUser = User::factory()->withProfile('female')->create(['name' => 'Test Female']);

        // Tester les méthodes de genre via reflection
        $reflection = new \ReflectionClass($this->familyRelationService);

        // Test getSiblingRelationByGender
        $method = $reflection->getMethod('getSiblingRelationByGender');
        $method->setAccessible(true);

        $maleResult = $method->invoke($this->familyRelationService, $maleUser);
        $femaleResult = $method->invoke($this->familyRelationService, $femaleUser);

        $this->assertEquals('brother', $maleResult->name, "Homme devrait être 'brother'");
        $this->assertEquals('sister', $femaleResult->name, "Femme devrait être 'sister'");

        echo "✅ getSiblingRelationByGender fonctionne correctement\n";

        // Test getChildRelationByGender
        $method = $reflection->getMethod('getChildRelationByGender');
        $method->setAccessible(true);

        $maleChildResult = $method->invoke($this->familyRelationService, $maleUser);
        $femaleChildResult = $method->invoke($this->familyRelationService, $femaleUser);

        $this->assertEquals('son', $maleChildResult->name, "Garçon devrait être 'son'");
        $this->assertEquals('daughter', $femaleChildResult->name, "Fille devrait être 'daughter'");

        echo "✅ getChildRelationByGender fonctionne correctement\n";

        // Test getParentRelationByGender
        $method = $reflection->getMethod('getParentRelationByGender');
        $method->setAccessible(true);

        $maleParentResult = $method->invoke($this->familyRelationService, $maleUser);
        $femaleParentResult = $method->invoke($this->familyRelationService, $femaleUser);

        $this->assertEquals('father', $maleParentResult->name, "Homme devrait être 'father'");
        $this->assertEquals('mother', $femaleParentResult->name, "Femme devrait être 'mother'");

        echo "✅ getParentRelationByGender fonctionne correctement\n";

        echo "\n🎉 TESTS DES MÉTHODES HELPER TERMINÉS\n";
    }
}
