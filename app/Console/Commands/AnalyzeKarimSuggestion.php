<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;
use App\Models\FamilyRelationship;

class AnalyzeKarimSuggestion extends Command
{
    protected $signature = 'analyze:karim-suggestion';
    protected $description = 'Analyser pourquoi Karim est suggéré comme beau-fils au lieu de fils';

    public function handle()
    {
        $this->info('🔍 ANALYSE DE LA SUGGESTION KARIM EL FASSI');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia et Karim
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();
        $karim = User::where('name', 'like', '%Karim%')->where('name', 'like', '%El Fassi%')->first();
        
        if (!$nadia || !$karim) {
            $this->error('❌ Utilisateurs non trouvés');
            $this->line('Nadia: ' . ($nadia ? $nadia->name : 'NON TROUVÉ'));
            $this->line('Karim: ' . ($karim ? $karim->name : 'NON TROUVÉ'));
            return 1;
        }

        $this->info("👤 UTILISATEURS ANALYSÉS :");
        $this->line("   • Nadia Berrada (ID: {$nadia->id})");
        $this->line("   • Karim El Fassi (ID: {$karim->id})");
        $this->newLine();

        // Analyser la suggestion actuelle
        $this->info('1️⃣ SUGGESTION ACTUELLE :');
        $suggestion = Suggestion::where('user_id', $nadia->id)
            ->where('suggested_user_id', $karim->id)
            ->first();

        if ($suggestion) {
            $this->line("   📋 Suggestion trouvée :");
            $this->line("      • Type: {$suggestion->type}");
            $this->line("      • Code relation: {$suggestion->suggested_relation_code}");
            $this->line("      • Nom relation: {$suggestion->suggested_relation_name}");
            $this->line("      • Statut: {$suggestion->status}");
        } else {
            $this->line("   ⚠️  Aucune suggestion trouvée entre Nadia et Karim");
        }
        $this->newLine();

        // Analyser les relations existantes de Nadia
        $this->info('2️⃣ RELATIONS EXISTANTES DE NADIA :');
        $nadiaRelations = FamilyRelationship::where('user_id', $nadia->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        if ($nadiaRelations->isEmpty()) {
            $this->line("   ⚠️  Aucune relation familiale acceptée pour Nadia");
        } else {
            foreach ($nadiaRelations as $relation) {
                $relatedUser = $relation->relatedUser;
                $relationType = $relation->relationshipType;
                $this->line("   • {$relatedUser->name} → {$relationType->name_fr} ({$relationType->code})");
            }
        }
        $this->newLine();

        // Analyser les relations existantes de Karim
        $this->info('3️⃣ RELATIONS EXISTANTES DE KARIM :');
        $karimRelations = FamilyRelationship::where('user_id', $karim->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        if ($karimRelations->isEmpty()) {
            $this->line("   ⚠️  Aucune relation familiale acceptée pour Karim");
        } else {
            foreach ($karimRelations as $relation) {
                $relatedUser = $relation->relatedUser;
                $relationType = $relation->relationshipType;
                $this->line("   • {$relatedUser->name} → {$relationType->name_fr} ({$relationType->code})");
            }
        }
        $this->newLine();

        // Analyser les connexions communes
        $this->info('4️⃣ CONNEXIONS COMMUNES :');
        $commonConnections = collect();

        foreach ($nadiaRelations as $nadiaRel) {
            foreach ($karimRelations as $karimRel) {
                if ($nadiaRel->related_user_id === $karimRel->related_user_id) {
                    $commonUser = $nadiaRel->relatedUser;
                    $commonConnections->push([
                        'user' => $commonUser,
                        'nadia_relation' => $nadiaRel->relationshipType,
                        'karim_relation' => $karimRel->relationshipType,
                    ]);
                }
            }
        }

        if ($commonConnections->isEmpty()) {
            $this->line("   ⚠️  Aucune connexion commune trouvée");
            $this->line("   💡 C'est probablement pourquoi Karim est suggéré comme beau-fils");
            $this->line("      (relation par alliance) au lieu de fils (relation directe)");
        } else {
            $this->line("   🔗 Connexions communes trouvées :");
            foreach ($commonConnections as $connection) {
                $user = $connection['user'];
                $nadiaRel = $connection['nadia_relation'];
                $karimRel = $connection['karim_relation'];
                $this->line("      • {$user->name}:");
                $this->line("        - Nadia → {$user->name} : {$nadiaRel->name_fr}");
                $this->line("        - Karim → {$user->name} : {$karimRel->name_fr}");
            }
        }
        $this->newLine();

        // Recommandations
        $this->info('5️⃣ RECOMMANDATIONS :');
        
        if ($commonConnections->isEmpty()) {
            $this->line("   🎯 PROBLÈME IDENTIFIÉ :");
            $this->line("      Karim et Nadia n'ont aucune connexion familiale commune dans le système.");
            $this->line("      Le système suggère donc une relation par alliance (beau-fils).");
            $this->newLine();
            
            $this->line("   💡 SOLUTIONS POSSIBLES :");
            $this->line("      1. Ajouter d'abord les parents communs (si Karim est vraiment le fils de Nadia)");
            $this->line("      2. Ou ajouter le mari de Nadia comme père de Karim");
            $this->line("      3. Ou corriger manuellement la suggestion en choisissant 'Fils' au lieu de 'Beau-fils'");
        } else {
            $this->line("   ✅ Des connexions communes existent.");
            $this->line("   🔍 Vérifier la logique de suggestion pour comprendre pourquoi");
            $this->line("      'stepson' est choisi au lieu de 'son'.");
        }

        $this->newLine();
        $this->info('🎯 ANALYSE TERMINÉE !');

        return 0;
    }
}
