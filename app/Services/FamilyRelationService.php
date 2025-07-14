<?php

namespace App\Services;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FamilyRelationService
{
    public function getUserRelationships(User $user): Collection
    {
        return FamilyRelationship::where('user_id', $user->id)
            ->orWhere('related_user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();
    }

    public function createRelationshipRequest(
        User $requester,
        int $targetUserId,
        int $relationshipTypeId,
        string $message = '',
        string $motherName = null
    ): RelationshipRequest {
        return RelationshipRequest::create([
            'requester_id' => $requester->id,
            'target_user_id' => $targetUserId,
            'relationship_type_id' => $relationshipTypeId,
            'message' => $message,
            'mother_name' => $motherName,
            'status' => 'pending',
        ]);
    }

    public function acceptRelationshipRequest(RelationshipRequest $request): FamilyRelationship
    {
        DB::transaction(function () use ($request) {
            $request->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            // Créer la relation familiale
            FamilyRelationship::create([
                'user_id' => $request->requester_id,
                'related_user_id' => $request->target_user_id,
                'relationship_type_id' => $request->relationship_type_id,
                'mother_name' => $request->mother_name,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            // Créer la relation inverse si nécessaire
            $inverseType = $this->getInverseRelationshipType($request->relationship_type_id);
            if ($inverseType) {
                FamilyRelationship::create([
                    'user_id' => $request->target_user_id,
                    'related_user_id' => $request->requester_id,
                    'relationship_type_id' => $inverseType->id,
                    'status' => 'accepted',
                    'accepted_at' => now(),
                ]);
            }
        });

        return FamilyRelationship::where('user_id', $request->requester_id)
            ->where('related_user_id', $request->target_user_id)
            ->first();
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

    private function getInverseRelationshipType(int $relationshipTypeId): ?RelationshipType
    {
        $inverseMap = [
            1 => 2, // Père -> Fils
            2 => 1, // Fils -> Père
            3 => 4, // Mère -> Fille
            4 => 3, // Fille -> Mère
            5 => 6, // Frère -> Frère
            6 => 5, // Frère -> Frère
            7 => 8, // Sœur -> Sœur
            8 => 7, // Sœur -> Sœur
        ];

        $inverseId = $inverseMap[$relationshipTypeId] ?? null;
        return $inverseId ? RelationshipType::find($inverseId) : null;
    }

    public function deleteRelationship(FamilyRelationship $relationship): void
    {
        // Supprimer aussi la relation inverse
        FamilyRelationship::where('user_id', $relationship->related_user_id)
            ->where('related_user_id', $relationship->user_id)
            ->delete();

        $relationship->delete();
    }

    public function getFamilyStatistics(User $user): array
    {
        $relationships = $this->getUserRelationships($user);

        $stats = [
            'total_relatives' => $relationships->count(),
            'by_type' => [],
        ];

        foreach ($relationships as $relationship) {
            $typeName = $relationship->relationshipType->name;
            if (!isset($stats['by_type'][$typeName])) {
                $stats['by_type'][$typeName] = 0;
            }
            $stats['by_type'][$typeName]++;
        }

        return $stats;
    }
}
