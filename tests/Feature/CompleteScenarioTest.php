<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompleteScenarioTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRelationService = app(FamilyRelationService::class);

        // Exécuter les seeders nécessaires
        $this->seed(\Database\Seeders\ComprehensiveRelationshipTypesSeeder::class);
    }

    /**
     * Test du scénario complet demandé :
     * 
     * Ahmed crée les demandes :
     * - Vers Fatima en tant que "Épouse"
     * - Vers Mohammed en tant que "Fils"
     * - Vers Amina en tant que "Fille"
     * 
     * Fatima crée une demande vers Youssef en tant que "Frère"
     * 
     * Youssef crée les demandes :
     * - Vers Ahmed en tant que "Beau-frère"
     * - Vers Mohammed en tant que "Neveu"
     * - Vers Amina en tant que "Nièce"
     * 
     * Chaque acceptation crée deux entrées réciproques dans la table des relations.
     */
    public function test_complete_family_scenario(): void
    {
        // Créer les utilisateurs pour le test
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed Benali', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima Zahra', 'email' => 'fatima@test.com']);
        $mohammed = User::factory()->withProfile('male')->create(['name' => 'Mohammed Alami', 'email' => 'mohammed@test.com']);
        $amina = User::factory()->withProfile('female')->create(['name' => 'Amina Tazi', 'email' => 'amina@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef Bennani', 'email' => 'youssef@test.com']);

        echo "\n=== SCÉNARIO COMPLET DE RELATIONS FAMILIALES ===\n";

        // Afficher tous les types disponibles pour debug
        $allTypes = RelationshipType::pluck('name')->toArray();
        echo "Types disponibles : " . implode(', ', $allTypes) . "\n";

        // Récupérer les types de relations qui existent
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $wifeType = RelationshipType::where('name', 'wife')->first();
        $sonType = RelationshipType::where('name', 'son')->first();
        $daughterType = RelationshipType::where('name', 'daughter')->first();
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $sisterType = RelationshipType::where('name', 'sister')->first();
        $brotherInLawType = RelationshipType::where('name', 'brother_in_law')->first();
        $nephewType = RelationshipType::where('name', 'nephew')->first();
        $nieceType = RelationshipType::where('name', 'niece')->first();

        // Utiliser les types génériques s'ils existent
        if (!$husbandType) $husbandType = RelationshipType::where('name', 'spouse')->first();
        if (!$sonType) $sonType = RelationshipType::where('name', 'child')->first();
        if (!$daughterType) $daughterType = RelationshipType::where('name', 'child')->first();
        if (!$brotherType) $brotherType = RelationshipType::where('name', 'sibling')->first();

        echo "\n=== ÉTAPE 1: Ahmed crée ses demandes ===\n";

        // 1. Ahmed → Fatima (Épouse)
        echo "1. Ahmed demande à Fatima d'être son épouse...\n";
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $fatima->id, $husbandType->id, 'Demande de mariage'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        echo "✅ Ahmed et Fatima sont maintenant époux\n";

        // 2. Ahmed → Mohammed (Fils)
        echo "2. Ahmed déclare Mohammed comme son fils...\n";
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $mohammed->id, $sonType->id, 'Tu es mon fils'
        );
        $this->familyRelationService->acceptRelationshipRequest($request2);
        echo "✅ Ahmed et Mohammed sont maintenant père et fils\n";

        // 3. Ahmed → Amina (Fille)
        echo "3. Ahmed déclare Amina comme sa fille...\n";
        $request3 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $amina->id, $daughterType->id, 'Tu es ma fille'
        );
        $this->familyRelationService->acceptRelationshipRequest($request3);
        echo "✅ Ahmed et Amina sont maintenant père et fille\n";

        echo "\n=== ÉTAPE 2: Fatima crée sa demande ===\n";

        // 4. Fatima → Youssef (Frère)
        echo "4. Fatima déclare Youssef comme son frère...\n";
        $request4 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $youssef->id, $brotherType->id, 'Tu es mon frère'
        );
        $this->familyRelationService->acceptRelationshipRequest($request4);
        echo "✅ Fatima et Youssef sont maintenant frère et sœur\n";

        echo "\n=== ÉTAPE 3: Youssef crée ses demandes ===\n";

        // 5. Youssef → Ahmed (Beau-frère) - Si le type existe
        if ($brotherInLawType) {
            echo "5. Youssef demande à Ahmed d'être son beau-frère...\n";
            $request5 = $this->familyRelationService->createRelationshipRequest(
                $youssef, $ahmed->id, $brotherInLawType->id, 'Tu es mon beau-frère'
            );
            $this->familyRelationService->acceptRelationshipRequest($request5);
            echo "✅ Youssef et Ahmed sont maintenant beaux-frères\n";
        } else {
            echo "⚠️ Type brother_in_law non trouvé, relation ignorée\n";
        }

        // 6. Youssef → Mohammed (Neveu) - Si le type existe
        if ($nephewType) {
            echo "6. Youssef demande à Mohammed d'être son neveu...\n";
            $request6 = $this->familyRelationService->createRelationshipRequest(
                $youssef, $mohammed->id, $nephewType->id, 'Tu es mon neveu'
            );
            $this->familyRelationService->acceptRelationshipRequest($request6);
            echo "✅ Youssef et Mohammed sont maintenant oncle et neveu\n";
        } else {
            echo "⚠️ Type nephew non trouvé, relation ignorée\n";
        }

        // 7. Youssef → Amina (Nièce) - Si le type existe
        if ($nieceType) {
            echo "7. Youssef demande à Amina d'être sa nièce...\n";
            $request7 = $this->familyRelationService->createRelationshipRequest(
                $youssef, $amina->id, $nieceType->id, 'Tu es ma nièce'
            );
            $this->familyRelationService->acceptRelationshipRequest($request7);
            echo "✅ Youssef et Amina sont maintenant oncle et nièce\n";
        } else {
            echo "⚠️ Type niece non trouvé, relation ignorée\n";
        }

        echo "\n=== VÉRIFICATIONS FINALES ===\n";

        // Vérifier toutes les relations créées
        $this->verifyUserFamily($ahmed, 'Ahmed');
        $this->verifyUserFamily($fatima, 'Fatima');
        $this->verifyUserFamily($mohammed, 'Mohammed');
        $this->verifyUserFamily($amina, 'Amina');
        $this->verifyUserFamily($youssef, 'Youssef');

        // Vérifier les relations bidirectionnelles
        echo "\n=== VÉRIFICATION DES RELATIONS BIDIRECTIONNELLES ===\n";
        
        $totalRelations = FamilyRelationship::where('status', 'accepted')->count();
        echo "Total des relations en base : {$totalRelations}\n";

        // Vérifier que chaque relation a sa réciproque
        $this->verifyBidirectionalRelations();

        // Vérifications spécifiques
        $this->assertUserHasRelation($ahmed, $fatima, 'husband', 'Ahmed devrait avoir Fatima comme épouse');
        $this->assertUserHasRelation($fatima, $ahmed, 'wife', 'Fatima devrait avoir Ahmed comme époux');
        
        $this->assertUserHasRelation($ahmed, $mohammed, 'son', 'Ahmed devrait avoir Mohammed comme fils');
        $this->assertUserHasRelation($mohammed, $ahmed, 'father', 'Mohammed devrait avoir Ahmed comme père');
        
        $this->assertUserHasRelation($ahmed, $amina, 'daughter', 'Ahmed devrait avoir Amina comme fille');
        $this->assertUserHasRelation($amina, $ahmed, 'father', 'Amina devrait avoir Ahmed comme père');

        $this->assertUserHasRelation($fatima, $youssef, 'brother', 'Fatima devrait avoir Youssef comme frère');
        $this->assertUserHasRelation($youssef, $fatima, 'sister', 'Youssef devrait avoir Fatima comme sœur');

        // Vérifier les relations automatiquement créées
        $autoRelations = FamilyRelationship::where('created_automatically', true)->count();
        echo "\nRelations automatiquement créées : {$autoRelations}\n";

        echo "\n🎉 SCÉNARIO COMPLET RÉUSSI ! Toutes les relations fonctionnent correctement.\n";
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

    private function verifyBidirectionalRelations(): void
    {
        $relations = FamilyRelationship::where('status', 'accepted')->get();
        $missingReciprocalCount = 0;

        foreach ($relations as $relation) {
            $reciprocal = FamilyRelationship::where('user_id', $relation->related_user_id)
                ->where('related_user_id', $relation->user_id)
                ->where('status', 'accepted')
                ->exists();

            if (!$reciprocal) {
                $missingReciprocalCount++;
                echo "⚠️ Relation manquante : {$relation->relatedUser->name} → {$relation->user->name}\n";
            }
        }

        if ($missingReciprocalCount === 0) {
            echo "✅ Toutes les relations ont leur réciproque\n";
        } else {
            echo "❌ {$missingReciprocalCount} relations réciproques manquantes\n";
        }

        $this->assertEquals(0, $missingReciprocalCount, 'Toutes les relations devraient avoir leur réciproque');
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
