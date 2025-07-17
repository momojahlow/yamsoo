<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
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
        $suggestions = collect();

        // Récupérer TOUS les utilisateurs avec lesquels il y a déjà une relation
        $excludedUserIds = $this->getAllRelatedUserIds($user);

        // Récupérer les relations existantes pour l'analyse familiale
        $existingRelations = $this->getExistingRelations($user);

        // 1. Suggestions basées sur les relations familiales existantes
        $familySuggestions = $this->generateFamilyBasedSuggestions($user, $existingRelations, $excludedUserIds);
        $suggestions = $suggestions->merge($familySuggestions);

        // 2. Suggestions basées sur les noms similaires (avec analyse de genre)
        $nameSuggestions = $this->generateNameBasedSuggestions($user, $excludedUserIds);
        $suggestions = $suggestions->merge($nameSuggestions);

        // 3. Suggestions basées sur la région (avec analyse de genre)
        $regionSuggestions = $this->generateRegionBasedSuggestions($user, $excludedUserIds);
        $suggestions = $suggestions->merge($regionSuggestions);

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

    /**
     * Récupère TOUS les IDs des utilisateurs avec lesquels il y a une relation (acceptée, en attente, ou refusée)
     */
    private function getAllRelatedUserIds(User $user): array
    {
        $relatedIds = collect();

        // Relations où l'utilisateur est l'initiateur
        $relatedIds = $relatedIds->merge(
            FamilyRelationship::where('user_id', $user->id)->pluck('related_user_id')
        );

        // Relations où l'utilisateur est la cible
        $relatedIds = $relatedIds->merge(
            FamilyRelationship::where('related_user_id', $user->id)->pluck('user_id')
        );

        // Ajouter aussi les demandes de relation en attente
        $relatedIds = $relatedIds->merge(
            \App\Models\RelationshipRequest::where('requester_id', $user->id)->pluck('target_user_id')
        );

        $relatedIds = $relatedIds->merge(
            \App\Models\RelationshipRequest::where('target_user_id', $user->id)->pluck('requester_id')
        );

        return $relatedIds->unique()->filter()->toArray();
    }

    /**
     * Récupère les relations familiales existantes ACCEPTÉES de l'utilisateur
     */
    private function getExistingRelations(User $user): Collection
    {
        return FamilyRelationship::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('related_user_id', $user->id);
        })
        ->where('status', 'accepted')
        ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
        ->get();
    }

    /**
     * Génère des suggestions basées sur les relations familiales existantes
     */
    private function generateFamilyBasedSuggestions(User $user, Collection $existingRelations, array $excludedUserIds): Collection
    {
        $suggestions = collect();

        // Pour chaque relation existante, chercher des connexions potentielles
        foreach ($existingRelations as $relation) {
            $relatedUser = $relation->relatedUser;

            // Chercher les relations de cette personne pour suggérer des connexions
            $secondDegreeRelations = FamilyRelationship::where('user_id', $relatedUser->id)
                ->where('status', 'accepted')
                ->where('related_user_id', '!=', $user->id) // Exclure l'utilisateur actuel
                ->with(['relatedUser.profile', 'relationshipType'])
                ->get();

            foreach ($secondDegreeRelations as $secondRelation) {
                $suggestedUser = $secondRelation->relatedUser;

                // Éviter les suggestions pour les utilisateurs déjà liés ou suggérés
                if (!in_array($suggestedUser->id, $excludedUserIds) &&
                    !$this->hasExistingSuggestion($user, $suggestedUser->id)) {

                    $relationshipType = $this->inferRelationshipType(
                        $relation->relationshipType,
                        $secondRelation->relationshipType,
                        $user,
                        $suggestedUser
                    );

                    $suggestions->push($this->createSuggestion(
                        $user,
                        $suggestedUser->id,
                        'family_connection',
                        "Connexion via {$relatedUser->name} - Relation suggérée: {$relationshipType}"
                    ));
                }
            }
        }

        return $suggestions->take(3); // Limiter à 3 suggestions familiales
    }

    /**
     * Génère des suggestions basées sur les noms similaires avec analyse de genre
     */
    private function generateNameBasedSuggestions(User $user, array $excludedUserIds): Collection
    {
        $suggestions = collect();
        $userProfile = $user->profile;

        if (!$userProfile) {
            return $suggestions;
        }

        // Chercher des utilisateurs avec des noms de famille similaires
        $lastName = $userProfile->last_name ?? '';
        if (strlen($lastName) >= 3) {
            $similarUsers = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $excludedUserIds)
                ->whereHas('profile', function($query) use ($lastName) {
                    $query->where('last_name', 'like', "%{$lastName}%");
                })
                ->with('profile')
                ->limit(5)
                ->get();

            foreach ($similarUsers as $similarUser) {
                if (!$this->hasExistingSuggestion($user, $similarUser->id)) {
                    $relationshipType = $this->suggestRelationshipBasedOnGender($user, $similarUser);

                    $suggestions->push($this->createSuggestion(
                        $user,
                        $similarUser->id,
                        'name_similarity',
                        "Nom de famille similaire - Relation suggérée: {$relationshipType}"
                    ));
                }
            }
        }

        return $suggestions->take(2); // Limiter à 2 suggestions par nom
    }

    /**
     * Génère des suggestions basées sur la région avec analyse de genre
     */
    private function generateRegionBasedSuggestions(User $user, array $excludedUserIds): Collection
    {
        $suggestions = collect();
        $userProfile = $user->profile;

        if (!$userProfile || !$userProfile->address) {
            return $suggestions;
        }

        // Extraire la ville de l'adresse
        $city = $this->extractCityFromAddress($userProfile->address);

        if ($city) {
            $sameRegionUsers = User::where('id', '!=', $user->id)
                ->whereNotIn('id', $excludedUserIds)
                ->whereHas('profile', function($query) use ($city) {
                    $query->where('address', 'like', "%{$city}%");
                })
                ->with('profile')
                ->limit(3)
                ->get();

            foreach ($sameRegionUsers as $regionUser) {
                if (!$this->hasExistingSuggestion($user, $regionUser->id)) {
                    $relationshipType = $this->suggestRelationshipBasedOnGender($user, $regionUser);

                    $suggestions->push($this->createSuggestion(
                        $user,
                        $regionUser->id,
                        'region_proximity',
                        "Même région ({$city}) - Relation suggérée: {$relationshipType}"
                    ));
                }
            }
        }

        return $suggestions->take(2); // Limiter à 2 suggestions par région
    }

    /**
     * Vérifie si une relation existe déjà entre deux utilisateurs
     */
    private function hasExistingRelation(User $user, int $otherUserId): bool
    {
        return FamilyRelationship::where(function($query) use ($user, $otherUserId) {
            $query->where('user_id', $user->id)->where('related_user_id', $otherUserId);
        })->orWhere(function($query) use ($user, $otherUserId) {
            $query->where('user_id', $otherUserId)->where('related_user_id', $user->id);
        })->exists();
    }

    /**
     * Suggère un type de relation basé sur le genre des utilisateurs
     */
    private function suggestRelationshipBasedOnGender(User $user1, User $user2): string
    {
        $user1Gender = $user1->profile?->gender;
        $user2Gender = $user2->profile?->gender;

        // Si les genres ne sont pas définis, suggérer une relation neutre
        if (!$user1Gender || !$user2Gender) {
            return 'cousin(e)';
        }

        // Logique basée sur l'âge et le genre
        $user1Age = $user1->profile?->birth_date ? now()->diffInYears($user1->profile->birth_date) : null;
        $user2Age = $user2->profile?->birth_date ? now()->diffInYears($user2->profile->birth_date) : null;

        // Si même génération (différence d'âge < 10 ans), suggérer frère/sœur ou cousin(e)
        if ($user1Age && $user2Age && abs($user1Age - $user2Age) < 10) {
            return $user2Gender === 'male' ? 'frère' : 'sœur';
        }

        // Sinon, suggérer cousin(e)
        return $user2Gender === 'male' ? 'cousin' : 'cousine';
    }

    /**
     * Infère le type de relation basé sur deux relations existantes
     */
    private function inferRelationshipType(RelationshipType $relation1, RelationshipType $relation2, User $user, User $suggestedUser): string
    {
        $suggestedGender = $suggestedUser->profile?->gender;

        // Logique d'inférence basée sur les codes de relation
        $code1 = $relation1->code;
        $code2 = $relation2->code;

        // Si A est frère de B et B est père de C, alors A est oncle de C
        if (($code1 === 'brother' && $code2 === 'father') || ($code1 === 'father' && $code2 === 'brother')) {
            return $suggestedGender === 'male' ? 'neveu' : 'nièce';
        }

        // Si A est sœur de B et B est mère de C, alors A est tante de C
        if (($code1 === 'sister' && $code2 === 'mother') || ($code1 === 'mother' && $code2 === 'sister')) {
            return $suggestedGender === 'male' ? 'neveu' : 'nièce';
        }

        // Si A est enfant de B et B est parent de C, alors A et C sont frère/sœur
        if (($code1 === 'son' || $code1 === 'daughter') && ($code2 === 'father' || $code2 === 'mother')) {
            return $suggestedGender === 'male' ? 'frère' : 'sœur';
        }

        // Par défaut, suggérer cousin(e)
        return $suggestedGender === 'male' ? 'cousin' : 'cousine';
    }

    /**
     * Extrait la ville d'une adresse
     */
    private function extractCityFromAddress(string $address): ?string
    {
        // Logique simple pour extraire la ville (avant la virgule)
        $parts = explode(',', $address);
        return trim($parts[0] ?? '');
    }
}
