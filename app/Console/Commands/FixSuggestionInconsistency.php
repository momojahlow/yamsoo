<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\RelationshipType;

class FixSuggestionInconsistency extends Command
{
    protected $signature = 'fix:suggestion-inconsistency';
    protected $description = 'Corriger les incohérences dans les suggestions de relations';

    public function handle()
    {
        $this->info('🔧 CORRECTION DES INCOHÉRENCES DE SUGGESTIONS');
        $this->info('═══════════════════════════════════════════');
        $this->newLine();

        $fixedCount = 0;

        // 1. Supprimer la suggestion incorrecte Ahmed → Leila (grandfather_paternal)
        $this->info('1️⃣ SUPPRESSION DE LA SUGGESTION INCORRECTE :');

        $leila = User::where('email', 'leila.mansouri@example.com')->first();
        $ahmed = User::where('email', 'ahmed.benali@example.com')->first();

        if ($leila && $ahmed) {
            $incorrectSuggestion = Suggestion::where('user_id', $leila->id)
                ->where('suggested_user_id', $ahmed->id)
                ->where('suggested_relation_code', 'grandfather_paternal')
                ->first();

            if ($incorrectSuggestion) {
                $incorrectSuggestion->delete();
                $this->line("   ✅ Suggestion incorrecte supprimée : Leila → Ahmed (grandfather_paternal)");
                $fixedCount++;
            } else {
                $this->line("   ℹ️ Aucune suggestion incorrecte trouvée");
            }
        }
        $this->newLine();

        // 2. Créer la relation correcte : Ahmed et Leila sont frère et sœur
        $this->info('2️⃣ CRÉATION DE LA RELATION CORRECTE :');

        if ($leila && $ahmed) {
            // Vérifier si la relation existe déjà
            $existingRelation = FamilyRelationship::where(function($query) use ($leila, $ahmed) {
                $query->where('user_id', $leila->id)->where('related_user_id', $ahmed->id);
            })->orWhere(function($query) use ($leila, $ahmed) {
                $query->where('user_id', $ahmed->id)->where('related_user_id', $leila->id);
            })->first();

            if (!$existingRelation) {
                // Créer la relation Leila → Ahmed (Frère)
                $brotherType = RelationshipType::where('code', 'brother')->first();
                $sisterType = RelationshipType::where('code', 'sister')->first();

                if ($brotherType && $sisterType) {
                    // Leila → Ahmed : Frère
                    FamilyRelationship::create([
                        'user_id' => $leila->id,
                        'related_user_id' => $ahmed->id,
                        'relationship_type_id' => $brotherType->id,
                        'status' => 'accepted',
                        'created_automatically' => true
                    ]);

                    // Ahmed → Leila : Sœur
                    FamilyRelationship::create([
                        'user_id' => $ahmed->id,
                        'related_user_id' => $leila->id,
                        'relationship_type_id' => $sisterType->id,
                        'status' => 'accepted',
                        'created_automatically' => true
                    ]);

                    $this->line("   ✅ Relations créées :");
                    $this->line("      👩 Leila → Ahmed : Frère");
                    $this->line("      👨 Ahmed → Leila : Sœur");
                    $fixedCount += 2;
                } else {
                    $this->error("   ❌ Types de relations brother/sister non trouvés");
                }
            } else {
                $this->line("   ℹ️ Relation déjà existante entre Leila et Ahmed");
            }
        }
        $this->newLine();

        // 3. Nettoyer toutes les suggestions incohérentes similaires
        $this->info('3️⃣ NETTOYAGE DES SUGGESTIONS INCOHÉRENTES :');

        $inconsistentSuggestions = Suggestion::where('suggested_relation_code', 'grandfather_paternal')
            ->orWhere('suggested_relation_code', 'grandmother_paternal')
            ->orWhere('suggested_relation_code', 'grandfather_maternal')
            ->orWhere('suggested_relation_code', 'grandmother_maternal')
            ->get();

        $cleanedCount = 0;
        foreach ($inconsistentSuggestions as $suggestion) {
            $user = $suggestion->user;
            $suggestedUser = $suggestion->suggestedUser;

            if ($user && $suggestedUser) {
                // Vérifier s'ils ont des parents communs (donc frères/sœurs)
                $commonParents = $this->findCommonParents($user, $suggestedUser);

                if ($commonParents->isNotEmpty()) {
                    $this->line("   🗑️ Suppression suggestion incohérente : {$user->name} → {$suggestedUser->name} ({$suggestion->suggested_relation_code})");
                    $this->line("      Raison : Parents communs détectés - ils sont frères/sœurs");
                    $suggestion->delete();
                    $cleanedCount++;
                }
            }
        }

        $this->line("   ✅ {$cleanedCount} suggestions incohérentes supprimées");
        $fixedCount += $cleanedCount;
        $this->newLine();

        // 4. Améliorer la logique de validation
        $this->info('4️⃣ VALIDATION DES SUGGESTIONS RESTANTES :');

        $allSuggestions = Suggestion::where('status', 'pending')->get();
        $validatedCount = 0;

        foreach ($allSuggestions as $suggestion) {
            $user = $suggestion->user;
            $suggestedUser = $suggestion->suggestedUser;
            $relationCode = $suggestion->suggested_relation_code;

            if ($user && $suggestedUser && $relationCode && $this->isGenerationInconsistent($user, $suggestedUser, $relationCode)) {
                $this->line("   🔍 Suggestion potentiellement incohérente : {$user->name} → {$suggestedUser->name} ({$relationCode})");
                $validatedCount++;
            }
        }

        $this->line("   ℹ️ {$validatedCount} suggestions nécessitent une révision manuelle");
        $this->newLine();

        $this->info("🎉 CORRECTION TERMINÉE !");
        $this->line("   Total des corrections : {$fixedCount}");
        $this->line("   Suggestions nettoyées : {$cleanedCount}");
        $this->line("   Suggestions à réviser : {$validatedCount}");

        return 0;
    }

    private function findCommonParents(User $user1, User $user2)
    {
        $user1Parents = FamilyRelationship::where('user_id', $user1->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->with('relatedUser')
            ->get()
            ->pluck('related_user_id');

        $user2Parents = FamilyRelationship::where('user_id', $user2->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->with('relatedUser')
            ->get()
            ->pluck('related_user_id');

        return $user1Parents->intersect($user2Parents);
    }

    private function isGenerationInconsistent(User $user, User $suggestedUser, string $relationCode): bool
    {
        $grandparentRelations = ['grandfather_paternal', 'grandmother_paternal', 'grandfather_maternal', 'grandmother_maternal'];

        if (in_array($relationCode, $grandparentRelations)) {
            // Vérifier s'ils ont des parents communs (même génération)
            return $this->findCommonParents($user, $suggestedUser)->isNotEmpty();
        }

        return false;
    }
}
