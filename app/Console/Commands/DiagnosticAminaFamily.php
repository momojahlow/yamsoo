<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;

class DiagnosticAminaFamily extends Command
{
    protected $signature = 'diagnostic:amina-family';
    protected $description = 'Diagnostic des relations familiales d\'Amina';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC DES RELATIONS D\'AMINA');
        $this->info('═══════════════════════════════════');
        $this->newLine();

        $amina = User::where('email', 'amina.tazi@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();
        $mohammed = User::where('email', 'mohammed.alami@example.com')->first();
        $youssef = User::where('email', 'youssef.bennani@example.com')->first();

        if (!$amina) {
            $this->error('❌ Amina non trouvée');
            return 1;
        }

        $this->info("👩 AMINA TAZI :");
        $this->line("   Email : {$amina->email}");
        $this->line("   Genre : {$amina->profile?->gender}");
        $this->newLine();

        // Vérifier les genres des autres personnes
        $this->info("👥 GENRES DES AUTRES PERSONNES :");
        if ($ahmed) {
            $this->line("   👨 Ahmed Benali : {$ahmed->profile?->gender}");
        }
        if ($mohammed) {
            $this->line("   👨 Mohammed Alami : {$mohammed->profile?->gender}");
        }
        if ($youssef) {
            $this->line("   👨 Youssef Bennani : {$youssef->profile?->gender}");
        }
        $this->newLine();

        // Vérifier les relations existantes d'Amina
        $this->info("🔗 RELATIONS EXISTANTES D'AMINA :");
        $aminaRelations = FamilyRelationship::where(function($query) use ($amina) {
            $query->where('user_id', $amina->id)
                  ->orWhere('related_user_id', $amina->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($aminaRelations as $relation) {
            if ($relation->user_id === $amina->id) {
                $this->line("   👩 Amina → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            } else {
                $this->line("   👤 {$relation->user->name} → Amina : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            }
        }
        $this->newLine();

        // Analyser pourquoi Ahmed et Mohammed sont suggérés comme "Sœur"
        $this->info("🧠 ANALYSE DU PROBLÈME :");
        
        // Vérifier si Ahmed, Mohammed et Amina ont le même père
        if ($youssef) {
            $youssefChildren = FamilyRelationship::where('related_user_id', $youssef->id)
                ->whereHas('relationshipType', function($query) {
                    $query->whereIn('code', ['son', 'daughter']);
                })
                ->with(['user', 'relationshipType'])
                ->get();

            $this->line("   👨 ENFANTS DE YOUSSEF :");
            foreach ($youssefChildren as $childRelation) {
                $child = $childRelation->user;
                $gender = $child->profile?->gender;
                $genderIcon = $gender === 'female' ? '👩' : '👨';
                $this->line("      {$genderIcon} {$child->name} : {$childRelation->relationshipType->name_fr} (genre: {$gender})");
            }
        }

        $this->newLine();
        $this->info("🔧 PROBLÈME IDENTIFIÉ :");
        $this->line("   Ahmed (👨 male) et Mohammed (👨 male) sont suggérés comme 'Sœur' pour Amina");
        $this->line("   Mais ils devraient être suggérés comme 'Frère' car ils sont des hommes !");
        $this->line("   Le service de suggestions ne vérifie pas correctement le genre.");

        return 0;
    }
}
