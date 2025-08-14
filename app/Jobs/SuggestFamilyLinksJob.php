<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\Suggestion;
use App\Models\RelationshipType;
use App\Services\FamilyRelationshipInferenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class SuggestFamilyLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $targetUser;
    public User $newMember;
    public FamilyRelationship $relationship;

    /**
     * Create a new job instance.
     */
    public function __construct(User $targetUser, User $newMember, FamilyRelationship $relationship)
    {
        $this->targetUser = $targetUser;
        $this->newMember = $newMember;
        $this->relationship = $relationship;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Starting family link suggestions', [
                'target_user' => $this->targetUser->name,
                'new_member' => $this->newMember->name
            ]);

            // Clear old suggestions for this user
            $this->clearOldSuggestions();

            // Get all existing family members for the target user
            $existingRelations = $this->getExistingRelations();
            
            // Get users to exclude (already related)
            $excludedUserIds = $this->getExcludedUserIds($existingRelations);

            // Generate suggestions based on the new relationship
            $suggestions = $this->generateSuggestionsFromNewRelationship($existingRelations, $excludedUserIds);

            // Save suggestions to database
            $this->saveSuggestions($suggestions);

            Log::info('Family link suggestions completed', [
                'target_user_id' => $this->targetUser->id,
                'suggestions_generated' => $suggestions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error in SuggestFamilyLinksJob', [
                'target_user_id' => $this->targetUser->id,
                'new_member_id' => $this->newMember->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Clear old pending suggestions for the target user
     */
    private function clearOldSuggestions(): void
    {
        Suggestion::where('user_id', $this->targetUser->id)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->delete();
    }

    /**
     * Get existing family relationships for the target user
     */
    private function getExistingRelations(): Collection
    {
        return FamilyRelationship::where(function($query) {
                $query->where('user_id', $this->targetUser->id)
                      ->orWhere('related_user_id', $this->targetUser->id);
            })
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();
    }

    /**
     * Get user IDs to exclude from suggestions
     */
    private function getExcludedUserIds(Collection $existingRelations): array
    {
        $excludedIds = [$this->targetUser->id];

        foreach ($existingRelations as $relation) {
            $relatedUserId = $relation->user_id === $this->targetUser->id 
                ? $relation->related_user_id 
                : $relation->user_id;
            $excludedIds[] = $relatedUserId;
        }

        return array_unique($excludedIds);
    }

    /**
     * Generate suggestions based on the new relationship
     */
    private function generateSuggestionsFromNewRelationship(Collection $existingRelations, array $excludedUserIds): Collection
    {
        $suggestions = collect();

        // For each existing relation, find potential connections through the new member
        foreach ($existingRelations as $relation) {
            $relatedUser = $relation->user_id === $this->targetUser->id 
                ? $relation->relatedUser 
                : $relation->user;

            // Get family members of this related user
            $familyMembers = $this->getFamilyMembersOf($relatedUser, $excludedUserIds);

            foreach ($familyMembers as $familyMember) {
                if (!in_array($familyMember->id, $excludedUserIds) && 
                    !$this->hasExistingSuggestion($familyMember->id)) {
                    
                    $inferredRelation = $this->inferRelationshipThroughConnection(
                        $relation,
                        $familyMember,
                        $relatedUser
                    );

                    if ($inferredRelation) {
                        $suggestions->push([
                            'suggested_user' => $familyMember,
                            'relation_code' => $inferredRelation['code'],
                            'reason' => $inferredRelation['reason'],
                            'confidence' => $inferredRelation['confidence']
                        ]);
                    }
                }
            }
        }

        return $suggestions->take(5); // Limit to 5 suggestions
    }

    /**
     * Get family members of a specific user
     */
    private function getFamilyMembersOf(User $user, array $excludedUserIds): Collection
    {
        return FamilyRelationship::where(function($query) use ($user) {
                $query->where('user_id', $user->id)
                      ->orWhere('related_user_id', $user->id);
            })
            ->where('status', 'accepted')
            ->whereNotIn('user_id', $excludedUserIds)
            ->whereNotIn('related_user_id', $excludedUserIds)
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get()
            ->map(function($relation) use ($user) {
                return $relation->user_id === $user->id 
                    ? $relation->relatedUser 
                    : $relation->user;
            })
            ->unique('id');
    }

    /**
     * Check if suggestion already exists
     */
    private function hasExistingSuggestion(int $suggestedUserId): bool
    {
        return Suggestion::where('user_id', $this->targetUser->id)
            ->where('suggested_user_id', $suggestedUserId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Infer relationship through a connection
     */
    private function inferRelationshipThroughConnection(
        FamilyRelationship $userRelation,
        User $suggestedUser,
        User $connector
    ): ?array {
        $inferenceService = new FamilyRelationshipInferenceService();

        // Get the relationship type codes
        $userToConnectorCode = $userRelation->relationshipType->name ?? 'family_member';

        // Find the relationship between connector and suggested user
        $connectorToSuggestedRelation = FamilyRelationship::where(function($query) use ($connector, $suggestedUser) {
                $query->where('user_id', $connector->id)->where('related_user_id', $suggestedUser->id);
            })
            ->orWhere(function($query) use ($connector, $suggestedUser) {
                $query->where('user_id', $suggestedUser->id)->where('related_user_id', $connector->id);
            })
            ->with('relationshipType')
            ->first();

        if (!$connectorToSuggestedRelation) {
            return null;
        }

        $connectorToSuggestedCode = $connectorToSuggestedRelation->relationshipType->name ?? 'family_member';

        // Use the inference service to determine the relationship
        return $inferenceService->inferRelationship(
            $this->targetUser,
            $suggestedUser,
            $connector,
            $userToConnectorCode,
            $connectorToSuggestedCode
        );
    }

    /**
     * Save suggestions to database
     */
    private function saveSuggestions(Collection $suggestions): void
    {
        foreach ($suggestions as $suggestionData) {
            Suggestion::create([
                'user_id' => $this->targetUser->id,
                'suggested_user_id' => $suggestionData['suggested_user']->id,
                'type' => 'family_link',
                'status' => 'pending',
                'suggested_relation_code' => $suggestionData['relation_code'],
                'message' => $suggestionData['reason'],
                'confidence_score' => $suggestionData['confidence']
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SuggestFamilyLinksJob failed', [
            'target_user_id' => $this->targetUser->id,
            'new_member_id' => $this->newMember->id,
            'error' => $exception->getMessage()
        ]);
    }
}
