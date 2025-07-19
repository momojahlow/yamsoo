<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;

class AnalyzeSuggestionInconsistency extends Command
{
    protected $signature = 'analyze:suggestion-inconsistency';
    protected $description = 'Analyser l\'incohérence dans les suggestions de relations';

    public function handle()
    {
        $this->info('🔍 ANALYSE DE L\'INCOHÉRENCE DES SUGGESTIONS');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        // Trouver Leila et Ahmed
        $leila = User::where('email', 'leila.mansouri@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();

        if (!$leila || !$ahmed) {
            $this->error('❌ Utilisateurs non trouvés');
            return 1;
        }

        $this->info("👩 LEILA MANSOURI :");
        $this->line("   Email : {$leila->email}");
        $this->line("   Genre : {$leila->profile?->gender}");
        $this->newLine();

        $this->info("👨 AHMED BENALI :");
        $this->line("   Email : {$ahmed->email}");
        $this->line("   Genre : {$ahmed->profile?->gender}");
        $this->newLine();

        // Analyser les relations existantes de Leila
        $this->info("🔗 RELATIONS EXISTANTES DE LEILA :");
        $leilaRelations = FamilyRelationship::where(function($query) use ($leila) {
            $query->where('user_id', $leila->id)
                  ->orWhere('related_user_id', $leila->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($leilaRelations as $relation) {
            if ($relation->user_id === $leila->id) {
                $this->line("   👩 Leila → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            } else {
                $this->line("   👤 {$relation->user->name} → Leila : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            }
        }
        $this->newLine();

        // Analyser les relations existantes d'Ahmed
        $this->info("🔗 RELATIONS EXISTANTES D'AHMED :");
        $ahmedRelations = FamilyRelationship::where(function($query) use ($ahmed) {
            $query->where('user_id', $ahmed->id)
                  ->orWhere('related_user_id', $ahmed->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($ahmedRelations as $relation) {
            if ($relation->user_id === $ahmed->id) {
                $this->line("   👨 Ahmed → {$relation->relatedUser->name} : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            } else {
                $this->line("   👤 {$relation->user->name} → Ahmed : {$relation->relationshipType->name_fr} ({$relation->relationshipType->code})");
            }
        }
        $this->newLine();

        // Analyser la suggestion problématique
        $this->info("🤖 SUGGESTION PROBLÉMATIQUE :");
        $suggestion = Suggestion::where('user_id', $leila->id)
            ->where('suggested_user_id', $ahmed->id)
            ->with(['suggestedUser'])
            ->first();

        if ($suggestion) {
            $this->line("   Suggestion : Leila → Ahmed");
            $this->line("   Code relation suggérée : {$suggestion->suggested_relation_code}");
            $this->line("   Message : {$suggestion->message}");
            $this->line("   Statut : {$suggestion->status}");
        } else {
            $this->line("   ❌ Aucune suggestion trouvée entre Leila et Ahmed");
        }
        $this->newLine();

        // Analyser la logique qui a mené à cette suggestion
        $this->info("🧠 ANALYSE DE LA LOGIQUE :");

        // Chercher les connexions communes
        $commonConnections = collect();

        foreach ($leilaRelations as $leilaRel) {
            $leilaContact = $leilaRel->user_id === $leila->id ? $leilaRel->relatedUser : $leilaRel->user;

            foreach ($ahmedRelations as $ahmedRel) {
                $ahmedContact = $ahmedRel->user_id === $ahmed->id ? $ahmedRel->relatedUser : $ahmedRel->user;

                if ($leilaContact->id === $ahmedContact->id) {
                    $leilaRelType = $leilaRel->user_id === $leila->id ? $leilaRel->relationshipType->code : 'inverse_' . $leilaRel->relationshipType->code;
                    $ahmedRelType = $ahmedRel->user_id === $ahmed->id ? $ahmedRel->relationshipType->code : 'inverse_' . $ahmedRel->relationshipType->code;

                    $commonConnections->push([
                        'person' => $leilaContact->name,
                        'leila_relation' => $leilaRelType,
                        'ahmed_relation' => $ahmedRelType
                    ]);
                }
            }
        }

        if ($commonConnections->isNotEmpty()) {
            $this->line("   Connexions communes trouvées :");
            foreach ($commonConnections as $connection) {
                $this->line("      👤 {$connection['person']} :");
                $this->line("         Leila → {$connection['person']} : {$connection['leila_relation']}");
                $this->line("         Ahmed → {$connection['person']} : {$connection['ahmed_relation']}");
            }
        } else {
            $this->line("   ❌ Aucune connexion commune trouvée");
        }
        $this->newLine();

        // Analyser pourquoi "grandfather_paternal" a été suggéré
        $this->info("🔧 PROBLÈME IDENTIFIÉ :");
        $this->line("   Ahmed Benali (👨 male) est suggéré comme 'Grand-père paternel' pour Leila");
        $this->line("   Cette suggestion semble incohérente car :");
        $this->line("   1. Ahmed est probablement de la même génération que Leila (frère/sœur)");
        $this->line("   2. Un grand-père devrait être de 2 générations plus âgé");
        $this->line("   3. La logique de déduction semble défaillante");
        $this->newLine();

        $this->info("💡 SOLUTION RECOMMANDÉE :");
        $this->line("   1. Vérifier la logique de déduction dans IntelligentSuggestionService");
        $this->line("   2. Ajouter des validations d'âge/génération");
        $this->line("   3. Corriger les règles de suggestion grand-parent");
        $this->line("   4. Supprimer cette suggestion incorrecte");

        return 0;
    }
}
