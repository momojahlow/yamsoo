<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use Illuminate\Support\Collection;

class IntelligentSuggestionService
{
    /**
     * Générer des suggestions intelligentes basées sur les relations existantes
     */
    public function generateIntelligentSuggestions(User $user): int
    {
        $suggestionsCreated = 0;

        // Obtenir toutes les relations de l'utilisateur
        $userRelations = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($userRelations as $relation) {
            $relatedUser = $relation->user_id === $user->id ? $relation->relatedUser : $relation->user;
            $relationCode = $relation->relationshipType->code;

            // Générer des suggestions basées sur cette relation
            $newSuggestions = $this->generateSuggestionsFromRelation($user, $relatedUser, $relationCode);
            $suggestionsCreated += $newSuggestions;
        }

        // Générer des suggestions basées sur les relations par alliance (belle-famille)
        $suggestionsCreated += $this->generateInLawRelationSuggestions($user);

        // Générer des suggestions basées sur les frères/sœurs
        $suggestionsCreated += $this->generateSiblingRelationSuggestions($user);

        return $suggestionsCreated;
    }

    /**
     * Générer des suggestions basées sur une relation spécifique
     */
    private function generateSuggestionsFromRelation(User $user, User $relatedUser, string $relationCode): int
    {
        $suggestionsCreated = 0;

        // Obtenir les relations du parent/conjoint/frère pour suggérer des relations étendues
        $extendedRelations = FamilyRelationship::where(function($query) use ($relatedUser) {
            $query->where('user_id', $relatedUser->id)
                  ->orWhere('related_user_id', $relatedUser->id);
        })->with(['user', 'relatedUser', 'relationshipType'])->get();

        foreach ($extendedRelations as $extendedRelation) {
            $potentialSuggestion = $extendedRelation->user_id === $relatedUser->id
                ? $extendedRelation->relatedUser
                : $extendedRelation->user;

            // Ne pas suggérer l'utilisateur lui-même
            if ($potentialSuggestion->id === $user->id) {
                continue;
            }

            // Vérifier si une relation existe déjà
            if ($this->relationExists($user, $potentialSuggestion)) {
                continue;
            }

            // Vérifier si une suggestion existe déjà
            if ($this->suggestionExists($user, $potentialSuggestion)) {
                continue;
            }

            // Déterminer le type de relation suggérée
            $suggestedRelation = $this->determineSuggestedRelation(
                $relationCode,
                $extendedRelation->relationshipType->code,
                $user,
                $relatedUser,
                $potentialSuggestion
            );

            if ($suggestedRelation) {
                $this->createSuggestion($user, $potentialSuggestion, $suggestedRelation);
                $suggestionsCreated++;
            }
        }

        return $suggestionsCreated;
    }

    /**
     * Générer des suggestions basées sur les relations communes (frères/sœurs)
     */
    private function generateSiblingRelationSuggestions(User $user): int
    {
        $suggestionsCreated = 0;

        // Trouver les personnes qui partagent les mêmes parents que l'utilisateur
        $userParents = FamilyRelationship::where('user_id', $user->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->with(['relatedUser', 'relationshipType'])
            ->get();

        foreach ($userParents as $parentRelation) {
            $parent = $parentRelation->relatedUser;

            // Trouver tous les enfants de ce parent
            $siblings = FamilyRelationship::where('related_user_id', $parent->id)
                ->whereHas('relationshipType', function($query) {
                    $query->whereIn('code', ['son', 'daughter']);
                })
                ->with(['user', 'relationshipType'])
                ->get();

            foreach ($siblings as $siblingRelation) {
                $potentialSibling = $siblingRelation->user;

                // Ne pas suggérer l'utilisateur lui-même
                if ($potentialSibling->id === $user->id) {
                    continue;
                }

                // Vérifier si une relation existe déjà
                if ($this->relationExists($user, $potentialSibling)) {
                    continue;
                }

                // Vérifier si une suggestion existe déjà
                if ($this->suggestionExists($user, $potentialSibling)) {
                    continue;
                }

                // Déterminer le type de relation (frère/sœur)
                $siblingGender = $potentialSibling->profile?->gender;
                $relationCode = $siblingGender === 'female' ? 'sister' : 'brother';
                $relationName = $siblingGender === 'female' ? 'Sœur' : 'Frère';

                $suggestedRelation = [
                    'relation_code' => $relationCode,
                    'relation_name' => $relationName
                ];

                // Vérifier la cohérence genre/relation
                if ($this->isGenderConsistent($potentialSibling, $relationCode)) {
                    $this->createSuggestion($user, $potentialSibling, $suggestedRelation, "Même parent : {$parent->name}");
                    $suggestionsCreated++;
                }
            }
        }

        return $suggestionsCreated;
    }

    /**
     * Générer des suggestions basées sur les conjoints (belle-famille)
     */
    private function generateInLawRelationSuggestions(User $user): int
    {
        $suggestionsCreated = 0;

        // Trouver le conjoint de l'utilisateur (dans les deux sens)
        $spouseRelation = FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->whereHas('relationshipType', function($query) {
            $query->whereIn('code', ['husband', 'wife']);
        })
        ->with(['relatedUser', 'relationshipType', 'user'])
        ->first();

        if (!$spouseRelation) {
            return 0; // Pas de conjoint
        }

        // Déterminer qui est le conjoint
        $spouse = $spouseRelation->user_id === $user->id
            ? $spouseRelation->relatedUser
            : $spouseRelation->user;

        // Obtenir TOUTE la famille du conjoint pour suggérer les relations par alliance
        $spouseFamilyRelations = FamilyRelationship::where(function($query) use ($spouse) {
            $query->where('user_id', $spouse->id)
                  ->orWhere('related_user_id', $spouse->id);
        })
        ->with(['relatedUser', 'relationshipType', 'user'])
        ->get();

        foreach ($spouseFamilyRelations as $familyRelation) {
            $familyMember = $familyRelation->user_id === $spouse->id
                ? $familyRelation->relatedUser
                : $familyRelation->user;

            // Ne pas suggérer le conjoint lui-même ou l'utilisateur
            if ($familyMember->id === $spouse->id || $familyMember->id === $user->id) {
                continue;
            }

            // Vérifier si une relation existe déjà
            if ($this->relationExists($user, $familyMember)) {
                continue;
            }

            // Déterminer la relation du conjoint avec ce membre de famille
            // IMPORTANT: Nous voulons la relation du point de vue du conjoint vers le membre de famille
            // Pour déterminer la relation par alliance correcte
            $spouseRelationCode = null;

            if ($familyRelation->user_id === $spouse->id) {
                // Le conjoint est l'utilisateur de la relation: spouse -> familyMember
                $spouseRelationCode = $familyRelation->relationshipType->code;
            } else {
                // Le membre de famille est l'utilisateur de la relation: familyMember -> spouse
                // Nous devons inverser pour avoir spouse -> familyMember
                $spouseRelationCode = $this->getInverseRelationCode(
                    $familyRelation->relationshipType->code,
                    $familyMember,
                    $spouse
                );
            }

            // Vérifier si une suggestion existe déjà pour cette relation spécifique
            $existingSuggestion = $this->getSuggestion($user, $familyMember);
            if ($existingSuggestion) {
                // Si la nouvelle relation est plus appropriée (relation par alliance vs relation directe)
                // on met à jour la suggestion
                $inLawRelation = $this->determineInLawRelation($spouseRelationCode, $familyMember);
                if ($inLawRelation && $this->isInLawRelationBetter($existingSuggestion->suggested_relation_code, $inLawRelation['relation_code'])) {
                    $existingSuggestion->update([
                        'suggested_relation_code' => $inLawRelation['relation_code'],
                        'message' => "Famille de votre conjoint : {$spouse->name}"
                    ]);
                }
                continue;
            }

            // Déterminer la relation par alliance
            $inLawRelation = $this->determineInLawRelation($spouseRelationCode, $familyMember);

            if ($inLawRelation) {
                $this->createSuggestion(
                    $user,
                    $familyMember,
                    $inLawRelation,
                    "Famille de votre conjoint : {$spouse->name}"
                );
                $suggestionsCreated++;
            }
        }

        return $suggestionsCreated;
    }

    /**
     * Déterminer la relation par alliance basée sur la relation du conjoint
     */
    private function determineInLawRelation(string $spouseRelationCode, User $familyMember): ?array
    {

        $inLawMappings = [
            'father' => ['relation_code' => 'father_in_law', 'relation_name' => 'Beau-père'],
            'mother' => ['relation_code' => 'mother_in_law', 'relation_name' => 'Belle-mère'],
            'brother' => ['relation_code' => 'brother_in_law', 'relation_name' => 'Beau-frère'],
            'sister' => ['relation_code' => 'sister_in_law', 'relation_name' => 'Belle-sœur'],
            // Les enfants du conjoint sont les enfants de l'utilisateur dans un mariage
            'son' => [
                'relation_code' => 'son',
                'relation_name' => 'Fils'
            ],
            'daughter' => [
                'relation_code' => 'daughter',
                'relation_name' => 'Fille'
            ]
        ];

        return $inLawMappings[$spouseRelationCode] ?? null;
    }

    /**
     * Obtenir le code de relation inverse
     */
    private function getInverseRelationCode(string $relationCode, User $fromUser, User $toUser): string
    {
        $fromGender = $fromUser->profile?->gender;
        $toGender = $toUser->profile?->gender;

        $inverseMappings = [
            'father' => $fromGender === 'female' ? 'daughter' : 'son',
            'mother' => $fromGender === 'female' ? 'daughter' : 'son',
            'son' => $toGender === 'female' ? 'mother' : 'father',
            'daughter' => $toGender === 'female' ? 'mother' : 'father',
            'brother' => $fromGender === 'female' ? 'sister' : 'brother',
            'sister' => $fromGender === 'female' ? 'sister' : 'brother',
            'husband' => 'wife',
            'wife' => 'husband',
            'uncle_paternal' => $fromGender === 'female' ? 'niece' : 'nephew',
            'aunt_paternal' => $fromGender === 'female' ? 'niece' : 'nephew',
            'uncle_maternal' => $fromGender === 'female' ? 'niece' : 'nephew',
            'aunt_maternal' => $fromGender === 'female' ? 'niece' : 'nephew',
            'nephew' => $toGender === 'female' ? 'aunt_paternal' : 'uncle_paternal',
            'niece' => $toGender === 'female' ? 'aunt_paternal' : 'uncle_paternal',
        ];

        return $inverseMappings[$relationCode] ?? $relationCode;
    }

    /**
     * Déterminer le type de relation suggérée basé sur la logique familiale
     */
    private function determineSuggestedRelation(string $userRelation, string $extendedRelation, User $user, User $relatedUser, User $potentialSuggestion): ?array
    {
        // Logique familiale intelligente
        $suggestions = [
            // Si l'utilisateur a un conjoint, suggérer la famille du conjoint
            'husband' => [
                'father' => ['relation_code' => 'father_in_law', 'relation_name' => 'Beau-père'],
                'mother' => ['relation_code' => 'mother_in_law', 'relation_name' => 'Belle-mère'],
                'brother' => ['relation_code' => 'brother_in_law', 'relation_name' => 'Beau-frère'],
                'sister' => ['relation_code' => 'sister_in_law', 'relation_name' => 'Belle-sœur'],
                'son' => ['relation_code' => 'son', 'relation_name' => 'Fils'],
                'daughter' => ['relation_code' => 'daughter', 'relation_name' => 'Fille'],
            ],
            'wife' => [
                'father' => ['relation_code' => 'father_in_law', 'relation_name' => 'Beau-père'],
                'mother' => ['relation_code' => 'mother_in_law', 'relation_name' => 'Belle-mère'],
                'brother' => ['relation_code' => 'brother_in_law', 'relation_name' => 'Beau-frère'],
                'sister' => ['relation_code' => 'sister_in_law', 'relation_name' => 'Belle-sœur'],
                'son' => ['relation_code' => 'son', 'relation_name' => 'Fils'],
                'daughter' => ['relation_code' => 'daughter', 'relation_name' => 'Fille'],
            ],
            // Si l'utilisateur a un père, suggérer les frères/sœurs du père comme oncles/tantes
            'father' => [
                'brother' => ['relation_code' => 'uncle_paternal', 'relation_name' => 'Oncle paternel'],
                'sister' => ['relation_code' => 'aunt_paternal', 'relation_name' => 'Tante paternelle'],
                'father' => ['relation_code' => 'grandfather_paternal', 'relation_name' => 'Grand-père paternel'],
                'mother' => ['relation_code' => 'grandmother_paternal', 'relation_name' => 'Grand-mère paternelle'],
            ],
            // Si l'utilisateur a une mère, suggérer les frères/sœurs de la mère comme oncles/tantes
            'mother' => [
                'brother' => ['relation_code' => 'uncle_maternal', 'relation_name' => 'Oncle maternel'],
                'sister' => ['relation_code' => 'aunt_maternal', 'relation_name' => 'Tante maternelle'],
                'father' => ['relation_code' => 'grandfather_maternal', 'relation_name' => 'Grand-père maternel'],
                'mother' => ['relation_code' => 'grandmother_maternal', 'relation_name' => 'Grand-mère maternelle'],
            ],
            // Si l'utilisateur a des frères/sœurs, suggérer leurs conjoints
            'brother' => [
                'wife' => ['relation_code' => 'sister_in_law', 'relation_name' => 'Belle-sœur'],
                'son' => ['relation_code' => 'nephew', 'relation_name' => 'Neveu'],
                'daughter' => ['relation_code' => 'niece', 'relation_name' => 'Nièce'],
            ],
            'sister' => [
                'husband' => ['relation_code' => 'brother_in_law', 'relation_name' => 'Beau-frère'],
                'son' => ['relation_code' => 'nephew', 'relation_name' => 'Neveu'],
                'daughter' => ['relation_code' => 'niece', 'relation_name' => 'Nièce'],
            ],
        ];

        $suggestedRelation = $suggestions[$userRelation][$extendedRelation] ?? null;

        // Vérifier la cohérence genre/relation
        if ($suggestedRelation && !$this->isGenderConsistent($potentialSuggestion, $suggestedRelation['relation_code'])) {
            return null; // Relation incohérente avec le genre
        }

        // Vérifier la cohérence générationnelle
        if ($suggestedRelation && !$this->isGenerationConsistent($user, $relatedUser, $potentialSuggestion, $suggestedRelation['relation_code'])) {
            return null; // Relation incohérente avec la génération
        }

        return $suggestedRelation;
    }

    /**
     * Vérifier la cohérence générationnelle des relations
     */
    private function isGenerationConsistent(User $user, User $relatedUser, User $potentialSuggestion, string $relationCode): bool
    {
        // Relations grand-parent/petit-enfant nécessitent une vérification spéciale
        $grandparentRelations = ['grandfather_paternal', 'grandmother_paternal', 'grandfather_maternal', 'grandmother_maternal'];

        if (in_array($relationCode, $grandparentRelations)) {
            // Vérifier si l'utilisateur et la suggestion potentielle ont des parents communs
            // Si oui, ils sont de la même génération et ne peuvent pas être grand-parent/petit-enfant
            $commonParents = $this->findCommonParents($user, $potentialSuggestion);

            if ($commonParents->isNotEmpty()) {
                return false; // Même génération, pas grand-parent/petit-enfant
            }
        }

        return true; // Relation cohérente
    }

    /**
     * Trouver les parents communs entre deux utilisateurs
     */
    private function findCommonParents(User $user1, User $user2): \Illuminate\Support\Collection
    {
        $user1Parents = FamilyRelationship::where('user_id', $user1->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->pluck('related_user_id');

        $user2Parents = FamilyRelationship::where('user_id', $user2->id)
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('code', ['father', 'mother']);
            })
            ->pluck('related_user_id');

        return $user1Parents->intersect($user2Parents);
    }

    /**
     * Vérifier si le genre de la personne est cohérent avec le type de relation
     */
    private function isGenderConsistent(User $user, string $relationCode): bool
    {
        $gender = $user->profile?->gender;

        // Relations qui nécessitent un genre masculin
        $maleRelations = [
            'father', 'father_in_law', 'grandfather_paternal', 'grandfather_maternal',
            'uncle_paternal', 'uncle_maternal', 'brother', 'brother_in_law',
            'husband', 'son', 'stepson', 'grandson', 'nephew'
        ];

        // Relations qui nécessitent un genre féminin
        $femaleRelations = [
            'mother', 'mother_in_law', 'grandmother_paternal', 'grandmother_maternal',
            'aunt_paternal', 'aunt_maternal', 'sister', 'sister_in_law',
            'wife', 'daughter', 'stepdaughter', 'granddaughter', 'niece'
        ];

        // Vérifier la cohérence
        if (in_array($relationCode, $maleRelations) && $gender !== 'male') {
            return false;
        }

        if (in_array($relationCode, $femaleRelations) && $gender !== 'female') {
            return false;
        }

        return true;
    }

    /**
     * Vérifier si une relation existe déjà entre deux utilisateurs
     */
    private function relationExists(User $user1, User $user2): bool
    {
        return FamilyRelationship::where(function($query) use ($user1, $user2) {
            $query->where('user_id', $user1->id)->where('related_user_id', $user2->id);
        })->orWhere(function($query) use ($user1, $user2) {
            $query->where('user_id', $user2->id)->where('related_user_id', $user1->id);
        })->exists();
    }

    /**
     * Vérifier si une suggestion existe déjà
     */
    private function suggestionExists(User $user, User $suggestedUser): bool
    {
        return Suggestion::where('user_id', $user->id)
            ->where('suggested_user_id', $suggestedUser->id)
            ->exists();
    }

    /**
     * Obtenir une suggestion existante
     */
    private function getSuggestion(User $user, User $suggestedUser): ?Suggestion
    {
        return Suggestion::where('user_id', $user->id)
            ->where('suggested_user_id', $suggestedUser->id)
            ->first();
    }

    /**
     * Déterminer si une relation par alliance est meilleure qu'une relation directe
     */
    private function isInLawRelationBetter(string $currentRelationCode, string $newRelationCode): bool
    {
        // Les relations par alliance sont généralement plus appropriées que les relations directes incorrectes
        $inLawRelations = ['father_in_law', 'mother_in_law', 'brother_in_law', 'sister_in_law', 'stepson', 'stepdaughter'];
        $directRelations = ['father', 'mother', 'brother', 'sister', 'son', 'daughter'];

        // Si la nouvelle relation est par alliance et l'ancienne est directe, préférer la nouvelle
        if (in_array($newRelationCode, $inLawRelations) && in_array($currentRelationCode, $directRelations)) {
            return true;
        }

        return false;
    }

    /**
     * Créer une nouvelle suggestion
     */
    private function createSuggestion(User $user, User $suggestedUser, array $relationData, ?string $message = null): void
    {
        Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $suggestedUser->id,
            'type' => 'intelligent', // Type de suggestion
            'suggested_relation_code' => $relationData['relation_code'],
            'suggested_relation_name' => $relationData['relation_name'],
            'status' => 'pending',
            'message' => $message ?? 'Suggestion intelligente basée sur les relations familiales existantes',
        ]);
    }

    /**
     * Générer des suggestions pour tous les utilisateurs
     */
    public function generateSuggestionsForAllUsers(): int
    {
        $totalSuggestions = 0;
        $users = User::all();

        foreach ($users as $user) {
            $suggestions = $this->generateIntelligentSuggestions($user);
            $totalSuggestions += $suggestions;
        }

        return $totalSuggestions;
    }
}
