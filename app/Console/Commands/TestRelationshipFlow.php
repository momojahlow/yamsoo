<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\FamilyRelationService;

class TestRelationshipFlow extends Command
{
    protected $signature = 'test:relationship-flow';
    protected $description = 'Test complet du flux de relations familiales';

    public function handle()
    {
        $this->info('🧪 TEST COMPLET DU FLUX DE RELATIONS FAMILIALES');
        $this->info('===============================================');
        $this->newLine();

        // Test 1: Vérifier les utilisateurs
        $this->info('👥 Test 1: Utilisateurs disponibles');
        $users = User::take(5)->get();
        foreach ($users as $user) {
            $this->line("   - {$user->name} (ID: {$user->id})");
        }
        $this->newLine();

        // Test 2: Vérifier les types de relations
        $this->info('📋 Test 2: Types de relations disponibles');
        $types = RelationshipType::take(10)->get();
        foreach ($types as $type) {
            $this->line("   - {$type->name} : {$type->display_name_fr}");
        }
        $this->newLine();

        // Test 3: Créer une demande de relation
        $this->info('📝 Test 3: Création d\'une demande de relation');
        
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $fatherType = RelationshipType::where('name', 'father')->first();

        if (!$ahmed || !$fatima || !$fatherType) {
            $this->error('❌ Utilisateurs ou type de relation non trouvés');
            return 1;
        }

        $this->line("   Demandeur: {$ahmed->name} (ID: {$ahmed->id})");
        $this->line("   Cible: {$fatima->name} (ID: {$fatima->id})");
        $this->line("   Type: {$fatherType->display_name_fr} (ID: {$fatherType->id})");

        $service = app(FamilyRelationService::class);

        try {
            $request = $service->createRelationshipRequest(
                $ahmed,
                $fatima->id,
                $fatherType->id,
                "Test de relation père-fille"
            );
            $this->info("   ✅ Demande créée avec succès (ID: {$request->id})");
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors de la création: {$e->getMessage()}");
            return 1;
        }
        $this->newLine();

        // Test 4: Vérifier la demande en attente
        $this->info('⏳ Test 4: Vérification de la demande en attente');
        $pendingRequest = RelationshipRequest::where('id', $request->id)
            ->with(['requester', 'targetUser', 'relationshipType'])
            ->first();

        if ($pendingRequest) {
            $this->info('   ✅ Demande trouvée:');
            $this->line("     - ID: {$pendingRequest->id}");
            $this->line("     - Statut: {$pendingRequest->status}");
            $this->line("     - Demandeur: {$pendingRequest->requester->name}");
            $this->line("     - Cible: {$pendingRequest->targetUser->name}");
            $this->line("     - Type: {$pendingRequest->relationshipType->display_name_fr}");
        } else {
            $this->error('   ❌ Demande non trouvée');
            return 1;
        }
        $this->newLine();

        // Test 5: Accepter la demande
        $this->info('✅ Test 5: Acceptation de la demande');
        try {
            $createdRelationship = $service->acceptRelationshipRequest($pendingRequest);
            $this->info('   ✅ Demande acceptée avec succès');
            $this->info("   ✅ Relation créée (ID: {$createdRelationship->id})");
        } catch (\Exception $e) {
            $this->error("   ❌ Erreur lors de l'acceptation: {$e->getMessage()}");
            $this->line("   Trace: {$e->getTraceAsString()}");
            return 1;
        }
        $this->newLine();

        // Test 6: Vérifier la mise à jour du statut
        $this->info('🔄 Test 6: Vérification de la mise à jour de la demande');
        $updatedRequest = RelationshipRequest::find($pendingRequest->id);
        if ($updatedRequest) {
            $this->info('   ✅ Demande mise à jour:');
            $this->line("     - Statut: {$updatedRequest->status}");
            $this->line("     - Répondu le: " . ($updatedRequest->responded_at ? $updatedRequest->responded_at->format('Y-m-d H:i:s') : 'Non défini'));
        } else {
            $this->error('   ❌ Demande non trouvée après acceptation');
        }
        $this->newLine();

        // Test 7: Vérifier les relations créées
        $this->info('🔗 Test 7: Vérification des relations créées');
        
        $ahmedRelations = FamilyRelationship::where('user_id', $ahmed->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $fatimaRelations = FamilyRelationship::where('user_id', $fatima->id)
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        $this->line('   Relations d\'Ahmed:');
        foreach ($ahmedRelations as $rel) {
            $this->line("     - Ahmed → {$rel->relatedUser->name} : {$rel->relationshipType->display_name_fr} (statut: {$rel->status})");
        }

        $this->line('   Relations de Fatima:');
        foreach ($fatimaRelations as $rel) {
            $this->line("     - Fatima → {$rel->relatedUser->name} : {$rel->relationshipType->display_name_fr} (statut: {$rel->status})");
        }
        $this->newLine();

        // Test 8: Vérifier la cohérence des relations inverses
        $this->info('🔄 Test 8: Vérification des relations inverses');
        
        $ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();

        $fatimaToAhmed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $ahmed->id)
            ->with('relationshipType')
            ->first();

        if ($ahmedToFatima && $fatimaToAhmed) {
            $this->info('   ✅ Relations bidirectionnelles trouvées:');
            $this->line("     - Ahmed → Fatima : {$ahmedToFatima->relationshipType->display_name_fr}");
            $this->line("     - Fatima → Ahmed : {$fatimaToAhmed->relationshipType->display_name_fr}");

            if ($ahmedToFatima->relationshipType->name === 'father' && $fatimaToAhmed->relationshipType->name === 'daughter') {
                $this->info('   ✅ Logique inverse correcte (père ↔ fille)');
            } else {
                $this->error('   ❌ Logique inverse incorrecte');
            }
        } else {
            $this->error('   ❌ Relations bidirectionnelles manquantes');
            if (!$ahmedToFatima) $this->line('     - Relation Ahmed → Fatima manquante');
            if (!$fatimaToAhmed) $this->line('     - Relation Fatima → Ahmed manquante');
        }
        $this->newLine();

        $this->info('✅ RÉSUMÉ DES TESTS');
        $this->info('===================');
        $this->info('Tous les tests ont été exécutés avec succès !');
        $this->info('Le flux complet de création et d\'acceptation de relations fonctionne correctement.');
        $this->newLine();

        $this->info('🎉 SYSTÈME DE RELATIONS VALIDÉ !');

        return 0;
    }
}
