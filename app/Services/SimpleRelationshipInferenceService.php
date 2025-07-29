<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SimpleRelationshipInferenceService
{
    /**
     * Règles de déduction simplifiées et CORRECTES
     * Basées sur la logique : Si A → B (relation1) et B → C (relation2), alors A → C (relation déduite)
     */
    private array $relationshipRules = [
        // Relations via le MARI
        'husband' => [
            'son' => 'son',                             // Fils du mari = Fils (beau-fils devient fils)
            'daughter' => 'daughter',                   // Fille du mari = Fille (belle-fille devient fille)
            'father' => 'father_in_law',                // Père du mari = Beau-père
            'mother' => 'mother_in_law',                // Mère du mari = Belle-mère
        ],

        // Relations via l'ÉPOUSE
        'wife' => [
            'son' => 'son',                             // Fils de l'épouse = Fils (beau-fils devient fils)
            'daughter' => 'daughter',                   // Fille de l'épouse = Fille (belle-fille devient fille)
            'father' => 'father_in_law',                // Père de l'épouse = Beau-père
            'mother' => 'mother_in_law',                // Mère de l'épouse = Belle-mère
        ],

        // Relations via le PÈRE
        'father' => [
            'son' => 'brother',                         // Fils du père = Frère
            'daughter' => 'sister',                     // Fille du père = Sœur
            'wife' => 'mother',                         // Épouse du père = Mère
        ],

        // Relations via la MÈRE
        'mother' => [
            'son' => 'brother',                         // Fils de la mère = Frère
            'daughter' => 'sister',                     // Fille de la mère = Sœur
            'husband' => 'father',                      // Mari de la mère = Père
        ],

        // Relations via le FILS (AJOUTÉ)
        'son' => [
            'father' => 'husband',                      // Père du fils = Mari (de la mère)
            'mother' => 'wife',                         // Mère du fils = Épouse (du père)
            'brother' => 'son',                         // Frère du fils = Fils
            'sister' => 'daughter',                     // Sœur du fils = Fille
        ],

        // Relations via la FILLE (AJOUTÉ)
        'daughter' => [
            'father' => 'husband',                      // Père de la fille = Mari (de la mère)
            'mother' => 'wife',                         // Mère de la fille = Épouse (du père)
            'brother' => 'son',                         // Frère de la fille = Fils
            'sister' => 'daughter',                     // Sœur de la fille = Fille
        ],
    ];

    /**
     * Déduire automatiquement les nouvelles relations basées sur une relation existante
     */
    public function deduceRelationships(User $user, User $relatedUser, string $relationshipCode): Collection
    {
        $deducedRelations = collect();

        try {
            // Obtenir toutes les relations où relatedUser est impliqué
            $existingRelations = FamilyRelationship::where(function($query) use ($relatedUser) {
                $query->where('user_id', $relatedUser->id)
                      ->orWhere('related_user_id', $relatedUser->id);
            })->with(['relationshipType', 'user', 'relatedUser'])->get();

            foreach ($existingRelations as $existingRelation) {
                $otherUser = null;
                $relationFromRelatedUserToOther = null;

                // Déterminer la relation de relatedUser vers l'autre personne
                if ($existingRelation->user_id === $relatedUser->id) {
                    // relatedUser → otherUser
                    $otherUser = $existingRelation->relatedUser;
                    $relationFromRelatedUserToOther = $existingRelation->relationshipType->name;
                } else {
                    // otherUser → relatedUser, donc on inverse pour avoir relatedUser → otherUser
                    $otherUser = $existingRelation->user;
                    $relationFromRelatedUserToOther = $this->getInverseRelationCode(
                        $existingRelation->relationshipType->name,
                        $otherUser,
                        $relatedUser
                    );
                }

                // Éviter les relations avec soi-même
                if (!$otherUser || $otherUser->id === $user->id) {
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

        } catch (\Exception $e) {
            Log::error('Erreur lors de la déduction des relations', [
                'user_id' => $user->id,
                'related_user_id' => $relatedUser->id,
                'relationship_code' => $relationshipCode,
                'error' => $e->getMessage()
            ]);
        }

        return $deducedRelations;
    }

    /**
     * Obtenir la relation déduite basée sur deux relations
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
     * Obtenir le code de relation inverse
     */
    private function getInverseRelationCode(string $relationCode, User $user1, User $user2): ?string
    {
        $user2Gender = $user2->profile?->gender;

        $inverseMap = [
            'father' => $user2Gender === 'male' ? 'son' : 'daughter',
            'mother' => $user2Gender === 'male' ? 'son' : 'daughter',
            'son' => 'father',
            'daughter' => 'mother',
            'husband' => 'wife',
            'wife' => 'husband',
            'brother' => $user2Gender === 'male' ? 'brother' : 'sister',
            'sister' => $user2Gender === 'male' ? 'brother' : 'sister',
            'uncle' => $user2Gender === 'male' ? 'nephew' : 'niece',
            'aunt' => $user2Gender === 'male' ? 'nephew' : 'niece',
            'nephew' => 'uncle',
            'niece' => 'aunt',
            'grandfather' => $user2Gender === 'male' ? 'grandson' : 'granddaughter',
            'grandmother' => $user2Gender === 'male' ? 'grandson' : 'granddaughter',
            'grandson' => 'grandfather',
            'granddaughter' => 'grandmother',
            'cousin' => 'cousin',
            'father_in_law' => $user2Gender === 'male' ? 'son_in_law' : 'daughter_in_law',
            'mother_in_law' => $user2Gender === 'male' ? 'son_in_law' : 'daughter_in_law',
            'son_in_law' => 'father_in_law',
            'daughter_in_law' => 'mother_in_law',
        ];

        return $inverseMap[$relationCode] ?? null;
    }
}
