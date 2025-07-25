<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\IntelligentRelationshipService;
use Illuminate\Support\Collection;

class SuggestionService
{
    protected FamilyRelationService $familyRelationService;
    protected IntelligentRelationshipService $intelligentRelationshipService;

    public function __construct(
        FamilyRelationService $familyRelationService,
        IntelligentRelationshipService $intelligentRelationshipService
    ) {
        $this->familyRelationService = $familyRelationService;
        $this->intelligentRelationshipService = $intelligentRelationshipService;
    }
    public function getUserSuggestions(User $user): Collection
    {
        // Obtenir les utilisateurs à exclure (déjà en relation)
        $excludedUserIds = $this->intelligentRelationshipService->getExcludedUsersForSuggestions($user);

        return Suggestion::where('user_id', $user->id)
            ->whereNotIn('suggested_user_id', $excludedUserIds)
            ->with(['suggestedUser.profile'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($suggestion) {
                // Ajouter la relation suggérée si elle existe
                if ($suggestion->suggested_relation_code) {
                    $relationNames = [
                        'father' => 'Père',
                        'mother' => 'Mère',
                        'son' => 'Fils',
                        'daughter' => 'Fille',
                        'brother' => 'Frère',
                        'sister' => 'Sœur',
                        'husband' => 'Mari',
                        'wife' => 'Épouse',
                    ];

                    $suggestion->suggested_relation_name = $relationNames[$suggestion->suggested_relation_code] ?? ucfirst($suggestion->suggested_relation_code);
                }

                return $suggestion;
            });
    }

    public function createSuggestion(User $user, int $suggestedUserId, string $type, string $message = '', ?string $suggestedRelationCode = null): Suggestion
    {
        return Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $suggestedUserId,
            'type' => $type,
            'status' => 'pending',
            'message' => $message,
            'suggested_relation_code' => $suggestedRelationCode,
        ]);
    }

    public function acceptSuggestion(Suggestion $suggestion, ?string $correctedRelationCode = null): void
    {
        // Déterminer le code de relation à utiliser
        $relationCode = $correctedRelationCode ?? $suggestion->suggested_relation_code;

        // Si une relation corrigée est fournie, l'utiliser
        if ($correctedRelationCode) {
            $suggestion->update([
                'status' => 'accepted',
                'suggested_relation_code' => $correctedRelationCode
            ]);
        } else {
            $suggestion->update(['status' => 'accepted']);
        }

        // Créer DIRECTEMENT la relation familiale (pas une demande)
        if ($relationCode) {
            $this->createFamilyRelationshipFromSuggestion($suggestion, $relationCode);
        }
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

        // Éliminer les doublons basés sur l'ID de l'utilisateur suggéré
        $uniqueSuggestions = $suggestions->unique(function ($suggestion) {
            return $suggestion->suggestedUser->id;
        });

        return $uniqueSuggestions;
    }

    /**
     * Convertit un code de relation en nom français
     */
    private function getRelationNameFromCode(string $code): string
    {
        $relationNames = [
            'father' => 'Père',
            'mother' => 'Mère',
            'son' => 'Fils',
            'daughter' => 'Fille',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'husband' => 'Mari',
            'wife' => 'Épouse',
        ];

        return $relationNames[$code] ?? ucfirst($code);
    }

    /**
     * Devine le genre basé sur le prénom
     */
    private function guessGenderFromName(User $user): ?string
    {
        $firstName = $user->profile?->first_name ?? '';

        // Prénoms masculins courants
        $maleNames = ['ahmed', 'mohammed', 'youssef', 'hassan', 'omar', 'ali', 'karim', 'said'];

        // Prénoms féminins courants
        $femaleNames = ['fatima', 'amina', 'khadija', 'aicha', 'zahra', 'maryam', 'sara', 'nadia'];

        $firstNameLower = strtolower($firstName);

        foreach ($maleNames as $maleName) {
            if (strpos($firstNameLower, $maleName) !== false) {
                return 'male';
            }
        }

        foreach ($femaleNames as $femaleName) {
            if (strpos($firstNameLower, $femaleName) !== false) {
                return 'female';
            }
        }

        return null; // Genre inconnu
    }

    /**
     * Crée DIRECTEMENT une relation familiale à partir d'une suggestion acceptée
     */
    private function createFamilyRelationshipFromSuggestion(Suggestion $suggestion, string $relationCode): void
    {
        // Récupérer les utilisateurs
        $requester = User::find($suggestion->user_id);
        $targetUser = User::find($suggestion->suggested_user_id);

        if (!$requester || !$targetUser) {
            \Log::error("Utilisateurs non trouvés pour la suggestion", [
                'suggestion_id' => $suggestion->id,
                'requester_id' => $suggestion->user_id,
                'target_user_id' => $suggestion->suggested_user_id
            ]);
            return;
        }

        // Récupérer le type de relation
        $relationshipType = RelationshipType::where('code', $relationCode)->first();

        if (!$relationshipType) {
            \Log::error("Type de relation non trouvé", [
                'relation_code' => $relationCode,
                'suggestion_id' => $suggestion->id
            ]);
            return;
        }

        try {
            // Créer DIRECTEMENT la relation familiale via le service
            $createdRelationship = $this->familyRelationService->createDirectRelationship(
                $requester,
                $targetUser,
                $relationshipType,
                "Relation créée automatiquement à partir d'une suggestion acceptée"
            );

            \Log::info("Relation familiale créée directement à partir d'une suggestion", [
                'suggestion_id' => $suggestion->id,
                'relationship_id' => $createdRelationship->id,
                'requester' => $requester->name,
                'target' => $targetUser->name,
                'relation' => $relationshipType->name_fr
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la création de la relation familiale", [
                'suggestion_id' => $suggestion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Crée une demande de relation familiale à partir d'une suggestion acceptée (ANCIENNE MÉTHODE)
     */
    private function createRelationshipRequestFromSuggestion(Suggestion $suggestion, string $relationCode): void
    {
        // Récupérer les utilisateurs
        $requester = User::find($suggestion->user_id);
        $targetUser = User::find($suggestion->suggested_user_id);

        if (!$requester || !$targetUser) {
            \Log::error("Utilisateurs non trouvés pour la suggestion", [
                'suggestion_id' => $suggestion->id,
                'requester_id' => $suggestion->user_id,
                'target_user_id' => $suggestion->suggested_user_id
            ]);
            return;
        }

        // Récupérer le type de relation
        $relationshipType = RelationshipType::where('code', $relationCode)->first();

        if (!$relationshipType) {
            \Log::error("Type de relation non trouvé", [
                'relation_code' => $relationCode,
                'suggestion_id' => $suggestion->id
            ]);
            return;
        }

        try {
            // Créer la demande de relation via le service FamilyRelationService
            $request = $this->familyRelationService->createRelationshipRequest(
                $requester,
                $targetUser->id,
                $relationshipType->id,
                "Demande créée automatiquement à partir d'une suggestion acceptée",
                null // mother_name - peut être null pour les suggestions
            );

            \Log::info("Demande de relation créée à partir d'une suggestion", [
                'suggestion_id' => $suggestion->id,
                'request_id' => $request->id,
                'requester' => $requester->name,
                'target' => $targetUser->name,
                'relation' => $relationshipType->name_fr
            ]);

        } catch (\Exception $e) {
            \Log::error("Erreur lors de la création de la demande de relation", [
                'suggestion_id' => $suggestion->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
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
     * Supprime les anciennes suggestions pour éviter les doublons
     */
    public function clearOldSuggestions(User $user): void
    {
        // Supprimer les suggestions en attente qui sont devenues obsolètes
        Suggestion::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7)) // Garder les suggestions récentes
            ->delete();
    }

    /**
     * Génère des suggestions automatiques après acceptation d'une relation
     */
    public function generateAutomaticSuggestions(User $user): Collection
    {
        // Générer des suggestions avec priorité sur les connexions familiales
        $suggestions = $this->generateSuggestions($user);

        // Limiter à 4 suggestions comme demandé
        return $suggestions->take(4);
    }

    /**
     * Récupère SEULEMENT les IDs des utilisateurs avec lesquels il y a une relation DIRECTE
     */
    private function getAllRelatedUserIds(User $user): array
    {
        $relatedIds = collect();

        // Relations familiales acceptées où l'utilisateur est directement impliqué
        $relatedIds = $relatedIds->merge(
            FamilyRelationship::where('user_id', $user->id)
                ->where('status', 'accepted')
                ->pluck('related_user_id')
        );

        $relatedIds = $relatedIds->merge(
            FamilyRelationship::where('related_user_id', $user->id)
                ->where('status', 'accepted')
                ->pluck('user_id')
        );

        // Demandes de relation en attente (pour éviter les doublons)
        $relatedIds = $relatedIds->merge(
            \App\Models\RelationshipRequest::where('requester_id', $user->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->pluck('target_user_id')
        );

        $relatedIds = $relatedIds->merge(
            \App\Models\RelationshipRequest::where('target_user_id', $user->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->pluck('requester_id')
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

        // Pour chaque relation existante, analyser les connexions familiales
        foreach ($existingRelations as $relation) {
            $relatedUser = $this->getRelatedUserFromRelation($relation, $user);
            $userRelationType = $this->getUserRelationTypeFromRelation($relation, $user);

            // Récupérer toutes les relations de cette personne
            $familyMembers = FamilyRelationship::where(function($query) use ($relatedUser) {
                $query->where('user_id', $relatedUser->id)
                      ->orWhere('related_user_id', $relatedUser->id);
            })
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get();

            foreach ($familyMembers as $familyRelation) {
                $suggestedUser = $this->getRelatedUserFromRelation($familyRelation, $relatedUser);

                // Éviter l'utilisateur actuel et les utilisateurs déjà exclus
                if ($suggestedUser->id === $user->id ||
                    in_array($suggestedUser->id, $excludedUserIds) ||
                    $this->hasExistingSuggestion($user, $suggestedUser->id)) {
                    continue;
                }

                $suggestedUserRelationType = $this->getUserRelationTypeFromRelation($familyRelation, $relatedUser);

                // Inférer la relation correcte
                $inferredRelation = $this->inferFamilyRelation(
                    $userRelationType,
                    $suggestedUserRelationType,
                    $user,
                    $suggestedUser,
                    $relatedUser
                );

                // Debug: Afficher l'inférence (seulement en mode console)
                if (app()->runningInConsole()) {
                    echo "Inférence: {$user->name} -> {$suggestedUser->name} via {$relatedUser->name}: " .
                         ($inferredRelation ? $inferredRelation['description'] : 'null') . "\n";
                }

                if ($inferredRelation) {
                    // Créer et sauvegarder la suggestion dans la base de données
                    $suggestion = $this->createSuggestion(
                        $user,
                        $suggestedUser->id,
                        'family_connection',
                        "Via {$relatedUser->name} - {$inferredRelation['description']}",
                        $inferredRelation['code']
                    );

                    $suggestions->push($suggestion);
                }
            }
        }

        return $suggestions->take(4); // Limiter à 4 suggestions familiales comme demandé
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

    /**
     * Récupère l'utilisateur lié dans une relation par rapport à un utilisateur de référence
     */
    private function getRelatedUserFromRelation(FamilyRelationship $relation, User $referenceUser): User
    {
        if ($relation->user_id === $referenceUser->id) {
            return $relation->relatedUser;
        } else {
            return $relation->user;
        }
    }

    /**
     * Récupère le type de relation de l'utilisateur dans une relation
     */
    private function getUserRelationTypeFromRelation(FamilyRelationship $relation, User $user): RelationshipType
    {
        if ($relation->user_id === $user->id) {
            // L'utilisateur est l'initiateur, retourner son type de relation
            return $relation->relationshipType;
        } else {
            // L'utilisateur est la cible, retourner le type de relation inverse
            return $this->getInverseRelationshipTypeByCode($relation->relationshipType);
        }
    }

    /**
     * Récupère le type de relation inverse basé sur le code
     */
    private function getInverseRelationshipTypeByCode(RelationshipType $relationType): RelationshipType
    {
        $inverseCodeMap = [
            'father' => 'son',
            'mother' => 'daughter',
            'son' => 'father',
            'daughter' => 'mother',
            'brother' => 'brother',
            'sister' => 'sister',
            'husband' => 'wife',
            'wife' => 'husband',
        ];

        $inverseCode = $inverseCodeMap[$relationType->code] ?? $relationType->code;
        return RelationshipType::where('code', $inverseCode)->first() ?? $relationType;
    }

    /**
     * Infère la relation familiale entre deux personnes via une connexion commune
     */
    private function inferFamilyRelation(
        RelationshipType $userToConnector,
        RelationshipType $connectorToSuggested,
        User $user,
        User $suggestedUser,
        User $connector
    ): ?array {
        $userGender = $user->profile?->gender;
        $suggestedGender = $suggestedUser->profile?->gender;

        // Si le genre n'est pas défini, essayer de le deviner par le prénom
        if (!$suggestedGender) {
            $suggestedGender = $this->guessGenderFromName($suggestedUser);
        }

        // Logique d'inférence basée sur les codes de relation
        $userCode = $userToConnector->code;
        $suggestedCode = $connectorToSuggested->code;

        // Debug: Log the relationship codes for troubleshooting
        if (app()->runningInConsole()) {
            echo "DEBUG: User ({$user->name}) -> Connector ({$connector->name}): {$userCode}\n";
            echo "DEBUG: Connector ({$connector->name}) -> Suggested ({$suggestedUser->name}): {$suggestedCode}\n";
        }

        // PRIORITÉ 1: Si l'utilisateur est parent du connecteur ET la personne suggérée est épouse/mari du connecteur
        if (in_array($userCode, ['father', 'mother'])) {
            if (in_array($suggestedCode, ['wife', 'husband'])) {
                $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
                $relationName = $suggestedGender === 'male' ? 'beau-fils' : 'belle-fille';
                return [
                    'code' => $relationCode,
                    'description' => "Enfant par alliance - {$relationName}"
                ];
            }
        }

        // PRIORITÉ 2: Si l'utilisateur est fils/fille du connecteur
        if (in_array($userCode, ['son', 'daughter'])) {

            // Et la personne suggérée est épouse/mari du connecteur (parent)
            if (in_array($suggestedCode, ['wife', 'husband'])) {
                $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
                $relationName = $suggestedGender === 'male' ? 'beau-père' : 'belle-mère';
                return [
                    'code' => $relationCode,
                    'description' => "Parent par alliance - {$relationName}"
                ];
            }

            // Et la personne suggérée est aussi fils/fille du connecteur (frère/sœur)
            if (in_array($suggestedCode, ['son', 'daughter'])) {
                $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
                $relationName = $suggestedGender === 'male' ? 'frère' : 'sœur';
                return [
                    'code' => $relationCode,
                    'description' => "Frère/Sœur - {$relationName} via {$connector->name}"
                ];
            }
        }

        // Si l'utilisateur est époux/épouse du connecteur
        if (in_array($userCode, ['husband', 'wife'])) {

            // Et la personne suggérée est fils/fille du connecteur
            if (in_array($suggestedCode, ['son', 'daughter'])) {
                $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
                $relationName = $suggestedGender === 'male' ? 'beau-fils' : 'belle-fille';
                return [
                    'code' => $relationCode,
                    'description' => "Beau-fils/Belle-fille - {$relationName}"
                ];
            }
        }

        // Si l'utilisateur est père/mère du connecteur
        if (in_array($userCode, ['father', 'mother'])) {

            // Et la personne suggérée est fils/fille du connecteur (petit-enfant)
            if (in_array($suggestedCode, ['son', 'daughter'])) {
                $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
                $relationName = $suggestedGender === 'male' ? 'petit-fils' : 'petite-fille';
                return [
                    'code' => $relationCode,
                    'description' => "Petit-enfant - {$relationName}"
                ];
            }
        }



        // Relations par défaut (cousin/cousine)
        $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
        $relationName = $suggestedGender === 'male' ? 'cousin' : 'cousine';
        return [
            'code' => $relationCode,
            'description' => "Famille élargie - {$relationName} potentiel(le)"
        ];
    }
}
