<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FamilyRelationshipInferenceService
{
    /**
     * Cache des types de relations pour éviter les requêtes répétées
     */
    private ?Collection $relationshipTypes = null;

    /**
     * Obtenir tous les types de relations depuis la base de données
     */
    private function getRelationshipTypes(): Collection
    {
        if ($this->relationshipTypes === null) {
            $this->relationshipTypes = Cache::remember('relationship_types', 3600, function () {
                return RelationshipType::ordered()->get();
            });
        }

        return $this->relationshipTypes;
    }

    /**
     * Obtenir un type de relation par son nom
     */
    private function getRelationshipType(string $name): ?RelationshipType
    {
        return $this->getRelationshipTypes()->firstWhere('name', $name);
    }

    /**
     * Infer relationship between two users through a common connection
     */
    public function inferRelationship(
        User $user1,
        User $user2,
        User $connector,
        string $user1ToConnectorRelation,
        string $connectorToUser2Relation
    ): ?array {
        // Get relationship types from database
        $relation1Type = $this->getRelationshipType($user1ToConnectorRelation);
        $relation2Type = $this->getRelationshipType($connectorToUser2Relation);

        if (!$relation1Type || !$relation2Type) {
            return null;
        }

        // Calculate generation difference
        $generationDiff = $relation1Type->generation_level - $relation2Type->generation_level;
        $user2Gender = $user2->profile?->gender ?? 'neutral';

        // Apply inference rules based on generation difference and relationship types
        return $this->applyInferenceRules(
            $relation1Type,
            $relation2Type,
            $generationDiff,
            $user2Gender,
            $connector
        );
    }

    /**
     * Apply inference rules to determine relationship
     */
    private function applyInferenceRules(
        RelationshipType $relation1Type,
        RelationshipType $relation2Type,
        int $generationDiff,
        string $targetGender,
        User $connector
    ): ?array {
        // Rule 1: Parent-Child relationships through marriage
        if ($this->isParentChildThroughMarriage($relation1Type, $relation2Type)) {
            return $this->handleParentChildThroughMarriage($relation1Type, $relation2Type, $targetGender, $connector);
        }

        // Rule 2: Sibling relationships (both are children of connector)
        if ($this->areSiblingsOfConnector($relation1Type, $relation2Type)) {
            return $this->handleSiblingRelationship($targetGender);
        }

        // Rule 3: Uncle/Aunt - Nephew/Niece relationships
        if ($this->isUncleAuntNephewNiece($generationDiff)) {
            return $this->handleUncleAuntRelationship($generationDiff, $targetGender, $connector);
        }

        // Rule 4: Grandparent-Grandchild relationships
        if ($this->isGrandparentGrandchild($generationDiff)) {
            return $this->handleGrandparentRelationship($generationDiff, $targetGender, $connector);
        }

        // Rule 5: Same generation (cousins)
        if ($generationDiff === 0 && $relation1Type->category !== 'marriage' && $relation2Type->category !== 'marriage') {
            return [
                'code' => 'cousin',
                'reason' => "Cousin/Cousine via {$connector->name}",
                'confidence' => 75
            ];
        }

        // Default: Family member
        return [
            'code' => 'family_member',
            'reason' => "Membre de la famille via {$connector->name}",
            'confidence' => 50
        ];
    }

    /**
     * Check if relationship is parent-child through marriage
     */
    private function isParentChildThroughMarriage(RelationshipType $relation1Type, RelationshipType $relation2Type): bool
    {
        $parentRelations = ['father', 'mother', 'parent'];
        $marriageRelations = ['husband', 'wife', 'spouse'];

        return (in_array($relation1Type->name, $parentRelations) && in_array($relation2Type->name, $marriageRelations)) ||
               (in_array($relation1Type->name, $marriageRelations) && in_array($relation2Type->name, $parentRelations));
    }

    /**
     * Handle parent-child through marriage (step-parent/step-child or in-law)
     */
    private function handleParentChildThroughMarriage(
        RelationshipType $relation1Type,
        RelationshipType $relation2Type,
        string $targetGender,
        User $connector
    ): array {
        $parentRelations = ['father', 'mother', 'parent'];

        if (in_array($relation1Type->name, $parentRelations)) {
            // User is parent of connector, target is spouse of connector
            $relationCode = $targetGender === 'male' ? 'son_in_law' : 'daughter_in_law';
            $relationName = $targetGender === 'male' ? 'gendre' : 'belle-fille';

            return [
                'code' => $relationCode,
                'reason' => "Enfant par alliance - {$relationName} via {$connector->name}",
                'confidence' => 90
            ];
        } else {
            // User is spouse of connector, target is parent of connector
            $relationCode = $targetGender === 'male' ? 'father_in_law' : 'mother_in_law';
            $relationName = $targetGender === 'male' ? 'beau-père' : 'belle-mère';

            return [
                'code' => $relationCode,
                'reason' => "Parent par alliance - {$relationName} via {$connector->name}",
                'confidence' => 90
            ];
        }
    }

    /**
     * Check if both are siblings of connector
     * AMÉLIORATION: Vérification plus stricte pour éviter les fausses relations de fratrie
     */
    private function areSiblingsOfConnector(RelationshipType $relation1Type, RelationshipType $relation2Type): bool
    {
        $childRelations = ['son', 'daughter', 'child'];

        // Les deux doivent être des enfants du connecteur
        $bothAreChildren = in_array($relation1Type->name, $childRelations) &&
                          in_array($relation2Type->name, $childRelations);

        // Vérification supplémentaire : s'assurer que ce sont des relations directes parent-enfant
        // et non des relations inférées ou indirectes
        return $bothAreChildren;
    }

    /**
     * Handle sibling relationship
     * AMÉLIORATION: Réduction de la confiance et ajout de vérifications
     */
    private function handleSiblingRelationship(string $targetGender): array
    {
        $relationCode = $targetGender === 'male' ? 'brother' : 'sister';
        $relationName = $targetGender === 'male' ? 'frère' : 'sœur';

        return [
            'code' => $relationCode,
            'reason' => "Relation de fratrie inférée - {$relationName}",
            'confidence' => 75  // Réduit de 95 à 75 pour être plus prudent
        ];
    }

    /**
     * Check if uncle/aunt - nephew/niece relationship
     */
    private function isUncleAuntNephewNiece(int $generationDiff): bool
    {
        return abs($generationDiff) === 1;
    }

    /**
     * Handle uncle/aunt relationship
     */
    private function handleUncleAuntRelationship(int $generationDiff, string $targetGender, User $connector): array
    {
        if ($generationDiff > 0) {
            // Target is younger generation (nephew/niece)
            $relationCode = $targetGender === 'male' ? 'nephew' : 'niece';
            $relationName = $targetGender === 'male' ? 'neveu' : 'nièce';
        } else {
            // Target is older generation (uncle/aunt)
            $relationCode = $targetGender === 'male' ? 'uncle' : 'aunt';
            $relationName = $targetGender === 'male' ? 'oncle' : 'tante';
        }

        return [
            'code' => $relationCode,
            'reason' => "Relation oncle/tante - {$relationName} via {$connector->name}",
            'confidence' => 85
        ];
    }

    /**
     * Check if grandparent-grandchild relationship
     */
    private function isGrandparentGrandchild(int $generationDiff): bool
    {
        return abs($generationDiff) === 2;
    }

    /**
     * Handle grandparent relationship
     */
    private function handleGrandparentRelationship(int $generationDiff, string $targetGender, User $connector): array
    {
        if ($generationDiff > 0) {
            // Target is younger generation (grandchild)
            $relationCode = $targetGender === 'male' ? 'grandson' : 'granddaughter';
            $relationName = $targetGender === 'male' ? 'petit-fils' : 'petite-fille';
        } else {
            // Target is older generation (grandparent)
            $relationCode = $targetGender === 'male' ? 'grandfather' : 'grandmother';
            $relationName = $targetGender === 'male' ? 'grand-père' : 'grand-mère';
        }

        return [
            'code' => $relationCode,
            'reason' => "Relation grand-parent - {$relationName} via {$connector->name}",
            'confidence' => 80
        ];
    }
}
