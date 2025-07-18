<?php

namespace App\Services;

use App\Events\RelationshipAccepted;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Services\IntelligentRelationshipService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyRelationService
{
    protected IntelligentRelationshipService $intelligentRelationshipService;

    public function __construct(IntelligentRelationshipService $intelligentRelationshipService)
    {
        $this->intelligentRelationshipService = $intelligentRelationshipService;
    }
    public function getUserRelationships(User $user): Collection
    {
        // Récupérer TOUTES les relations où l'utilisateur est impliqué
        $directRelations = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

        // Récupérer aussi les relations inverses (où l'utilisateur est related_user_id)
        $inverseRelations = FamilyRelationship::where('related_user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

        // Combiner les deux collections
        $allRelations = $directRelations->merge($inverseRelations);

        // Supprimer les doublons basés sur les IDs des utilisateurs impliqués
        $uniqueRelations = $allRelations->unique(function ($relation) use ($user) {
            $otherUserId = $relation->user_id === $user->id ? $relation->related_user_id : $relation->user_id;
            return $otherUserId;
        });

        return $uniqueRelations;
    }

    public function createRelationshipRequest(
        User $requester,
        int $targetUserId,
        int $relationshipTypeId,
        string $message = '',
        string $motherName = null
    ): RelationshipRequest {

        // Vérifications préalables
        $targetUser = User::find($targetUserId);
        if (!$targetUser) {
            throw new \InvalidArgumentException('Utilisateur cible introuvable.');
        }

        $relationshipType = RelationshipType::find($relationshipTypeId);
        if (!$relationshipType) {
            throw new \InvalidArgumentException('Type de relation invalide.');
        }

        // Créer la demande avec vérification
        $request = RelationshipRequest::create([
            'requester_id' => $requester->id,
            'target_user_id' => $targetUserId,
            'relationship_type_id' => $relationshipTypeId,
            'message' => $message,
            'mother_name' => $motherName,
            'status' => 'pending',
        ]);

        // Vérifier que la création a réussi
        if (!$request->exists) {
            throw new \Exception('Échec de la création de la demande de relation.');
        }

        Log::info('RelationshipRequest créée', [
            'id' => $request->id,
            'requester_id' => $requester->id,
            'target_user_id' => $targetUserId
        ]);

        return $request;
    }

    public function acceptRelationshipRequest(RelationshipRequest $request): FamilyRelationship
    {
        $createdRelationship = null;

        DB::transaction(function () use ($request, &$createdRelationship) {
            $request->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Créer la relation familiale principale
            $createdRelationship = FamilyRelationship::create([
                'user_id' => $request->requester_id,
                'related_user_id' => $request->target_user_id,
                'relationship_type_id' => $request->relationship_type_id,
                'mother_name' => $request->mother_name,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Créer la relation inverse si nécessaire
            $requester = User::find($request->requester_id);
            $target = User::find($request->target_user_id);
            $inverseType = $this->getInverseRelationshipType($request->relationship_type_id, $requester, $target);
            if ($inverseType) {
                FamilyRelationship::create([
                    'user_id' => $request->target_user_id,
                    'related_user_id' => $request->requester_id,
                    'relationship_type_id' => $inverseType->id,
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);
            }

            // Les déductions se feront après la transaction pour avoir toutes les relations de base
        });

        // Maintenant que toutes les relations de base sont créées, déduire les relations automatiques
        $requester = User::find($request->requester_id);
        $target = User::find($request->target_user_id);
        $relationshipType = RelationshipType::find($request->relationship_type_id);

        if ($relationshipType && $requester && $target) {
            // Déduire les relations pour le demandeur
            $deducedForRequester = $this->intelligentRelationshipService->deduceRelationships(
                $requester,
                $target,
                $relationshipType->code
            );
            $this->intelligentRelationshipService->createDeducedRelationships($deducedForRequester);

            // Déduire les relations pour la cible (relation inverse)
            $inverseType = $this->getInverseRelationshipType($request->relationship_type_id, $requester, $target);
            if ($inverseType) {
                $deducedForTarget = $this->intelligentRelationshipService->deduceRelationships(
                    $target,
                    $requester,
                    $inverseType->code
                );
                $this->intelligentRelationshipService->createDeducedRelationships($deducedForTarget);
            }
        }

        // Déclencher l'événement pour générer des suggestions familiales
        if ($requester && $target) {
            event(new RelationshipAccepted($requester, $target, $request));
        }

        return $createdRelationship;
    }

    /**
     * Obtenir les statistiques familiales d'un utilisateur
     */
    public function getFamilyStatistics(User $user): array
    {
        $relationships = $this->getUserRelationships($user);

        $statistics = [
            'total_relatives' => $relationships->count(),
            'by_type' => [],
            'by_generation' => [
                'ancestors' => 0,
                'same_generation' => 0,
                'descendants' => 0,
            ],
            'automatic_relations' => $relationships->where('created_automatically', true)->count(),
            'manual_relations' => $relationships->where('created_automatically', false)->count(),
        ];

        // Compter par type de relation
        foreach ($relationships as $relationship) {
            $type = $relationship->relationshipType->name_fr;
            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;

            // Classer par génération
            $code = $relationship->relationshipType->code;
            if (in_array($code, ['father', 'mother', 'grandfather_paternal', 'grandmother_paternal', 'grandfather_maternal', 'grandmother_maternal', 'uncle_paternal', 'aunt_paternal', 'uncle_maternal', 'aunt_maternal'])) {
                $statistics['by_generation']['ancestors']++;
            } elseif (in_array($code, ['brother', 'sister', 'husband', 'wife', 'cousin_paternal_m', 'cousin_paternal_f', 'cousin_maternal_m', 'cousin_maternal_f'])) {
                $statistics['by_generation']['same_generation']++;
            } elseif (in_array($code, ['son', 'daughter', 'grandson', 'granddaughter', 'nephew', 'niece'])) {
                $statistics['by_generation']['descendants']++;
            }
        }

        return $statistics;
    }

    public function rejectRelationshipRequest(RelationshipRequest $request): void
    {
        $request->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    public function getPendingRequests(User $user): Collection
    {
        return RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['requester.profile', 'relationshipType'])
            ->get();
    }

    public function getRelationshipTypes(): Collection
    {
        return RelationshipType::all();
    }

    public function getFamilyTree(User $user): array
    {
        $relationships = $this->getUserRelationships($user);
        $tree = [];

        foreach ($relationships as $relationship) {
            $relatedUser = $relationship->user_id === $user->id
                ? $relationship->relatedUser
                : $relationship->user;

            $tree[] = [
                'id' => $relatedUser->id,
                'name' => $relatedUser->name,
                'relationship' => $relationship->relationshipType->name,
                'profile' => $relatedUser->profile,
                'relationship_id' => $relationship->id,
            ];
        }

        return $tree;
    }

    public function searchPotentialRelatives(User $user, string $query): Collection
    {
        return User::where('id', '!=', $user->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->whereNotIn('id', function ($subQuery) use ($user) {
                $subQuery->select('related_user_id')
                    ->from('family_relationships')
                    ->where('user_id', $user->id)
                    ->where('status', 'accepted');
            })
            ->with('profile')
            ->limit(10)
            ->get();
    }

    private function getInverseRelationshipType(int $relationshipTypeId, User $requester = null, User $target = null): ?RelationshipType
    {
        // Récupérer le type de relation actuel
        $currentType = RelationshipType::find($relationshipTypeId);
        if (!$currentType) {
            return null;
        }

        // Pour les relations parent-enfant, adapter selon le genre du demandeur
        if (in_array($currentType->code, ['son', 'daughter']) && $requester) {
            return $this->getParentRelationByGender($requester);
        }

        // Pour les relations enfant-parent, adapter selon le genre de la cible
        if (in_array($currentType->code, ['father', 'mother']) && $target) {
            return $this->getChildRelationByGender($target);
        }

        // Carte des relations inverses basée sur les codes (pour les autres relations)
        $inverseCodeMap = [
            'brother' => 'brother', // Frère -> Frère
            'sister' => 'sister',   // Sœur -> Sœur
            'husband' => 'wife',    // Mari -> Épouse
            'wife' => 'husband',    // Épouse -> Mari
        ];

        $inverseCode = $inverseCodeMap[$currentType->code] ?? null;
        if (!$inverseCode) {
            return null;
        }

        return RelationshipType::where('code', $inverseCode)->first();
    }

    /**
     * Retourne la relation parent appropriée selon le genre
     */
    private function getParentRelationByGender(User $parent): ?RelationshipType
    {
        $parentGender = $parent->profile?->gender;

        if ($parentGender === 'male') {
            return RelationshipType::where('code', 'father')->first();
        } elseif ($parentGender === 'female') {
            return RelationshipType::where('code', 'mother')->first();
        }

        // Par défaut, retourner père si le genre n'est pas défini
        return RelationshipType::where('code', 'father')->first();
    }

    /**
     * Retourne la relation enfant appropriée selon le genre
     */
    private function getChildRelationByGender(User $child): ?RelationshipType
    {
        $childGender = $child->profile?->gender;

        if ($childGender === 'male') {
            return RelationshipType::where('code', 'son')->first();
        } elseif ($childGender === 'female') {
            return RelationshipType::where('code', 'daughter')->first();
        }

        // Par défaut, retourner fils si le genre n'est pas défini
        return RelationshipType::where('code', 'son')->first();
    }

    public function deleteRelationship(FamilyRelationship $relationship): void
    {
        // Supprimer aussi la relation inverse
        FamilyRelationship::where('user_id', $relationship->related_user_id)
            ->where('related_user_id', $relationship->user_id)
            ->delete();

        $relationship->delete();
    }

}
