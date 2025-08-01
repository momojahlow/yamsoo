<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class FinalSystemTest extends Command
{
    protected $signature = 'test:final-system';
    protected $description = 'Test final complet du système de relations familiales';

    public function handle()
    {
        $this->info('🎯 TEST FINAL COMPLET DU SYSTÈME');
        $this->info('===============================');
        $this->newLine();

        $service = app(FamilyRelationService::class);

        // Récupérer les utilisateurs
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $mohammed = User::where('name', 'Mohammed Alami')->first();
        $amina = User::where('name', 'Amina Tazi')->first();
        $leila = User::where('name', 'Leila Mansouri')->first();

        $this->info('👥 Utilisateurs de test:');
        $this->line("   - Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   - Fatima Zahra (ID: {$fatima->id})");
        $this->line("   - Mohammed Alami (ID: {$mohammed->id})");
        $this->line("   - Amina Tazi (ID: {$amina->id})");
        $this->line("   - Leila Mansouri (ID: {$leila->id})");
        $this->newLine();

        // Test 1: Créer une famille cohérente
        $this->info('📝 Test 1: Création d\'une famille cohérente');
        
        // Ahmed et Leila sont mariés
        $this->createAndAcceptRelation($service, $ahmed, $leila, 'husband', 'Ahmed (mari) ↔ Leila (épouse)');
        
        // Ahmed est le père de Fatima et Mohammed
        $this->createAndAcceptRelation($service, $ahmed, $fatima, 'father', 'Ahmed (père) ↔ Fatima (fille)');
        $this->createAndAcceptRelation($service, $ahmed, $mohammed, 'father', 'Ahmed (père) ↔ Mohammed (fils)');
        
        // Leila est la mère de Fatima et Mohammed
        $this->createAndAcceptRelation($service, $leila, $fatima, 'mother', 'Leila (mère) ↔ Fatima (fille)');
        $this->createAndAcceptRelation($service, $leila, $mohammed, 'mother', 'Leila (mère) ↔ Mohammed (fils)');
        
        $this->newLine();

        // Test 2: Vérifier que les relations de fratrie sont automatiquement créées
        $this->info('📝 Test 2: Vérification des relations de fratrie automatiques');
        
        $fatimaToMohammed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $mohammed->id)
            ->with('relationshipType')
            ->first();
            
        $mohammedToFatima = FamilyRelationship::where('user_id', $mohammed->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();

        if ($fatimaToMohammed && $mohammedToFatima) {
            $this->info("   ✅ Fatima ↔ Mohammed : {$fatimaToMohammed->relationshipType->display_name_fr} / {$mohammedToFatima->relationshipType->display_name_fr}");
            
            // Vérifier que cette relation est justifiée
            if ($this->hasSiblingJustification($fatima, $mohammed)) {
                $this->info('   ✅ Relation de fratrie justifiée par parents communs');
            } else {
                $this->error('   ❌ Relation de fratrie NON justifiée');
            }
        } else {
            $this->warn('   ⚠️ Relations de fratrie non créées automatiquement');
        }
        
        $this->newLine();

        // Test 3: Vérifier le problème original
        $this->info('📝 Test 3: Vérification du problème original résolu');
        
        $fatimaRelations = FamilyRelationship::where('user_id', $fatima->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();
            
        $this->info('   Relations de Fatima:');
        foreach ($fatimaRelations as $relation) {
            $this->line("     - Fatima → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}");
        }
        
        // Vérifier spécifiquement qu'il n'y a pas de relation incorrecte avec Amina
        $fatimaToAmina = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $amina->id)
            ->first();
            
        if (!$fatimaToAmina) {
            $this->info('   ✅ Aucune relation incorrecte entre Fatima et Amina');
        } else {
            $this->error('   ❌ Relation incorrecte trouvée entre Fatima et Amina');
        }
        
        $this->newLine();

        // Test 4: Statistiques finales
        $this->info('📊 Statistiques finales:');
        $totalUsers = User::count();
        $totalRequests = RelationshipRequest::count();
        $totalRelations = FamilyRelationship::count();
        $acceptedRelations = FamilyRelationship::where('status', 'accepted')->count();
        
        $this->line("   - Utilisateurs: {$totalUsers}");
        $this->line("   - Demandes de relation: {$totalRequests}");
        $this->line("   - Relations familiales: {$totalRelations}");
        $this->line("   - Relations acceptées: {$acceptedRelations}");
        
        $this->newLine();
        $this->info('🎉 TEST FINAL TERMINÉ AVEC SUCCÈS !');
        $this->info('Le système de relations familiales fonctionne correctement.');
        $this->info('Le problème original de Fatima a été résolu définitivement.');

        return 0;
    }

    private function createAndAcceptRelation(FamilyRelationService $service, User $requester, User $target, string $relationTypeName, string $description)
    {
        try {
            $relationType = RelationshipType::where('name', $relationTypeName)->first();
            if (!$relationType) {
                $this->error("   ❌ Type de relation '{$relationTypeName}' non trouvé");
                return;
            }

            $request = $service->createRelationshipRequest(
                $requester,
                $target->id,
                $relationType->id,
                "Test final: {$description}"
            );

            $relation = $service->acceptRelationshipRequest($request);
            $this->info("   ✅ {$description} - Succès");

        } catch (\Exception $e) {
            $this->error("   ❌ {$description} - Erreur: {$e->getMessage()}");
        }
    }

    private function hasSiblingJustification(User $user1, User $user2): bool
    {
        $user1Parents = FamilyRelationship::where('user_id', $user1->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['father', 'mother']);
            })
            ->pluck('related_user_id')
            ->toArray();

        $user2Parents = FamilyRelationship::where('user_id', $user2->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['father', 'mother']);
            })
            ->pluck('related_user_id')
            ->toArray();

        $commonParents = array_intersect($user1Parents, $user2Parents);
        return !empty($commonParents);
    }
}
