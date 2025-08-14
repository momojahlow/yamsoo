<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;

class CleanIncorrectRelations extends Command
{
    protected $signature = 'clean:incorrect-relations {--force : Supprimer automatiquement sans confirmation}';
    protected $description = 'Nettoyer les relations familiales incorrectes';

    public function handle()
    {
        $this->info('🧹 NETTOYAGE DES RELATIONS INCORRECTES');
        $this->info('=====================================');
        $this->newLine();

        // Trouver toutes les relations de fratrie
        $siblingRelations = FamilyRelationship::whereHas('relationshipType', function($query) {
            $query->whereIn('name', ['brother', 'sister', 'sibling']);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        $this->info("🔍 Analyse de {$siblingRelations->count()} relations de fratrie...");
        $this->newLine();

        $incorrectRelations = [];

        foreach ($siblingRelations as $relation) {
            $user1 = $relation->user;
            $user2 = $relation->relatedUser;
            
            // Vérifier si cette relation de fratrie est justifiée
            if (!$this->hasSiblingJustification($user1, $user2)) {
                $incorrectRelations[] = $relation;
                $this->warn("❌ Relation incorrecte: {$user1->name} → {$user2->name} ({$relation->relationshipType->display_name_fr})");
            } else {
                $this->info("✅ Relation correcte: {$user1->name} → {$user2->name} ({$relation->relationshipType->display_name_fr})");
            }
        }

        $this->newLine();

        if (empty($incorrectRelations)) {
            $this->info('🎉 Aucune relation incorrecte trouvée !');
            return 0;
        }

        $this->warn("⚠️ " . count($incorrectRelations) . " relation(s) incorrecte(s) trouvée(s)");

        if ($this->option('force') || $this->confirm('Voulez-vous supprimer ces relations incorrectes ?')) {
            foreach ($incorrectRelations as $relation) {
                $this->line("   Suppression: {$relation->user->name} → {$relation->relatedUser->name}");
                $relation->delete();
            }
            
            $this->info('✅ Relations incorrectes supprimées avec succès !');
        } else {
            $this->info('❌ Suppression annulée');
        }

        return 0;
    }

    private function hasSiblingJustification($user1, $user2): bool
    {
        // Trouver les parents de user1
        $user1Parents = FamilyRelationship::where('user_id', $user1->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['father', 'mother']);
            })
            ->pluck('related_user_id')
            ->toArray();

        // Trouver les parents de user2
        $user2Parents = FamilyRelationship::where('user_id', $user2->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['father', 'mother']);
            })
            ->pluck('related_user_id')
            ->toArray();

        // Vérifier s'il y a au moins un parent commun
        $commonParents = array_intersect($user1Parents, $user2Parents);
        
        return !empty($commonParents);
    }
}
