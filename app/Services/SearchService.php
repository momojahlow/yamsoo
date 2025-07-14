<?php

namespace App\Services;

use App\Models\User;
use App\Models\Family;
use App\Models\Message;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function searchUsers(string $query, User $currentUser, int $limit = 10): Collection
    {
        return User::where('id', '!=', $currentUser->id)
            ->where(function (Builder $q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%")
                  ->orWhereHas('profile', function (Builder $profileQuery) use ($query) {
                      $profileQuery->where('first_name', 'like', "%{$query}%")
                                  ->orWhere('last_name', 'like', "%{$query}%")
                                  ->orWhere('phone', 'like', "%{$query}%");
                  });
            })
            ->with(['profile'])
            ->limit($limit)
            ->get();
    }

    public function searchFamilies(string $query, User $currentUser, int $limit = 10): Collection
    {
        return Family::where('name', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->with(['members.profile'])
            ->limit($limit)
            ->get();
    }

    public function searchMessages(string $query, User $currentUser, int $limit = 20): Collection
    {
        return Message::where(function (Builder $q) use ($currentUser) {
                $q->where('user_id', $currentUser->id)
                  ->orWhere('recipient_id', $currentUser->id);
            })
            ->where('content', 'like', "%{$query}%")
            ->with(['sender.profile', 'receiver.profile'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function searchByLocation(string $location, User $currentUser, int $limit = 10): Collection
    {
        return User::where('id', '!=', $currentUser->id)
            ->whereHas('profile', function (Builder $q) use ($location) {
                $q->where('address', 'like', "%{$location}%");
            })
            ->with(['profile'])
            ->limit($limit)
            ->get();
    }

    public function searchByAgeRange(int $minAge, int $maxAge, User $currentUser, int $limit = 10): Collection
    {
        $minDate = now()->subYears($maxAge);
        $maxDate = now()->subYears($minAge);

        return User::where('id', '!=', $currentUser->id)
            ->whereHas('profile', function (Builder $q) use ($minDate, $maxDate) {
                $q->whereBetween('birth_date', [$minDate, $maxDate]);
            })
            ->with(['profile'])
            ->limit($limit)
            ->get();
    }

    public function searchByRelationshipType(string $relationshipType, User $currentUser, int $limit = 10): Collection
    {
        return User::where('id', '!=', $currentUser->id)
            ->whereHas('familyRelationships', function (Builder $q) use ($relationshipType) {
                $q->whereHas('relationshipType', function (Builder $typeQuery) use ($relationshipType) {
                    $typeQuery->where('name', 'like', "%{$relationshipType}%");
                });
            })
            ->with(['profile', 'familyRelationships.relationshipType'])
            ->limit($limit)
            ->get();
    }

    public function advancedSearch(array $criteria, User $currentUser, int $limit = 20): Collection
    {
        $query = User::where('id', '!=', $currentUser->id);

        if (isset($criteria['name'])) {
            $query->where('name', 'like', "%{$criteria['name']}%");
        }

        if (isset($criteria['email'])) {
            $query->where('email', 'like', "%{$criteria['email']}%");
        }

        if (isset($criteria['location'])) {
            $query->whereHas('profile', function (Builder $q) use ($criteria) {
                $q->where('address', 'like', "%{$criteria['location']}%");
            });
        }

        if (isset($criteria['age_min']) && isset($criteria['age_max'])) {
            $minDate = now()->subYears($criteria['age_max']);
            $maxDate = now()->subYears($criteria['age_min']);

            $query->whereHas('profile', function (Builder $q) use ($minDate, $maxDate) {
                $q->whereBetween('birth_date', [$minDate, $maxDate]);
            });
        }

        if (isset($criteria['gender'])) {
            $query->whereHas('profile', function (Builder $q) use ($criteria) {
                $q->where('gender', $criteria['gender']);
            });
        }

        return $query->with(['profile'])
                    ->limit($limit)
                    ->get();
    }

    public function searchSuggestions(string $query, User $currentUser, int $limit = 5): Collection
    {
        // Recherche pour des suggestions de connexions
        $users = $this->searchUsers($query, $currentUser, $limit);

        // Filtrer les utilisateurs déjà connectés
        return $users->filter(function ($user) use ($currentUser) {
            return !$this->areUsersConnected($currentUser, $user);
        });
    }

    public function getSearchHistory(User $user): Collection
    {
        // Implémenter l'historique de recherche si nécessaire
        return collect();
    }

    public function saveSearchQuery(User $user, string $query): void
    {
        // Sauvegarder la requête de recherche pour l'historique
    }

    private function areUsersConnected(User $user1, User $user2): bool
    {
        // Vérifier si les utilisateurs sont déjà connectés
        return false; // À implémenter selon la logique métier
    }

    public function getPopularSearches(): array
    {
        // Retourner les recherches populaires
        return [
            'famille',
            'cousins',
            'grands-parents',
            'oncles',
            'tantes',
        ];
    }

    public function getSearchSuggestions(string $partialQuery): array
    {
        // Retourner des suggestions basées sur la requête partielle
        $suggestions = [];

        if (strlen($partialQuery) >= 2) {
            $suggestions = User::where('name', 'like', "{$partialQuery}%")
                ->orWhere('email', 'like', "{$partialQuery}%")
                ->limit(5)
                ->pluck('name')
                ->toArray();
        }

        return $suggestions;
    }
}
