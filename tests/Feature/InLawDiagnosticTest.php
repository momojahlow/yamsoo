<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InLawDiagnosticTest extends TestCase
{
    use RefreshDatabase;

    protected FamilyRelationService $familyRelationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->familyRelationService = app(FamilyRelationService::class);
        
        // Créer les types de relations nécessaires (seulement s'ils n'existent pas)
        $types = [
            ['name' => 'husband', 'display_name_fr' => 'Mari', 'display_name_ar' => 'زوج', 'display_name_en' => 'Husband', 'category' => 'marriage'],
            ['name' => 'wife', 'display_name_fr' => 'Épouse', 'display_name_ar' => 'زوجة', 'display_name_en' => 'Wife', 'category' => 'marriage'],
            ['name' => 'brother', 'display_name_fr' => 'Frère', 'display_name_ar' => 'أخ', 'display_name_en' => 'Brother', 'category' => 'direct'],
            ['name' => 'sister', 'display_name_fr' => 'Sœur', 'display_name_ar' => 'أخت', 'display_name_en' => 'Sister', 'category' => 'direct'],
            ['name' => 'brother_in_law', 'display_name_fr' => 'Beau-frère', 'display_name_ar' => 'صهر', 'display_name_en' => 'Brother-in-law', 'category' => 'marriage'],
            ['name' => 'sister_in_law', 'display_name_fr' => 'Belle-sœur', 'display_name_ar' => 'كنة', 'display_name_en' => 'Sister-in-law', 'category' => 'marriage'],
        ];

        foreach ($types as $type) {
            RelationshipType::firstOrCreate(['name' => $type['name']], $type);
        }
    }

    /**
     * Test diagnostic simple pour les relations de belle-famille
     */
    public function test_diagnostic_in_law_relationships(): void
    {
        // Créer les utilisateurs avec leurs profils et genres
        $ahmed = User::factory()->withProfile('male')->create(['name' => 'Ahmed', 'email' => 'ahmed@test.com']);
        $fatima = User::factory()->withProfile('female')->create(['name' => 'Fatima', 'email' => 'fatima@test.com']);
        $youssef = User::factory()->withProfile('male')->create(['name' => 'Youssef', 'email' => 'youssef@test.com']);

        echo "\n=== DIAGNOSTIC DES RELATIONS DE BELLE-FAMILLE ===\n";

        // 1. Ahmed épouse Fatima
        $husbandType = RelationshipType::where('name', 'husband')->first();
        $request1 = $this->familyRelationService->createRelationshipRequest(
            $ahmed, $fatima->id, $husbandType->id, 'Mariage'
        );
        $this->familyRelationService->acceptRelationshipRequest($request1);
        
        echo "✅ Ahmed et Fatima sont mariés\n";

        // 2. Fatima a Youssef comme frère
        $brotherType = RelationshipType::where('name', 'brother')->first();
        $request2 = $this->familyRelationService->createRelationshipRequest(
            $fatima, $youssef->id, $brotherType->id, 'Frère'
        );

        echo "🔍 Avant acceptation de la relation frère/sœur...\n";
        $relationsBefore = FamilyRelationship::count();
        echo "Nombre de relations avant : $relationsBefore\n";

        echo "🚀 ACCEPTATION DE LA RELATION FRÈRE/SŒUR...\n";
        $this->familyRelationService->acceptRelationshipRequest($request2);

        $relationsAfter = FamilyRelationship::count();
        echo "Nombre de relations après : $relationsAfter\n";
        echo "Nouvelles relations créées : " . ($relationsAfter - $relationsBefore) . "\n";

        // Vérifier spécifiquement les relations de belle-famille créées
        $inLawRelations = FamilyRelationship::whereHas('relationshipType', function($query) {
            $query->whereIn('name', ['brother_in_law', 'sister_in_law']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        echo "Relations de belle-famille créées automatiquement : " . $inLawRelations->count() . "\n";
        foreach ($inLawRelations as $relation) {
            echo "  - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}\n";
        }

        echo "✅ Fatima et Youssef sont frère et sœur\n";

        // 3. Vérifier toutes les relations dans la base
        echo "\n=== TOUTES LES RELATIONS DANS LA BASE ===\n";
        $allRelations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])->get();
        
        foreach ($allRelations as $relation) {
            echo sprintf(
                "- %s → %s : %s (statut: %s, créé automatiquement: %s)\n",
                $relation->user->name,
                $relation->relatedUser->name,
                $relation->relationshipType->display_name_fr,
                $relation->status,
                $relation->created_automatically ? 'OUI' : 'NON'
            );
        }

        // 4. Vérifier ce que voit Ahmed dans sa famille
        echo "\n=== CE QUE VOIT AHMED DANS SA FAMILLE ===\n";
        
        $ahmedRelationsAsUser = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();
            
        $ahmedRelationsAsRelated = FamilyRelationship::where('related_user_id', $ahmed->id)
            ->where('status', 'accepted')
            ->with(['user', 'relationshipType'])
            ->get();

        echo "Relations où Ahmed est user_id :\n";
        foreach ($ahmedRelationsAsUser as $relation) {
            echo sprintf(
                "  - %s : %s\n",
                $relation->relatedUser->name,
                $relation->relationshipType->display_name_fr
            );
        }

        echo "Relations où Ahmed est related_user_id :\n";
        foreach ($ahmedRelationsAsRelated as $relation) {
            echo sprintf(
                "  - %s : %s (relation inverse)\n",
                $relation->user->name,
                $relation->relationshipType->display_name_fr
            );
        }

        // 5. Simuler ce que fait le contrôleur Family
        echo "\n=== SIMULATION DU CONTRÔLEUR FAMILY ===\n";
        
        $response = $this->actingAs($ahmed)->get('/famille');
        $response->assertStatus(200);
        
        $members = $response->getOriginalContent()->getData()['page']['props']['members'];
        
        echo "Membres visibles par Ahmed :\n";
        foreach ($members as $member) {
            echo sprintf(
                "  - %s : %s (code: %s, catégorie: %s)\n",
                $member['name'],
                $member['relation'],
                $member['relation_code'],
                $member['category']
            );
        }

        // 6. Vérifications
        $inLawRelations = collect($members)->filter(function ($member) {
            return $member['category'] === 'in_law' || str_contains($member['relation_code'], '_in_law');
        });

        echo "\n=== RÉSULTAT ===\n";
        echo "Nombre de relations de belle-famille trouvées : " . $inLawRelations->count() . "\n";
        
        if ($inLawRelations->count() > 0) {
            echo "✅ Les relations de belle-famille sont visibles !\n";
            foreach ($inLawRelations as $relation) {
                echo "  - " . $relation['name'] . " : " . $relation['relation'] . "\n";
            }
        } else {
            echo "❌ Aucune relation de belle-famille visible\n";
            echo "Problème identifié : Les relations automatiquement inférées ne sont pas récupérées par le contrôleur\n";
        }

        // Assertion pour faire échouer le test si pas de belle-famille
        $this->assertGreaterThan(0, $inLawRelations->count(), 
            "Ahmed devrait voir au moins une relation de belle-famille (Youssef comme beau-frère)");
    }
}
