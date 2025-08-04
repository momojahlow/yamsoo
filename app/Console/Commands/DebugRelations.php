<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;

class DebugRelations extends Command
{
    protected $signature = 'debug:relations';
    protected $description = 'Debug family relationships in database';

    public function handle()
    {
        $this->info('🔍 DEBUG DES RELATIONS DANS LA BASE DE DONNÉES');
        $this->info('==============================================');

        // Récupérer les utilisateurs
        $ahmed = User::where('name', 'like', '%Ahmed%')->first();
        $fatima = User::where('name', 'like', '%Fatima%')->first();
        $mohammed = User::where('name', 'like', '%Mohammed%')->first();

        if (!$ahmed || !$fatima || !$mohammed) {
            $this->error('❌ Utilisateurs non trouvés');
            return;
        }

        $this->info('👥 Utilisateurs trouvés:');
        $this->info("- Ahmed: ID {$ahmed->id}");
        $this->info("- Fatima: ID {$fatima->id}");
        $this->info("- Mohammed: ID {$mohammed->id}");

        // Récupérer toutes les relations
        $relations = FamilyRelationship::with(['user', 'relatedUser', 'relationshipType'])
            ->where('status', 'accepted')
            ->get();

        $this->info('📋 TOUTES LES RELATIONS DANS LA BASE:');
        foreach ($relations as $relation) {
            $this->info("- {$relation->user->name} ({$relation->user_id}) → {$relation->relatedUser->name} ({$relation->related_user_id}) : {$relation->relationshipType->name}");
        }

        $this->info('🔍 ANALYSE SPÉCIFIQUE:');

        // Relation Ahmed → Fatima
        $ahmedToFatima = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('related_user_id', $fatima->id)
            ->with('relationshipType')
            ->first();

        if ($ahmedToFatima) {
            $this->info("Ahmed → Fatima: {$ahmedToFatima->relationshipType->name}");
        } else {
            $this->info("Ahmed → Fatima: AUCUNE RELATION");
        }

        // Relation Fatima → Ahmed
        $fatimaToAhmed = FamilyRelationship::where('user_id', $fatima->id)
            ->where('related_user_id', $ahmed->id)
            ->with('relationshipType')
            ->first();

        if ($fatimaToAhmed) {
            $this->info("Fatima → Ahmed: {$fatimaToAhmed->relationshipType->name}");
        } else {
            $this->info("Fatima → Ahmed: AUCUNE RELATION");
        }

        // Relation Ahmed → Mohammed
        $ahmedToMohammed = FamilyRelationship::where('user_id', $ahmed->id)
            ->where('related_user_id', $mohammed->id)
            ->with('relationshipType')
            ->first();

        if ($ahmedToMohammed) {
            $this->info("Ahmed → Mohammed: {$ahmedToMohammed->relationshipType->name}");
        } else {
            $this->info("Ahmed → Mohammed: AUCUNE RELATION");
        }

        // Relation Mohammed → Ahmed
        $mohammedToAhmed = FamilyRelationship::where('user_id', $mohammed->id)
            ->where('related_user_id', $ahmed->id)
            ->with('relationshipType')
            ->first();

        if ($mohammedToAhmed) {
            $this->info("Mohammed → Ahmed: {$mohammedToAhmed->relationshipType->name}");
        } else {
            $this->info("Mohammed → Ahmed: AUCUNE RELATION");
        }

        $this->info('🎯 PROBLÈME IDENTIFIÉ:');
        $this->info('Pour que les suggestions soient correctes:');
        $this->info("- Fatima → Ahmed devrait être 'wife'");
        $this->info("- Ahmed → Fatima devrait être 'husband'");
        $this->info("- Mohammed → Ahmed devrait être 'son'");
        $this->info("- Ahmed → Mohammed devrait être 'father'");
    }
}
