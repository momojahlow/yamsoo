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
        // ===== RELATIONS VIA CONJOINT =====

        // Relations via le MARI
        'husband' => [
            'father' => 'father_in_law',                // Père du mari = Beau-père
            'mother' => 'mother_in_law',                // Mère du mari = Belle-mère
            'brother' => 'brother_in_law',              // Frère du mari = Beau-frère
            'sister' => 'sister_in_law',                // Sœur du mari = Belle-sœur
            'son' => 'son',                             // Fils du mari = Fils (beau-fils devient fils)
            'daughter' => 'daughter',                   // Fille du mari = Fille (belle-fille devient fille)
        ],

        // Relations via l'ÉPOUSE
        'wife' => [
            'father' => 'father_in_law',                // Père de l'épouse = Beau-père
            'mother' => 'mother_in_law',                // Mère de l'épouse = Belle-mère
            'brother' => 'brother_in_law',              // Frère de l'épouse = Beau-frère
            'sister' => 'sister_in_law',                // Sœur de l'épouse = Belle-sœur
            'son' => 'son',                             // Fils de l'épouse = Fils (beau-fils devient fils)
            'daughter' => 'daughter',                   // Fille de l'épouse = Fille (belle-fille devient fille)
        ],

        // ===== RELATIONS VIA FRÈRES/SŒURS =====

        // Relations via le FRÈRE
        'brother' => [
            'wife' => 'sister_in_law',                  // Épouse du frère = Belle-sœur
            'son' => 'nephew',                          // Fils du frère = Neveu
            'daughter' => 'niece',                      // Fille du frère = Nièce
        ],

        // Relations via la SŒUR
        'sister' => [
            'husband' => 'brother_in_law',              // Mari de la sœur = Beau-frère
            'son' => 'nephew',                          // Fils de la sœur = Neveu
            'daughter' => 'niece',                      // Fille de la sœur = Nièce
        ],

        // ===== RELATIONS VIA PARENTS =====

        // Relations via le PÈRE
        'father' => [
            'son' => 'brother',                         // Fils du père = Frère
            'daughter' => 'sister',                     // Fille du père = Sœur
            'wife' => 'mother',                         // Épouse du père = Mère
            'brother' => 'uncle',                       // Frère du père = Oncle
            'sister' => 'aunt',                         // Sœur du père = Tante
        ],

        // Relations via la MÈRE
        'mother' => [
            'son' => 'brother',                         // Fils de la mère = Frère
            'daughter' => 'sister',                     // Fille de la mère = Sœur
            'husband' => 'father',                      // Mari de la mère = Père
            'brother' => 'uncle',                       // Frère de la mère = Oncle
            'sister' => 'aunt',                         // Sœur de la mère = Tante
        ],

        // ===== RELATIONS VIA ENFANTS =====

        // Relations via le FILS
        'son' => [
            'father' => 'husband',                      // Père du fils = Mari (de la mère)
            'mother' => 'wife',                         // Mère du fils = Épouse (du père)
            'brother' => 'son',                         // Frère du fils = Fils
            'sister' => 'daughter',                     // Sœur du fils = Fille
        ],

        // Relations via la FILLE
        'daughter' => [
            'father' => 'husband',                      // Père de la fille = Mari (de la mère)
            'mother' => 'wife',                         // Mère de la fille = Épouse (du père)
            'brother' => 'son',                         // Frère de la fille = Fils
            'sister' => 'daughter',                     // Sœur de la fille = Fille
        ],

        // ===== RELATIONS VIA ONCLES/TANTES =====

        // Relations via l'ONCLE
        'uncle' => [
            'son' => 'cousin',                          // Fils de l'oncle = Cousin
            'daughter' => 'cousin',                     // Fille de l'oncle = Cousine
        ],

        // Relations via la TANTE
        'aunt' => [
            'son' => 'cousin',                          // Fils de la tante = Cousin
            'daughter' => 'cousin',                     // Fille de la tante = Cousine
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

                // Vérifier que relationshipType existe
                if (!$existingRelation->relationshipType) {
                    continue;
                }

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

                // Debug : Afficher les relations analysées
                if (app()->runningInConsole()) {
                    echo "🔍 ANALYSE DÉDUCTION:\n";
                    echo "   User: {$user->name} (ID: {$user->id})\n";
                    echo "   Connector: {$relatedUser->name} (ID: {$relatedUser->id})\n";
                    echo "   Other: {$otherUser->name} (ID: {$otherUser->id})\n";
                    echo "   User → Connector: {$relationshipCode}\n";
                    echo "   Connector → Other: {$relationFromRelatedUserToOther}\n";
                }

                // Appliquer les règles de déduction dans les deux sens
                $deducedRelation = $this->getDeducedRelation(
                    $relationshipCode,
                    $relationFromRelatedUserToOther,
                    $user,
                    $otherUser
                );

                if (app()->runningInConsole()) {
                    echo "   Règle 1 ({$relationshipCode} → {$relationFromRelatedUserToOther}): " . ($deducedRelation ?: 'AUCUNE') . "\n";
                }

                // Si pas trouvé dans le premier sens, essayer l'inverse
                if (!$deducedRelation) {
                    $deducedRelation = $this->getDeducedRelation(
                        $relationFromRelatedUserToOther,
                        $relationshipCode,
                        $user,
                        $otherUser
                    );

                    if (app()->runningInConsole()) {
                        echo "   Règle 2 ({$relationFromRelatedUserToOther} → {$relationshipCode}): " . ($deducedRelation ?: 'AUCUNE') . "\n";
                    }
                }

                if ($deducedRelation) {
                    // Adapter la relation selon le genre de l'utilisateur
                    $adaptedRelation = $this->adaptRelationToGender($deducedRelation, $user);

                    if (app()->runningInConsole()) {
                        echo "   Relation adaptée au genre : {$deducedRelation} → {$adaptedRelation}\n";
                    }

                    // Vérifier que cette relation n'existe pas déjà
                    $existingDirectRelation = FamilyRelationship::where([
                        ['user_id', $user->id],
                        ['related_user_id', $otherUser->id]
                    ])->orWhere([
                        ['user_id', $otherUser->id],
                        ['related_user_id', $user->id]
                    ])->exists();

                    if (!$existingDirectRelation) {
                        $relationshipType = RelationshipType::where('name', $adaptedRelation)->first();

                        if (app()->runningInConsole()) {
                            echo "   Type de relation trouvé pour '{$adaptedRelation}': " . ($relationshipType ? 'OUI' : 'NON') . "\n";
                        }

                        if ($relationshipType) {
                            if (app()->runningInConsole()) {
                                echo "   ✅ CRÉATION DE LA RELATION: {$user->name} → {$otherUser->name} : {$adaptedRelation}\n";
                            }

                            // Créer automatiquement la relation déduite
                            FamilyRelationship::create([
                                'user_id' => $user->id,
                                'related_user_id' => $otherUser->id,
                                'relationship_type_id' => $relationshipType->id,
                                'status' => 'accepted',
                                'created_automatically' => true,
                                'accepted_at' => now(),
                            ]);

                            // Créer aussi la relation inverse
                            $inverseRelationCode = $this->getInverseRelationCode($deducedRelation, $otherUser, $user);
                            if ($inverseRelationCode) {
                                $inverseRelationType = RelationshipType::where('name', $inverseRelationCode)->first();
                                if ($inverseRelationType) {
                                    FamilyRelationship::create([
                                        'user_id' => $otherUser->id,
                                        'related_user_id' => $user->id,
                                        'relationship_type_id' => $inverseRelationType->id,
                                        'status' => 'accepted',
                                        'created_automatically' => true,
                                        'accepted_at' => now(),
                                    ]);
                                }
                            }

                            $deducedRelations->push([
                                'user_id' => $user->id,
                                'related_user_id' => $otherUser->id,
                                'relationship_type_id' => $relationshipType->id,
                                'reason' => "Déduit via {$relatedUser->name}: {$relationshipCode} → {$relationFromRelatedUserToOther}",
                                'confidence' => 85,
                                'created' => true
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

    /**
     * Adapter une relation selon le genre de l'utilisateur
     */
    private function adaptRelationToGender(string $relationCode, User $user): string
    {
        $userGender = $user->profile?->gender;

        // Si le genre n'est pas défini, essayer de le deviner par le nom
        if (!$userGender) {
            $maleNames = ['Ahmed', 'Youssef', 'Mohammed', 'Hassan', 'Omar', 'Karim', 'Adil', 'Rachid'];
            $femaleNames = ['Fatima', 'Amina', 'Leila', 'Nadia', 'Sara', 'Zineb', 'Hanae'];

            if (in_array($user->name, $maleNames)) {
                $userGender = 'male';
            } elseif (in_array($user->name, $femaleNames)) {
                $userGender = 'female';
            }
        }

        if (app()->runningInConsole()) {
            echo "   DEBUG GENRE: User {$user->name} a le genre: " . ($userGender ?: 'NON DÉFINI') . "\n";
        }

        // Adaptations basées sur le genre
        $genderAdaptations = [
            'sister_in_law' => [
                'male' => 'brother_in_law',
                'female' => 'sister_in_law'
            ],
            'brother_in_law' => [
                'male' => 'brother_in_law',
                'female' => 'sister_in_law'
            ],
            'cousin' => [
                'male' => 'cousin',
                'female' => 'cousin'
            ],
            'nephew' => [
                'male' => 'nephew',
                'female' => 'niece'
            ],
            'niece' => [
                'male' => 'nephew',
                'female' => 'niece'
            ],
        ];

        if (isset($genderAdaptations[$relationCode][$userGender])) {
            return $genderAdaptations[$relationCode][$userGender];
        }

        return $relationCode;
    }
}
