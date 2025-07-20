<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;

class DebugYoussefRelation extends Command
{
    protected $signature = 'debug:youssef-relation';
    protected $description = 'Debug la relation entre Youssef Bennani et Mohammed Alami';

    public function handle()
    {
        $this->info('🔍 DEBUG RELATION YOUSSEF BENNANI - MOHAMMED ALAMI');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Youssef Bennani
        $youssef = User::where('name', 'like', '%Youssef%')->where('name', 'like', '%Bennani%')->first();
        $mohammed = User::where('name', 'like', '%Mohammed%')->where('name', 'like', '%Alami%')->first();
        
        if (!$youssef || !$mohammed) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        $this->info("👤 YOUSSEF : {$youssef->name} (ID: {$youssef->id})");
        $this->info("👤 MOHAMMED : {$mohammed->name} (ID: {$mohammed->id})");
        $this->newLine();

        // Analyser toutes les relations impliquant ces deux utilisateurs
        $this->info('1️⃣ RELATIONS DIRECTES ENTRE YOUSSEF ET MOHAMMED :');
        $directRelations = FamilyRelationship::where(function($query) use ($youssef, $mohammed) {
            $query->where(function($subQuery) use ($youssef, $mohammed) {
                $subQuery->where('user_id', $youssef->id)
                         ->where('related_user_id', $mohammed->id);
            })->orWhere(function($subQuery) use ($youssef, $mohammed) {
                $subQuery->where('user_id', $mohammed->id)
                         ->where('related_user_id', $youssef->id);
            });
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

        foreach ($directRelations as $relation) {
            $from = $relation->user;
            $to = $relation->relatedUser;
            $relationType = $relation->relationshipType;
            
            $this->line("   📋 {$from->name} → {$to->name} : {$relationType->name_fr} ({$relationType->code})");
        }
        $this->newLine();

        // Analyser toutes les relations de Youssef
        $this->info('2️⃣ TOUTES LES RELATIONS DE YOUSSEF :');
        $youssefRelations = FamilyRelationship::where(function($query) use ($youssef) {
            $query->where('user_id', $youssef->id)
                  ->orWhere('related_user_id', $youssef->id);
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

        foreach ($youssefRelations as $relation) {
            $from = $relation->user;
            $to = $relation->relatedUser;
            $relationType = $relation->relationshipType;
            
            if ($from->id === $youssef->id) {
                $this->line("   👨 Youssef → {$to->name} : {$relationType->name_fr} ({$relationType->code})");
            } else {
                $this->line("   👨 {$from->name} → Youssef : {$relationType->name_fr} ({$relationType->code})");
            }
        }
        $this->newLine();

        // Analyser toutes les relations de Mohammed
        $this->info('3️⃣ TOUTES LES RELATIONS DE MOHAMMED :');
        $mohammedRelations = FamilyRelationship::where(function($query) use ($mohammed) {
            $query->where('user_id', $mohammed->id)
                  ->orWhere('related_user_id', $mohammed->id);
        })
        ->with(['user', 'relatedUser', 'relationshipType'])
        ->get();

        foreach ($mohammedRelations as $relation) {
            $from = $relation->user;
            $to = $relation->relatedUser;
            $relationType = $relation->relationshipType;
            
            if ($from->id === $mohammed->id) {
                $this->line("   👨 Mohammed → {$to->name} : {$relationType->name_fr} ({$relationType->code})");
            } else {
                $this->line("   👨 {$from->name} → Mohammed : {$relationType->name_fr} ({$relationType->code})");
            }
        }
        $this->newLine();

        // Analyser la logique de détermination de relation
        $this->info('4️⃣ ANALYSE DE LA LOGIQUE :');
        
        // Trouver la relation père/fils
        $fatherRelation = $directRelations->first(function($relation) use ($youssef, $mohammed) {
            return ($relation->user_id === $youssef->id && $relation->related_user_id === $mohammed->id && $relation->relationshipType->code === 'father') ||
                   ($relation->user_id === $mohammed->id && $relation->related_user_id === $youssef->id && $relation->relationshipType->code === 'son');
        });

        if ($fatherRelation) {
            $this->line("   ✅ Relation père/fils trouvée :");
            $this->line("      {$fatherRelation->user->name} → {$fatherRelation->relatedUser->name} : {$fatherRelation->relationshipType->name_fr}");
            
            if ($fatherRelation->user_id === $youssef->id && $fatherRelation->relationshipType->code === 'father') {
                $this->line("      ✅ Youssef est le PÈRE de Mohammed");
            } elseif ($fatherRelation->user_id === $mohammed->id && $fatherRelation->relationshipType->code === 'son') {
                $this->line("      ✅ Mohammed est le FILS de Youssef");
            }
        } else {
            $this->line("   ❌ Aucune relation père/fils claire trouvée");
        }

        return 0;
    }
}
