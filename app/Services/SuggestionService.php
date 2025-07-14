<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Support\Collection;

class SuggestionService
{
    public function getUserSuggestions(User $user): Collection
    {
        return Suggestion::where('user_id', $user->id)
            ->with(['suggestedUser.profile'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createSuggestion(User $user, int $suggestedUserId, string $type, string $message = ''): Suggestion
    {
        return Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $suggestedUserId,
            'type' => $type,
            'status' => 'pending',
            'message' => $message,
        ]);
    }

    public function acceptSuggestion(Suggestion $suggestion): void
    {
        $suggestion->update(['status' => 'accepted']);
    }

    public function rejectSuggestion(Suggestion $suggestion): void
    {
        $suggestion->update(['status' => 'rejected']);
    }

    public function deleteSuggestion(Suggestion $suggestion): void
    {
        $suggestion->delete();
    }

    public function generateSuggestions(User $user): Collection
    {
        // Logique pour générer des suggestions basées sur :
        // - Utilisateurs avec des noms similaires
        // - Utilisateurs dans la même région
        // - Connexions communes
        // - Familles similaires

        $suggestions = collect();

        // Suggestion basée sur les noms similaires
        $similarNames = User::where('id', '!=', $user->id)
            ->where('name', 'like', '%' . substr($user->name, 0, 3) . '%')
            ->with('profile')
            ->limit(5)
            ->get();

        foreach ($similarNames as $similarUser) {
            if (!$this->hasExistingSuggestion($user, $similarUser->id)) {
                $suggestions->push($this->createSuggestion(
                    $user,
                    $similarUser->id,
                    'name_similarity',
                    "Utilisateur avec un nom similaire : {$similarUser->name}"
                ));
            }
        }

        return $suggestions;
    }

    private function hasExistingSuggestion(User $user, int $suggestedUserId): bool
    {
        return Suggestion::where('user_id', $user->id)
            ->where('suggested_user_id', $suggestedUserId)
            ->exists();
    }

    public function getPendingSuggestions(User $user): Collection
    {
        return Suggestion::where('user_id', $user->id)
            ->where('status', 'pending')
            ->with(['suggestedUser.profile'])
            ->get();
    }

    public function getAcceptedSuggestions(User $user): Collection
    {
        return Suggestion::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['suggestedUser.profile'])
            ->get();
    }
}
