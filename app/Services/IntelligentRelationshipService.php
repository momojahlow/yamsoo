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
     * Règles de déduction automatique des relations familiales COMPLÈTES
     * Format: [relation_existante => [relation_intermediaire => nouvelle_relation]]
     */
    private array $relationshipRules = [
        // Relations via le PÈRE
        'father' => [
            'brother' => 'uncle_paternal',              // Frère du père = Oncle paternel
            'sister' => 'aunt_paternal',                // Sœur du père = Tante paternelle
            'father' => 'grandfather_paternal',         // Père du père = Grand-père paternel
            'mother' => 'grandmother_paternal',         // Mère du père = Grand-mère paternelle
            'son' => 'brother',                         // Fils du père = Frère
            'daughter' => 'sister',                     // Fille du père = Sœur
            'wife' => 'mother',                         // Épouse du père = Mère
            'husband' => 'father',                      // Mari du père = Père (cas rare)
            'grandfather_paternal' => 'great_grandfather_paternal', // Grand-père paternel du père = Arrière-grand-père paternel
            'grandmother_paternal' => 'great_grandmother_paternal', // Grand-mère paternelle du père = Arrière-grand-mère paternelle
            'grandfather_maternal' => 'great_grandfather_maternal', // Grand-père maternel du père = Arrière-grand-père maternel
            'grandmother_maternal' => 'great_grandmother_maternal', // Grand-mère maternelle du père = Arrière-grand-mère maternelle
            'uncle_paternal' => 'grandfather_paternal', // Oncle paternel du père = Grand-père paternel
            'aunt_paternal' => 'grandmother_paternal',  // Tante paternelle du père = Grand-mère paternelle
            'uncle_maternal' => 'grandfather_maternal', // Oncle maternel du père = Grand-père maternel
            'aunt_maternal' => 'grandmother_maternal',  // Tante maternelle du père = Grand-mère maternelle
        ],

        // Relations via la MÈRE
        'mother' => [
            'brother' => 'uncle_maternal',              // Frère de la mère = Oncle maternel
            'sister' => 'aunt_maternal',                // Sœur de la mère = Tante maternelle
            'father' => 'grandfather_maternal',         // Père de la mère = Grand-père maternel
            'mother' => 'grandmother_maternal',         // Mère de la mère = Grand-mère maternelle
            'son' => 'brother',                         // Fils de la mère = Frère
            'daughter' => 'sister',                     // Fille de la mère = Sœur
            'husband' => 'father',                      // Mari de la mère = Père
            'wife' => 'mother',                         // Épouse de la mère = Mère (cas rare)
            'grandfather_paternal' => 'great_grandfather_paternal', // Grand-père paternel de la mère = Arrière-grand-père paternel
            'grandmother_paternal' => 'great_grandmother_paternal', // Grand-mère paternelle de la mère = Arrière-grand-mère paternelle
            'grandfather_maternal' => 'great_grandfather_maternal', // Grand-père maternel de la mère = Arrière-grand-père maternel
            'grandmother_maternal' => 'great_grandmother_maternal', // Grand-mère maternelle de la mère = Arrière-grand-mère maternelle
            'uncle_paternal' => 'grandfather_paternal', // Oncle paternel de la mère = Grand-père paternel
            'aunt_paternal' => 'grandmother_paternal',  // Tante paternelle de la mère = Grand-mère paternelle
            'uncle_maternal' => 'grandfather_maternal', // Oncle maternel de la mère = Grand-père maternel
            'aunt_maternal' => 'grandmother_maternal',  // Tante maternelle de la mère = Grand-mère maternelle
        ],

        // Relations via le FILS
        'son' => [
            'brother' => 'son',                         // Frère du fils = Fils
            'sister' => 'daughter',                     // Sœur du fils = Fille
            'father' => 'self',                         // Père du fils = Moi
            'mother' => 'wife',                         // Mère du fils = Épouse
            'son' => 'grandson',                        // Fils du fils = Petit-fils
            'daughter' => 'granddaughter',              // Fille du fils = Petite-fille
            'wife' => 'daughter_in_law',                // Épouse du fils = Belle-fille
            'husband' => 'son_in_law',                  // Mari du fils = Gendre (cas rare)
            'grandson' => 'great_grandson',             // Petit-fils du fils = Arrière-petit-fils
            'granddaughter' => 'great_granddaughter',   // Petite-fille du fils = Arrière-petite-fille
        ],

        // Relations via la FILLE
        'daughter' => [
            'brother' => 'son',                         // Frère de la fille = Fils
            'sister' => 'daughter',                     // Sœur de la fille = Fille
            'father' => 'self',                         // Père de la fille = Moi
            'mother' => 'wife',                         // Mère de la fille = Épouse
            'son' => 'grandson',                        // Fils de la fille = Petit-fils
            'daughter' => 'granddaughter',              // Fille de la fille = Petite-fille
            'husband' => 'son_in_law',                  // Mari de la fille = Gendre
            'wife' => 'daughter_in_law',                // Épouse de la fille = Belle-fille (cas rare)
            'grandson' => 'great_grandson',             // Petit-fils de la fille = Arrière-petit-fils
            'granddaughter' => 'great_granddaughter',   // Petite-fille de la fille = Arrière-petite-fille
        ],

        // Relations via le FRÈRE
        'brother' => [
            'son' => 'nephew',                          // Fils du frère = Neveu
            'daughter' => 'niece',                      // Fille du frère = Nièce
            'wife' => 'sister_in_law',                  // Épouse du frère = Belle-sœur
            'father' => 'uncle_paternal',               // Si je suis frère de quelqu'un, je suis oncle paternel de ses enfants
            'mother' => 'uncle_paternal',               // Si je suis frère de quelqu'un, je suis oncle paternel de ses enfants
            'brother' => 'brother',                     // Frère du frère = Frère
            'sister' => 'sister',                       // Sœur du frère = Sœur
            'grandson' => 'nephew',                     // Petit-fils du frère = Neveu
            'granddaughter' => 'niece',                 // Petite-fille du frère = Nièce
        ],

        // Relations via la SŒUR
        'sister' => [
            'son' => 'nephew',                          // Fils de la sœur = Neveu
            'daughter' => 'niece',                      // Fille de la sœur = Nièce
            'husband' => 'brother_in_law',              // Mari de la sœur = Beau-frère
            'father' => 'father',                       // Père de la sœur = Père
            'mother' => 'mother',                       // Mère de la sœur = Mère
            'brother' => 'brother',                     // Frère de la sœur = Frère
            'sister' => 'sister',                       // Sœur de la sœur = Sœur
            'grandson' => 'nephew',                     // Petit-fils de la sœur = Neveu
            'granddaughter' => 'niece',                 // Petite-fille de la sœur = Nièce
        ],

        // Relations via le GRAND-PÈRE PATERNEL
        'grandfather_paternal' => [
            'son' => 'uncle_paternal',                  // Fils du grand-père paternel = Oncle paternel
            'daughter' => 'aunt_paternal',              // Fille du grand-père paternel = Tante paternelle
            'father' => 'great_grandfather_paternal',   // Père du grand-père paternel = Arrière-grand-père paternel
            'mother' => 'great_grandmother_paternal',   // Mère du grand-père paternel = Arrière-grand-mère paternelle
            'brother' => 'grandfather_paternal',        // Frère du grand-père paternel = Grand-père paternel
            'sister' => 'grandmother_paternal',         // Sœur du grand-père paternel = Grand-mère paternelle
        ],

        // Relations via la GRAND-MÈRE PATERNELLE
        'grandmother_paternal' => [
            'son' => 'uncle_paternal',                  // Fils de la grand-mère paternelle = Oncle paternel
            'daughter' => 'aunt_paternal',              // Fille de la grand-mère paternelle = Tante paternelle
            'father' => 'great_grandfather_paternal',   // Père de la grand-mère paternelle = Arrière-grand-père paternel
            'mother' => 'great_grandmother_paternal',   // Mère de la grand-mère paternelle = Arrière-grand-mère paternelle
            'brother' => 'grandfather_paternal',        // Frère de la grand-mère paternelle = Grand-père paternel
            'sister' => 'grandmother_paternal',         // Sœur de la grand-mère paternelle = Grand-mère paternelle
        ],

        // Relations via le GRAND-PÈRE MATERNEL
        'grandfather_maternal' => [
            'son' => 'uncle_maternal',                  // Fils du grand-père maternel = Oncle maternel
            'daughter' => 'aunt_maternal',              // Fille du grand-père maternel = Tante maternelle
            'father' => 'great_grandfather_maternal',   // Père du grand-père maternel = Arrière-grand-père maternel
            'mother' => 'great_grandmother_maternal',   // Mère du grand-père maternel = Arrière-grand-mère maternelle
            'brother' => 'grandfather_maternal',        // Frère du grand-père maternel = Grand-père maternel
            'sister' => 'grandmother_maternal',         // Sœur du grand-père maternel = Grand-mère maternelle
        ],

        // Relations via la GRAND-MÈRE MATERNELLE
        'grandmother_maternal' => [
            'son' => 'uncle_maternal',                  // Fils de la grand-mère maternelle = Oncle maternel
            'daughter' => 'aunt_maternal',              // Fille de la grand-mère maternelle = Tante maternelle
            'father' => 'great_grandfather_maternal',   // Père de la grand-mère maternelle = Arrière-grand-père maternel
            'mother' => 'great_grandmother_maternal',   // Mère de la grand-mère maternelle = Arrière-grand-mère maternelle
            'brother' => 'grandfather_maternal',        // Frère de la grand-mère maternelle = Grand-père maternel
            'sister' => 'grandmother_maternal',         // Sœur de la grand-mère maternelle = Grand-mère maternelle
        ],

        // Relations via l'ONCLE PATERNEL
        'uncle_paternal' => [
            'son' => 'cousin_paternal_m',               // Fils de l'oncle paternel = Cousin paternel
            'daughter' => 'cousin_paternal_f',          // Fille de l'oncle paternel = Cousine paternelle
            'father' => 'grandfather_paternal',         // Père de l'oncle paternel = Grand-père paternel
            'mother' => 'grandmother_paternal',         // Mère de l'oncle paternel = Grand-mère paternelle
            'brother' => 'uncle_paternal',              // Frère de l'oncle paternel = Oncle paternel
            'sister' => 'aunt_paternal',                // Sœur de l'oncle paternel = Tante paternelle
            'wife' => 'aunt_paternal',                  // Épouse de l'oncle paternel = Tante paternelle
        ],

        // Relations via la TANTE PATERNELLE
        'aunt_paternal' => [
            'son' => 'cousin_paternal_m',               // Fils de la tante paternelle = Cousin paternel
            'daughter' => 'cousin_paternal_f',          // Fille de la tante paternelle = Cousine paternelle
            'father' => 'grandfather_paternal',         // Père de la tante paternelle = Grand-père paternel
            'mother' => 'grandmother_paternal',         // Mère de la tante paternelle = Grand-mère paternelle
            'brother' => 'uncle_paternal',              // Frère de la tante paternelle = Oncle paternel
            'sister' => 'aunt_paternal',                // Sœur de la tante paternelle = Tante paternelle
            'husband' => 'uncle_paternal',              // Mari de la tante paternelle = Oncle paternel
        ],

        // Relations via l'ONCLE MATERNEL
        'uncle_maternal' => [
            'son' => 'cousin_maternal_m',               // Fils de l'oncle maternel = Cousin maternel
            'daughter' => 'cousin_maternal_f',          // Fille de l'oncle maternel = Cousine maternelle
            'father' => 'grandfather_maternal',         // Père de l'oncle maternel = Grand-père maternel
            'mother' => 'grandmother_maternal',         // Mère de l'oncle maternel = Grand-mère maternelle
            'brother' => 'uncle_maternal',              // Frère de l'oncle maternel = Oncle maternel
            'sister' => 'aunt_maternal',                // Sœur de l'oncle maternel = Tante maternelle
            'wife' => 'aunt_maternal',                  // Épouse de l'oncle maternel = Tante maternelle
        ],

        // Relations via la TANTE MATERNELLE
        'aunt_maternal' => [
            'son' => 'cousin_maternal_m',               // Fils de la tante maternelle = Cousin maternel
            'daughter' => 'cousin_maternal_f',          // Fille de la tante maternelle = Cousine maternelle
            'father' => 'grandfather_maternal',         // Père de la tante maternelle = Grand-père maternel
            'mother' => 'grandmother_maternal',         // Mère de la tante maternelle = Grand-mère maternelle
            'brother' => 'uncle_maternal',              // Frère de la tante maternelle = Oncle maternel
            'sister' => 'aunt_maternal',                // Sœur de la tante maternelle = Tante maternelle
            'husband' => 'uncle_maternal',              // Mari de la tante maternelle = Oncle maternel
        ],

        // Relations via le PETIT-FILS
        'grandson' => [
            'father' => 'son',                          // Père du petit-fils = Fils
            'mother' => 'daughter',                     // Mère du petit-fils = Fille
            'son' => 'great_grandson',                  // Fils du petit-fils = Arrière-petit-fils
            'daughter' => 'great_granddaughter',        // Fille du petit-fils = Arrière-petite-fille
            'brother' => 'grandson',                    // Frère du petit-fils = Petit-fils
            'sister' => 'granddaughter',                // Sœur du petit-fils = Petite-fille
        ],

        // Relations via la PETITE-FILLE
        'granddaughter' => [
            'father' => 'son',                          // Père de la petite-fille = Fils
            'mother' => 'daughter',                     // Mère de la petite-fille = Fille
            'son' => 'great_grandson',                  // Fils de la petite-fille = Arrière-petit-fils
            'daughter' => 'great_granddaughter',        // Fille de la petite-fille = Arrière-petite-fille
            'brother' => 'grandson',                    // Frère de la petite-fille = Petit-fils
            'sister' => 'granddaughter',                // Sœur de la petite-fille = Petite-fille
        ],
    ];

    /**
     * Déduire automatiquement les nouvelles relations basées sur une relation existante
     * Logique : Si A → B (relation1) et B → C (relation2), alors A → C (relation déduite)
     */
    public function deduceRelationships(User $user, User $relatedUser, string $relationshipCode): Collection
    {
        $deducedRelations = collect();

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
                $relationFromRelatedUserToOther = $existingRelation->relationshipType->code;
            } else {
                // otherUser → relatedUser, donc on inverse pour avoir relatedUser → otherUser
                $otherUser = $existingRelation->user;
                $relationFromRelatedUserToOther = $this->getInverseRelationCode(
                    $existingRelation->relationshipType->code,
                    $otherUser,
                    $relatedUser
                );
            }

            // Éviter les relations avec soi-même
            if (!$otherUser || $otherUser->id === $user->id) {
                continue;
            }

            // Appliquer les règles de déduction
            $deducedRelationCode = $this->getDeducedRelation($relationshipCode, $relationFromRelatedUserToOther, $user, $otherUser);

            if ($deducedRelationCode) {
                $deducedRelations->push([
                    'user_id' => $user->id,
                    'related_user_id' => $otherUser->id,
                    'relationship_code' => $deducedRelationCode,
                    'deduction_path' => "{$user->name} → {$relatedUser->name} ({$relationshipCode}) + {$relatedUser->name} → {$otherUser->name} ({$relationFromRelatedUserToOther}) = {$deducedRelationCode}"
                ]);
            }
        }

        return $deducedRelations;
    }

    /**
     * Obtenir la relation déduite basée sur deux relations consécutives
     */
    private function getDeducedRelation(string $relation1, string $relation2, User $user, User $targetUser): ?string
    {
        $userGender = $user->profile?->gender;
        $targetGender = $targetUser->profile?->gender;

        // Règles de déduction familiale
        $deductionRules = [
            // Si je suis le frère du père de quelqu'un, je suis son oncle paternel
            'brother' => [
                'father' => 'uncle_paternal',
                'mother' => 'uncle_maternal',
                'son' => 'nephew',
                'daughter' => 'niece',
            ],

            // Si je suis la sœur du père de quelqu'un, je suis sa tante paternelle
            'sister' => [
                'father' => 'aunt_paternal',
                'mother' => 'aunt_maternal',
                'son' => 'nephew',
                'daughter' => 'niece',
            ],

            // Si je suis le père du père de quelqu'un, je suis son grand-père paternel
            'father' => [
                'father' => 'grandfather_paternal',
                'mother' => 'grandfather_maternal',
                'son' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'daughter' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'brother' => 'uncle_paternal',
                'sister' => 'aunt_paternal',
            ],

            // Si je suis la mère du père de quelqu'un, je suis sa grand-mère paternelle
            'mother' => [
                'father' => 'grandmother_paternal',
                'mother' => 'grandmother_maternal',
                'son' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'daughter' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'brother' => 'uncle_maternal',
                'sister' => 'aunt_maternal',
            ],

            // Si je suis le fils du père de quelqu'un, je suis son frère
            'son' => [
                'father' => 'brother',
                'mother' => 'brother',
                'son' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'daughter' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
            ],

            // Si je suis la fille du père de quelqu'un, je suis sa sœur
            'daughter' => [
                'father' => 'sister',
                'mother' => 'sister',
                'son' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
                'daughter' => $targetGender === 'female' ? 'granddaughter' : 'grandson',
            ],
        ];

        return $deductionRules[$relation1][$relation2] ?? null;
    }

    /**
     * Obtenir le code de relation inverse avec gestion précise du genre
     */
    private function getInverseRelationCode(string $relationCode, User $user1, User $user2): ?string
    {
        $user2Gender = $user2->profile?->gender;

        // Relations avec adaptation selon le genre
        $genderBasedInverses = [
            // Relations parent-enfant
            'father' => $user2Gender === 'female' ? 'daughter' : 'son',
            'mother' => $user2Gender === 'female' ? 'daughter' : 'son',
            'son' => $user2Gender === 'female' ? 'mother' : 'father',
            'daughter' => $user2Gender === 'female' ? 'mother' : 'father',

            // Relations grands-parents - petits-enfants
            'grandfather_paternal' => $user2Gender === 'female' ? 'granddaughter' : 'grandson',
            'grandmother_paternal' => $user2Gender === 'female' ? 'granddaughter' : 'grandson',
            'grandfather_maternal' => $user2Gender === 'female' ? 'granddaughter' : 'grandson',
            'grandmother_maternal' => $user2Gender === 'female' ? 'granddaughter' : 'grandson',
            'grandson' => $user2Gender === 'female' ? 'grandmother_paternal' : 'grandfather_paternal',
            'granddaughter' => $user2Gender === 'female' ? 'grandmother_paternal' : 'grandfather_paternal',

            // Relations oncles/tantes - neveux/nièces
            'uncle_paternal' => $user2Gender === 'female' ? 'niece' : 'nephew',
            'aunt_paternal' => $user2Gender === 'female' ? 'niece' : 'nephew',
            'uncle_maternal' => $user2Gender === 'female' ? 'niece' : 'nephew',
            'aunt_maternal' => $user2Gender === 'female' ? 'niece' : 'nephew',
            'nephew' => $user2Gender === 'female' ? 'aunt_paternal' : 'uncle_paternal', // Simplifié
            'niece' => $user2Gender === 'female' ? 'aunt_paternal' : 'uncle_paternal', // Simplifié

            // Relations arrière-grands-parents - arrière-petits-enfants
            'great_grandfather_paternal' => $user2Gender === 'female' ? 'great_granddaughter' : 'great_grandson',
            'great_grandmother_paternal' => $user2Gender === 'female' ? 'great_granddaughter' : 'great_grandson',
            'great_grandfather_maternal' => $user2Gender === 'female' ? 'great_granddaughter' : 'great_grandson',
            'great_grandmother_maternal' => $user2Gender === 'female' ? 'great_granddaughter' : 'great_grandson',
            'great_grandson' => $user2Gender === 'female' ? 'great_grandmother_paternal' : 'great_grandfather_paternal',
            'great_granddaughter' => $user2Gender === 'female' ? 'great_grandmother_paternal' : 'great_grandfather_paternal',
        ];

        // Relations fixes (sans adaptation de genre)
        $fixedInverses = [
            'brother' => 'brother',
            'sister' => 'sister',
            'husband' => 'wife',
            'wife' => 'husband',
            'cousin_paternal_m' => 'cousin_paternal_m',
            'cousin_paternal_f' => 'cousin_paternal_f',
            'cousin_maternal_m' => 'cousin_maternal_m',
            'cousin_maternal_f' => 'cousin_maternal_f',
            'father_in_law' => 'son_in_law',
            'mother_in_law' => 'daughter_in_law',
            'son_in_law' => 'father_in_law',
            'daughter_in_law' => 'mother_in_law',
            'brother_in_law' => 'sister_in_law',
            'sister_in_law' => 'brother_in_law',
        ];

        // Vérifier d'abord les relations avec adaptation de genre
        if (isset($genderBasedInverses[$relationCode])) {
            return $genderBasedInverses[$relationCode];
        }

        // Puis les relations fixes
        if (isset($fixedInverses[$relationCode])) {
            return $fixedInverses[$relationCode];
        }

        return null;
    }

    /**
     * Créer automatiquement les relations déduites
     */
    public function createDeducedRelationships(Collection $deducedRelations): int
    {
        $created = 0;

        foreach ($deducedRelations as $relation) {
            // Vérifier que la relation n'existe pas déjà
            $exists = FamilyRelationship::where('user_id', $relation['user_id'])
                ->where('related_user_id', $relation['related_user_id'])
                ->exists();

            if (!$exists) {
                $relationshipType = RelationshipType::where('code', $relation['relationship_code'])->first();

                if ($relationshipType) {
                    FamilyRelationship::create([
                        'user_id' => $relation['user_id'],
                        'related_user_id' => $relation['related_user_id'],
                        'relationship_type_id' => $relationshipType->id,
                        'status' => 'accepted',
                        'created_automatically' => true
                    ]);

                    $created++;
                    Log::info("Relation automatique créée : " . $relation['deduction_path']);
                }
            }
        }

        return $created;
    }

    /**
     * Vérifier si deux utilisateurs ont déjà une relation
     */
    public function hasExistingRelationship(User $user1, User $user2): bool
    {
        return FamilyRelationship::where(function($query) use ($user1, $user2) {
            $query->where('user_id', $user1->id)->where('related_user_id', $user2->id);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('user_id', $user2->id)->where('related_user_id', $user1->id);
        })->exists();
    }

    /**
     * Obtenir les utilisateurs qui ne devraient pas apparaître dans les suggestions
     */
    public function getExcludedUsersForSuggestions(User $user): Collection
    {
        // Utilisateurs déjà en relation
        $relatedUserIds = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })->get()->map(function($relation) use ($user) {
            return $relation->user_id === $user->id
                ? $relation->related_user_id
                : $relation->user_id;
        });

        // Ajouter l'utilisateur lui-même
        $relatedUserIds->push($user->id);

        return $relatedUserIds;
    }
}
