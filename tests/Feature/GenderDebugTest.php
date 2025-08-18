<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenderDebugTest extends TestCase
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
     * Test pour débugger les problèmes de genre
     */
    public function test_gender_debug(): void
    {
        echo "\n=== DEBUG GENRE ===\n";

        // Créer Amina (féminine) et Karim (masculin)
        $amina = User::factory()->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);

        // Essayer différentes approches pour créer le profil
        try {
            $aminaProfile = \App\Models\Profile::create([
                'user_id' => $amina->id,
                'gender' => 'female',
                'bio' => 'Test',
                'language' => 'fr'
            ]);
            echo "Profil Amina créé directement avec genre: {$aminaProfile->gender}\n";
        } catch (\Exception $e) {
            echo "Erreur création profil Amina: {$e->getMessage()}\n";
        }

        $karim = User::factory()->create(['name' => 'Karim El Fassi', 'email' => 'karim@test.com']);

        try {
            $karimProfile = \App\Models\Profile::create([
                'user_id' => $karim->id,
                'gender' => 'male',
                'bio' => 'Test',
                'language' => 'fr'
            ]);
            echo "Profil Karim créé directement avec genre: {$karimProfile->gender}\n";
        } catch (\Exception $e) {
            echo "Erreur création profil Karim: {$e->getMessage()}\n";
        }

        echo "Amina profil créé avec genre: {$aminaProfile->gender}\n";
        echo "Karim profil créé avec genre: {$karimProfile->gender}\n";

        // Recharger depuis la base
        $amina->refresh();
        $karim->refresh();

        echo "Amina genre depuis relation: {$amina->profile->gender}\n";
        echo "Karim genre depuis relation: {$karim->profile->gender}\n";

        // Vérifier directement dans la base
        $aminaFromDb = \App\Models\Profile::where('user_id', $amina->id)->first();
        $karimFromDb = \App\Models\Profile::where('user_id', $karim->id)->first();

        echo "Amina genre depuis DB: {$aminaFromDb->gender}\n";
        echo "Karim genre depuis DB: {$karimFromDb->gender}\n";

        // Test 1: Amina → Karim (grand-père)
        echo "\n--- Test 1: Amina → Karim (grand-père) ---\n";
        $grandfatherType = RelationshipType::where('name', 'grandfather')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $amina, $karim->id, $grandfatherType->id, 'Test grand-père'
        );
        $relation1 = $this->familyRelationService->acceptRelationshipRequest($request1);
        
        // Vérifier la relation réciproque
        $reciprocal1 = \App\Models\FamilyRelationship::where('user_id', $karim->id)
            ->where('related_user_id', $amina->id)
            ->with('relationshipType')
            ->first();
            
        if ($reciprocal1) {
            echo "Relation réciproque: Karim → Amina = {$reciprocal1->relationshipType->display_name_fr} ({$reciprocal1->relationshipType->name})\n";
            echo "Attendu: Petite-fille (granddaughter)\n";
        } else {
            echo "❌ Aucune relation réciproque trouvée\n";
        }

        // Test 2: Fatima → Karim (beau-père)
        echo "\n--- Test 2: Fatima → Karim (beau-père) ---\n";
        $fatima = User::factory()->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $fatima->profile()->create(['gender' => 'female', 'bio' => 'Test', 'language' => 'fr']);
        
        echo "Fatima genre: {$fatima->profile->gender}\n";
        
        $fatherInLawType = RelationshipType::where('name', 'father_in_law')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $karim->id, $fatherInLawType->id, 'Test beau-père'
        );
        $relation2 = $this->familyRelationService->acceptRelationshipRequest($request2);
        
        // Vérifier la relation réciproque
        $reciprocal2 = \App\Models\FamilyRelationship::where('user_id', $karim->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();
            
        if ($reciprocal2) {
            echo "Relation réciproque: Karim → Fatima = {$reciprocal2->relationshipType->display_name_fr} ({$reciprocal2->relationshipType->name})\n";
            echo "Attendu: Belle-fille (daughter_in_law)\n";
        } else {
            echo "❌ Aucune relation réciproque trouvée\n";
        }

        echo "\n🎉 DEBUG TERMINÉ\n";
    }
}
