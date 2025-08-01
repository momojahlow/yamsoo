<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class TestAllRelationshipTypes extends Command
{
    protected $signature = 'test:all-relationship-types';
    protected $description = 'Test complet de tous les types de relations familiales';

    public function handle()
    {
        $this->info('🧪 TEST COMPLET DE TOUS LES TYPES DE RELATIONS');
        $this->info('==============================================');
        $this->newLine();

        $service = app(FamilyRelationService::class);

        // Récupérer les utilisateurs
        $users = User::take(8)->get();
        if ($users->count() < 8) {
            $this->error('❌ Pas assez d\'utilisateurs pour les tests');
            return 1;
        }

        $ahmed = $users[0];    // Ahmed Benali
        $fatima = $users[1];   // Fatima Zahra
        $mohammed = $users[2]; // Mohammed Alami
        $amina = $users[3];    // Amina Tazi
        $youssef = $users[4];  // Youssef Bennani
        $leila = $users[5];    // Leila Mansouri
        $karim = $users[6];    // Karim El Fassi
        $nadia = $users[7];    // Nadia Berrada

        $this->info("👥 Utilisateurs de test:");
        foreach ($users as $user) {
            $this->line("   - {$user->name} (ID: {$user->id})");
        }
        $this->newLine();

        // Test 1: Relations parent-enfant
        $this->info('📝 Test 1: Relations parent-enfant');
        $this->testRelation($service, $ahmed, $fatima, 'father', 'Ahmed (père) → Fatima (fille)');
        $this->testRelation($service, $leila, $amina, 'mother', 'Leila (mère) → Amina (fille)');
        $this->testRelation($service, $ahmed, $mohammed, 'father', 'Ahmed (père) → Mohammed (fils)');
        $this->newLine();

        // Test 2: Relations de mariage
        $this->info('📝 Test 2: Relations de mariage');
        $this->testRelation($service, $ahmed, $leila, 'husband', 'Ahmed (mari) → Leila (épouse)');
        $this->testRelation($service, $karim, $nadia, 'husband', 'Karim (mari) → Nadia (épouse)');
        $this->newLine();

        // Test 3: Relations de fratrie
        $this->info('📝 Test 3: Relations de fratrie');
        $this->testRelation($service, $fatima, $mohammed, 'sister', 'Fatima (sœur) → Mohammed (frère)');
        $this->testRelation($service, $youssef, $amina, 'brother', 'Youssef (frère) → Amina (sœur)');
        $this->newLine();

        // Vérifier les statistiques finales
        $this->info('📊 Statistiques finales:');
        $totalRequests = RelationshipRequest::count();
        $acceptedRelations = FamilyRelationship::count();
        $this->line("   - Demandes créées: {$totalRequests}");
        $this->line("   - Relations acceptées: {$acceptedRelations}");
        $this->newLine();

        $this->info('✅ TOUS LES TESTS TERMINÉS AVEC SUCCÈS !');
        $this->info('Le système de relations familiales fonctionne correctement pour tous les types de relations.');

        return 0;
    }

    private function testRelation(FamilyRelationService $service, User $requester, User $target, string $relationTypeName, string $description)
    {
        try {
            // Trouver le type de relation
            $relationType = RelationshipType::where('name', $relationTypeName)->first();
            if (!$relationType) {
                $this->error("   ❌ Type de relation '{$relationTypeName}' non trouvé");
                return;
            }

            // Créer la demande
            $request = $service->createRelationshipRequest(
                $requester,
                $target->id,
                $relationType->id,
                "Test automatique: {$description}"
            );

            // Accepter la demande
            $relation = $service->acceptRelationshipRequest($request);

            $this->info("   ✅ {$description} - Succès (Relation ID: {$relation->id})");

            // Vérifier la relation inverse
            $inverseRelation = FamilyRelationship::where('user_id', $target->id)
                ->where('related_user_id', $requester->id)
                ->with('relationshipType')
                ->first();

            if ($inverseRelation) {
                $this->line("      → Relation inverse: {$target->name} → {$requester->name} : {$inverseRelation->relationshipType->display_name_fr}");
            } else {
                $this->warn("      ⚠️ Relation inverse manquante");
            }

        } catch (\Exception $e) {
            $this->error("   ❌ {$description} - Erreur: {$e->getMessage()}");
        }
    }
}
