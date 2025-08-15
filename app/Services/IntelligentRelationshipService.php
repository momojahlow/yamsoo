<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class IntelligentRelationshipService
{
    /**
     * Règles de déduction automatique des relations familiales
     * Utilise le service centralisé RelationshipRulesService
     */
    private array $relationshipRules;

    public function __construct()
    {
        $this->relationshipRules = RelationshipRulesService::getRelationshipRules();
    }

    /**
     * Déduire automatiquement les nouvelles relations basées sur une relation existante
     */
    public function deduceRelationships(User $user, User $relatedUser, string $relationshipCode): Collection
    {
        $deducedRelations = collect();

        // Obtenir toutes les relations existantes de l'utilisateur connecté (relatedUser)
        $existingRelations = FamilyRelationship::where('user_id', $relatedUser->id)
            ->where('status', 'accepted')
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        foreach ($existingRelations as $relation) {
            $otherUser = $relation->relatedUser;
            $relationFromRelatedUserToOther = $relation->relationshipType->name;

            // Éviter les auto-relations
            if ($otherUser->id === $user->id) {
                continue;
            }

            // Appliquer les règles de déduction
            $deducedRelation = $this->getDeducedRelation(
                $relationshipCode,
                $relationFromRelatedUserToOther,
                $user,
                $otherUser
            );

            if ($deducedRelation) {
                // Vérifier que cette relation n'existe pas déjà
                $existingDirectRelation = FamilyRelationship::where([
                    ['user_id', $user->id],
                    ['related_user_id', $otherUser->id]
                ])->orWhere([
                    ['user_id', $otherUser->id],
                    ['related_user_id', $user->id]
                ])->exists();

                if (!$existingDirectRelation) {
                    $relationshipType = RelationshipType::where('name', $deducedRelation)->first();
                    if ($relationshipType) {
                        $deducedRelations->push([
                            'user_id' => $user->id,
                            'related_user_id' => $otherUser->id,
                            'relationship_type_id' => $relationshipType->id,
                            'reason' => "Déduit via {$relatedUser->name}: {$relationshipCode} → {$relationFromRelatedUserToOther}",
                            'confidence' => 85
                        ]);
                    }
                }
            }
        }

        return $deducedRelations;
    }

    /**
     * Obtenir la relation déduite basée sur deux relations consécutives
     */
    private function getDeducedRelation(string $relation1, string $relation2, User $user, User $targetUser): ?string
    {
        // Vérifier si on a une règle pour cette combinaison
        if (isset($this->relationshipRules[$relation1][$relation2])) {
            return $this->relationshipRules[$relation1][$relation2];
        }

        return null;
    }

    /**
     * Créer automatiquement les relations déduites
     */
    public function createDeducedRelationships(Collection $deducedRelations): int
    {
        $created = 0;

        foreach ($deducedRelations as $relationData) {
            try {
                // Créer la relation principale
                FamilyRelationship::create([
                    'user_id' => $relationData['user_id'],
                    'related_user_id' => $relationData['related_user_id'],
                    'relationship_type_id' => $relationData['relationship_type_id'],
                    'status' => 'accepted',
                    'created_automatically' => true,
                    'accepted_at' => now(),
                ]);

                $created++;
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création de relation automatique', [
                    'relation_data' => $relationData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $created;
    }
}
