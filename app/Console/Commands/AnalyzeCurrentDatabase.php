<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\RelationshipType;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\Suggestion;
use App\Services\FamilyRelationService;
use App\Services\SuggestionService;

class AnalyzeCurrentDatabase extends Command
{
    protected $signature = 'analyze:current-db';
    protected $description = 'Analyser l\'état actuel de la base de données';

    public function handle()
    {
        $this->info('🔍 ANALYSE DE L\'ÉTAT ACTUEL DE LA BASE DE DONNÉES');
        $this->info('=====================================================');
        $this->newLine();

        // Analyser les utilisateurs
        $this->info('👥 UTILISATEURS:');
        $users = User::all();
        foreach ($users as $user) {
            $this->line("   - {$user->name} (ID: {$user->id}) - {$user->email}");
        }
        $this->newLine();

        // Analyser les demandes de relation en attente
        $this->info('📋 DEMANDES DE RELATION EN ATTENTE:');
        $pendingRequests = RelationshipRequest::where('status', 'pending')
            ->with(['requester', 'target', 'relationshipType'])
            ->get();
        
        if ($pendingRequests->isEmpty()) {
            $this->line('   Aucune demande en attente');
        } else {
            foreach ($pendingRequests as $request) {
                $this->line("   - {$request->requester->name} → {$request->target->name} : {$request->relationshipType->display_name_fr}");
                $this->line("     Status: {$request->status}, Message: {$request->message}");
            }
        }
        $this->newLine();

        // Analyser les relations acceptées
        $this->info('✅ RELATIONS ACCEPTÉES:');
        $acceptedRelations = FamilyRelationship::where('status', 'accepted')
            ->with(['user', 'relatedUser', 'relationshipType'])
            ->get();
        
        if ($acceptedRelations->isEmpty()) {
            $this->line('   Aucune relation acceptée');
        } else {
            foreach ($acceptedRelations as $relation) {
                $this->line("   - {$relation->user->name} → {$relation->relatedUser->name} : {$relation->relationshipType->display_name_fr}");
            }
        }
        $this->newLine();

        // Analyser les suggestions actuelles
        $this->info('💡 SUGGESTIONS ACTUELLES:');
        $suggestions = Suggestion::with(['user', 'suggestedUser'])
            ->get();
        
        if ($suggestions->isEmpty()) {
            $this->line('   Aucune suggestion');
        } else {
            foreach ($suggestions as $suggestion) {
                $relationName = $suggestion->suggested_relation_name ?? 'Non défini';
                $this->line("   - {$suggestion->user->name} → {$suggestion->suggestedUser->name} : {$relationName}");
                $this->line("     Raison: {$suggestion->reason}");
            }
        }
        $this->newLine();

        // Récupérer les utilisateurs spécifiques
        $ahmed = User::where('name', 'Ahmed Benali')->first();
        $fatima = User::where('name', 'Fatima Zahra')->first();
        $mohammed = User::where('name', 'Mohammed Alami')->first();
        $amina = User::where('name', 'Amina Tazi')->first();

        if (!$ahmed || !$fatima || !$mohammed || !$amina) {
            $this->error('❌ Utilisateurs manquants dans la base de données');
            return 1;
        }

        $this->info('🎯 ANALYSE SPÉCIFIQUE DU SCÉNARIO:');
        $this->line("   Ahmed Benali (ID: {$ahmed->id})");
        $this->line("   Fatima Zahra (ID: {$fatima->id})");
        $this->line("   Mohammed Alami (ID: {$mohammed->id})");
        $this->line("   Amina Tazi (ID: {$amina->id})");
        $this->newLine();

        // Vérifier les relations spécifiques
        $this->checkSpecificRelation($ahmed, $fatima, 'Ahmed → Fatima (épouse)');
        $this->checkSpecificRelation($ahmed, $mohammed, 'Ahmed → Mohammed (fils)');
        $this->checkSpecificRelation($ahmed, $amina, 'Ahmed → Amina (fille)');
        $this->checkSpecificRelation($fatima, $ahmed, 'Fatima → Ahmed (mari)');
        $this->checkSpecificRelation($mohammed, $ahmed, 'Mohammed → Ahmed (père)');
        $this->checkSpecificRelation($amina, $ahmed, 'Amina → Ahmed (père)');

        $this->newLine();
        $this->info('✅ ANALYSE TERMINÉE');

        return 0;
    }

    private function checkSpecificRelation(User $user1, User $user2, string $description)
    {
        // Vérifier demande en attente
        $pendingRequest = RelationshipRequest::where('requester_id', $user1->id)
            ->where('target_id', $user2->id)
            ->where('status', 'pending')
            ->with('relationshipType')
            ->first();

        if ($pendingRequest) {
            $this->warn("   📋 {$description} - DEMANDE EN ATTENTE ({$pendingRequest->relationshipType->display_name_fr})");
            return;
        }

        // Vérifier relation acceptée
        $acceptedRelation = FamilyRelationship::where('user_id', $user1->id)
            ->where('related_user_id', $user2->id)
            ->where('status', 'accepted')
            ->with('relationshipType')
            ->first();

        if ($acceptedRelation) {
            $this->info("   ✅ {$description} - ACCEPTÉE ({$acceptedRelation->relationshipType->display_name_fr})");
            return;
        }

        $this->line("   ⚪ {$description} - AUCUNE RELATION");
    }
}
