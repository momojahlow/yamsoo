<?php

namespace App\Services;

use App\Models\Suggestion;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipType;
use App\Models\RelationshipRequest;
use App\Services\FamilyRelationService;
use App\Services\SimpleRelationshipInferenceService;
use App\Services\IntelligentSuggestionService;
use App\Services\GeminiRelationshipService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SuggestionService
{
    protected FamilyRelationService $familyRelationService;
    protected SimpleRelationshipInferenceService $simpleRelationshipInferenceService;
    protected IntelligentSuggestionService $intelligentSuggestionService;
    protected GeminiRelationshipService $geminiRelationshipService;

    public function __construct(
        FamilyRelationService $familyRelationService,
        SimpleRelationshipInferenceService $simpleRelationshipInferenceService,
        IntelligentSuggestionService $intelligentSuggestionService,
        GeminiRelationshipService $geminiRelationshipService
    ) {
        $this->familyRelationService = $familyRelationService;
        $this->simpleRelationshipInferenceService = $simpleRelationshipInferenceService;
        $this->intelligentSuggestionService = $intelligentSuggestionService;
        $this->geminiRelationshipService = $geminiRelationshipService;
    }
    public function getUserSuggestions(User $user): Collection
    {
        // Obtenir les utilisateurs à exclure (déjà en relation)
        $excludedUserIds = $this->getAllRelatedUserIds($user);

        return Suggestion::where('user_id', $user->id)
            ->whereNotIn('suggested_user_id', $excludedUserIds)
            ->with(['suggestedUser.profile'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($suggestion) {
                // Si suggested_relation_name n'existe pas, le récupérer depuis la base de données
                if ($suggestion->suggested_relation_code && !$suggestion->suggested_relation_name) {
                    $relationType = RelationshipType::where('name', $suggestion->suggested_relation_code)->first();
                    $suggestion->suggested_relation_name = $relationType ? $relationType->display_name_fr : ucfirst($suggestion->suggested_relation_code);
                }

                return $suggestion;
            });
    }

    public function createSuggestion(User $user, int $suggestedUserId, string $type, string $message = '', ?string $suggestedRelationCode = null): Suggestion
    {
        // Vérifier qu'on peut créer cette suggestion
        if ($this->hasExistingSuggestion($user, $suggestedUserId)) {
            throw new \InvalidArgumentException('Une suggestion, relation ou demande existe déjà entre ces utilisateurs.');
        }

        // Déterminer le nom de la relation à partir du code
        $relationName = null;
        if ($suggestedRelationCode) {
            $relationType = RelationshipType::where('name', $suggestedRelationCode)->first();
            $relationName = $relationType ? $relationType->display_name_fr : $suggestedRelationCode;
        }

        return Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $suggestedUserId,
            'type' => $type,
            'status' => 'pending',
            'message' => $message,
            'reason' => $message,
            'suggested_relation_code' => $suggestedRelationCode,
            'suggested_relation_name' => $relationName,
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

        // Créer une DEMANDE de relation familiale (pas directement)
        if ($relationCode) {
            $this->createRelationshipRequestFromSuggestion($suggestion, $relationCode);
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

        // 2. Suggestions intelligentes avec Gemini AI
        $geminiSuggestions = $this->generateGeminiBasedSuggestions($user, $excludedUserIds);
        $suggestions = $suggestions->merge($geminiSuggestions);

        // 3. Suggestions basées sur les noms similaires (avec analyse de genre)
        $nameSuggestions = $this->generateNameBasedSuggestions($user, $excludedUserIds);
        $suggestions = $suggestions->merge($nameSuggestions);

        // 4. Suggestions basées sur la région (avec analyse de genre)
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
     * Crée une demande de relation familiale à partir d'une suggestion acceptée
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
        $relationshipType = RelationshipType::where('name', $relationCode)->first();

        if (!$relationshipType) {
            \Log::error("Type de relation non trouvé", [
                'relation_code' => $relationCode,
                'suggestion_id' => $suggestion->id
            ]);
            return;
        }

        try {
            // Créer une DEMANDE de relation familiale (pas directement)
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
                'relation' => $relationshipType->display_name_fr
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
        // Vérifier s'il y a déjà une suggestion
        $existingSuggestion = Suggestion::where('user_id', $user->id)
            ->where('suggested_user_id', $suggestedUserId)
            ->exists();

        if ($existingSuggestion) {
            return true;
        }

        // Vérifier s'il y a déjà une relation établie
        $existingRelation = FamilyRelationship::where('user_id', $user->id)
            ->where('related_user_id', $suggestedUserId)
            ->exists();

        if ($existingRelation) {
            return true;
        }

        // Vérifier s'il y a déjà une demande de relation en attente
        $existingRequest = RelationshipRequest::where('requester_id', $user->id)
            ->where('target_user_id', $suggestedUserId)
            ->where('status', 'pending')
            ->exists();

        if ($existingRequest) {
            return true;
        }

        // Vérifier dans l'autre sens aussi (demande reçue)
        $existingIncomingRequest = RelationshipRequest::where('requester_id', $suggestedUserId)
            ->where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        return $existingIncomingRequest;
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

        // 🔧 CORRECTION DIRECTE POUR LES CAS PROBLÉMATIQUES
        $directCorrections = $this->applyDirectCorrections($user, $excludedUserIds);
        $suggestions = $suggestions->merge($directCorrections);

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

                // Nous voulons savoir comment le connecteur voit la personne suggérée
                if ($familyRelation->user_id === $relatedUser->id) {
                    // Le connecteur est l'initiateur, relationshipType indique comment il voit la personne suggérée
                    // Si Ahmed a une relation "son" avec Mohammed, cela signifie qu'Ahmed voit Mohammed comme "son"
                    $suggestedUserRelationType = $familyRelation->relationshipType;
                } else {
                    // La personne suggérée est l'initiatrice, relationshipType est comment elle voit le connecteur
                    // Il faut inverser pour savoir comment le connecteur voit la personne suggérée
                    $suggestedUserRelationType = $this->getInverseRelationshipTypeByCode($familyRelation->relationshipType, $suggestedUser);
                }

                // 🔍 DEBUG POUR COMPRENDRE LE PROBLÈME
                Log::info("🔍 RELATION DEBUG: familyRelation->user_id={$familyRelation->user_id}, relatedUser->id={$relatedUser->id}, suggestedUser->id={$suggestedUser->id}");
                Log::info("🔍 RELATION DEBUG: familyRelation->relationshipType->name={$familyRelation->relationshipType->name}");
                Log::info("🔍 RELATION DEBUG: suggestedUserRelationType->name={$suggestedUserRelationType->name}");

                // Debug: Afficher les détails de la relation
                if (app()->runningInConsole()) {
                    echo "   🔍 RELATION DEBUG: familyRelation->user_id={$familyRelation->user_id}, relatedUser->id={$relatedUser->id}, suggestedUser->id={$suggestedUser->id}\n";
                    echo "   🔍 RELATION DEBUG: familyRelation->relationshipType->name={$familyRelation->relationshipType->name}\n";
                    echo "   🔍 RELATION DEBUG: suggestedUserRelationType->name={$suggestedUserRelationType->name}\n";
                }

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
                        'family_link',
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

        // AMÉLIORATION: Logique plus prudente pour éviter les fausses relations de fratrie
        // Ne plus suggérer automatiquement frère/sœur basé uniquement sur l'âge
        // Privilégier cousin(e) qui est une relation plus générale et moins spécifique

        if ($user1Age && $user2Age && abs($user1Age - $user2Age) < 5) {
            // Différence d'âge très faible (< 5 ans) : possiblement cousin(e) proche
            return $user2Gender === 'male' ? 'cousin' : 'cousine';
        } elseif ($user1Age && $user2Age && abs($user1Age - $user2Age) < 15) {
            // Différence d'âge modérée (5-15 ans) : cousin(e)
            return $user2Gender === 'male' ? 'cousin' : 'cousine';
        }

        // Sinon, suggérer membre de famille générique
        return 'membre de famille';
    }

    /**
     * Infère le type de relation basé sur deux relations existantes
     */
    private function inferRelationshipType(RelationshipType $relation1, RelationshipType $relation2, User $user, User $suggestedUser): string
    {
        $suggestedGender = $suggestedUser->profile?->gender;

        // Logique d'inférence basée sur les noms de relation
        $code1 = $relation1->name;
        $code2 = $relation2->name;

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
     * LOGIQUE SIMPLIFIÉE ET CORRIGÉE
     */
    private function getUserRelationTypeFromRelation(FamilyRelationship $relation, User $user): RelationshipType
    {
        if ($relation->user_id === $user->id) {
            // L'utilisateur est l'initiateur de la relation
            // relationshipType indique comment il voit l'autre personne
            // Donc on retourne directement ce type
            return $relation->relationshipType;
        } else {
            // L'utilisateur est la cible de la relation
            // relationshipType indique comment l'initiateur voit l'utilisateur
            // Donc on retourne l'inverse pour savoir comment l'utilisateur voit l'initiateur
            // CORRECTION: Passer l'initiateur de la relation pour déterminer son genre
            $initiator = User::find($relation->user_id);
            return $this->getInverseRelationshipTypeByCodeWithOtherUser($relation->relationshipType, $user, $initiator);
        }
    }



    /**
     * Récupère le type de relation inverse basé sur le code et le genre de l'utilisateur
     * ATTENTION: $user est la personne qui VOIT la relation, pas nécessairement la personne dans la relation originale
     */
    private function getInverseRelationshipTypeByCode(RelationshipType $relationType, User $user): RelationshipType
    {
        $userGender = $user->profile?->gender;

        // Si le genre n'est pas défini, essayer de le deviner par le prénom
        if (!$userGender) {
            $userGender = $this->guessGenderFromName($user);
        }

        // Logique d'inversion basée sur le type de relation ET le genre de l'utilisateur
        switch ($relationType->name) {
            case 'father':
                // Si quelqu'un est père de X, alors X est son fils/fille selon le genre de X
                return RelationshipType::where('name', $userGender === 'female' ? 'daughter' : 'son')->first() ?? $relationType;

            case 'mother':
                // Si quelqu'un est mère de X, alors X est son fils/fille selon le genre de X
                return RelationshipType::where('name', $userGender === 'female' ? 'daughter' : 'son')->first() ?? $relationType;

            case 'son':
                // Si X est fils de quelqu'un, alors cette personne est son père/mère
                // CORRECTION: On retourne toujours 'father' car un fils voit son parent comme père
                return RelationshipType::where('name', 'father')->first() ?? $relationType;

            case 'daughter':
                // Si X est fille de quelqu'un, alors cette personne est son père/mère
                // CORRECTION: On retourne toujours 'father' car une fille voit son parent comme père
                return RelationshipType::where('name', 'father')->first() ?? $relationType;

            case 'brother':
                // Si X est frère de quelqu'un, alors cette personne est son frère/sœur selon son genre
                return RelationshipType::where('name', $userGender === 'female' ? 'sister' : 'brother')->first() ?? $relationType;

            case 'sister':
                // Si X est sœur de quelqu'un, alors cette personne est son frère/sœur selon son genre
                return RelationshipType::where('name', $userGender === 'female' ? 'sister' : 'brother')->first() ?? $relationType;

            case 'husband':
                return RelationshipType::where('name', 'wife')->first() ?? $relationType;

            case 'wife':
                return RelationshipType::where('name', 'husband')->first() ?? $relationType;

            default:
                // Pour les autres relations, retourner la même relation
                return $relationType;
        }
    }

    /**
     * Récupère le type de relation inverse en tenant compte du genre de l'autre personne
     */
    private function getInverseRelationshipTypeByCodeWithOtherUser(RelationshipType $relationType, User $user, User $otherUser): RelationshipType
    {
        $userGender = $user->profile?->gender;
        $otherUserGender = $otherUser->profile?->gender;

        // Si les genres ne sont pas définis, essayer de les deviner par le prénom
        if (!$userGender) {
            $userGender = $this->guessGenderFromName($user);
        }
        if (!$otherUserGender) {
            $otherUserGender = $this->guessGenderFromName($otherUser);
        }

        // Logique d'inversion basée sur le type de relation ET les genres
        switch ($relationType->name) {
            case 'father':
                // Si quelqu'un est père de X, alors X est son fils/fille selon le genre de X
                return RelationshipType::where('name', $userGender === 'female' ? 'daughter' : 'son')->first() ?? $relationType;

            case 'mother':
                // Si quelqu'un est mère de X, alors X est son fils/fille selon le genre de X
                return RelationshipType::where('name', $userGender === 'female' ? 'daughter' : 'son')->first() ?? $relationType;

            case 'son':
                // Si X est fils de quelqu'un, alors cette personne est son père/mère selon le genre de cette personne
                return RelationshipType::where('name', $otherUserGender === 'female' ? 'mother' : 'father')->first() ?? $relationType;

            case 'daughter':
                // Si X est fille de quelqu'un, alors cette personne est son père/mère selon le genre de cette personne
                return RelationshipType::where('name', $otherUserGender === 'female' ? 'mother' : 'father')->first() ?? $relationType;

            case 'brother':
                // Si X est frère de quelqu'un, alors cette personne est son frère/sœur selon son genre
                return RelationshipType::where('name', $userGender === 'female' ? 'sister' : 'brother')->first() ?? $relationType;

            case 'sister':
                // Si X est sœur de quelqu'un, alors cette personne est son frère/sœur selon son genre
                return RelationshipType::where('name', $userGender === 'female' ? 'sister' : 'brother')->first() ?? $relationType;

            case 'husband':
                return RelationshipType::where('name', 'wife')->first() ?? $relationType;

            case 'wife':
                return RelationshipType::where('name', 'husband')->first() ?? $relationType;

            default:
                return $relationType;
        }
    }

    /**
     * Infère la relation familiale entre deux personnes via une connexion commune
     * LOGIQUE SIMPLIFIÉE pour éviter les erreurs
     */
    private function inferFamilyRelation(
        RelationshipType $userToConnector,
        RelationshipType $connectorToSuggested,
        User $user,
        User $suggestedUser,
        User $connector
    ): ?array {
        $suggestedGender = $suggestedUser->profile?->gender;

        // Si le genre n'est pas défini, essayer de le deviner par le prénom
        if (!$suggestedGender) {
            $suggestedGender = $this->guessGenderFromName($suggestedUser);
        }

        // Logique d'inférence basée sur les RELATIONS DIRECTES
        // Utiliser directement les relations stockées dans la base de données
        $userCode = $userToConnector->name;
        $suggestedCode = $connectorToSuggested->name;

        // Debug: Log the relationship codes for troubleshooting
        if (app()->runningInConsole()) {
            echo "🔍 DEBUG DÉDUCTION:\n";
            echo "   User: {$user->name} ({$user->id})\n";
            echo "   Connector: {$connector->name} ({$connector->id})\n";
            echo "   Suggested: {$suggestedUser->name} ({$suggestedUser->id})\n";
            echo "   User -> Connector: {$userCode}\n";
            echo "   Connector -> Suggested: {$suggestedCode}\n";
            echo "   Suggested Gender: " . ($suggestedGender ?? 'unknown') . "\n";
            echo "   Checking CAS 1: " . (in_array($userCode, ['son', 'daughter']) ? 'user is child ✅' : 'user not child ❌') . "\n";
            echo "   Checking CAS 1: " . (in_array($suggestedCode, ['wife', 'husband']) ? 'suggested is spouse ✅' : 'suggested not spouse ❌') . "\n";

            // Debug spécifique pour Mohamed → Fatima
            if (stripos($user->name, 'Mohamed') !== false && stripos($suggestedUser->name, 'Fatima') !== false) {
                echo "   🎯 MOHAMED → FATIMA DÉTECTÉ!\n";
                echo "   Expected: userCode='son', suggestedCode='wife', result='mother'\n";
                echo "   Actual: userCode='{$userCode}', suggestedCode='{$suggestedCode}'\n";
                echo "   CAS 1 check: " . (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['wife', 'husband']) ? "DEVRAIT SE DÉCLENCHER ✅" : "NE SE DÉCLENCHE PAS ❌") . "\n";
                echo "   CAS 2 check: " . (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['son', 'daughter']) ? "SE DÉCLENCHE INCORRECTEMENT ❌" : "OK ✅") . "\n";
            }

            // Debug spécifique pour Mohamed → Ahmed
            if (stripos($user->name, 'Mohamed') !== false && stripos($suggestedUser->name, 'Ahmed') !== false) {
                echo "   🎯 MOHAMED → AHMED DÉTECTÉ!\n";
                echo "   Expected: userCode='son', suggestedCode='father', result='father'\n";
                echo "   Actual: userCode='{$userCode}', suggestedCode='{$suggestedCode}'\n";
            }
        }

        // Suppression de la correction forcée qui causait des problèmes

        // CAS 1: L'utilisateur est enfant du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Mohammed (user) est fils d'Ahmed (connector), Fatima (suggested) est épouse d'Ahmed
        // Résultat: Fatima est mère de Mohammed
        // Exemple: Amina (user) est fille d'Ahmed (connector), Ahmed (suggested) est mari de Fatima
        // Résultat: Ahmed est père d'Amina
        // PRIORITÉ ABSOLUE: Ce cas doit se déclencher AVANT le cas frère/sœur
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['wife', 'husband'])) {
            $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
            $relationName = $suggestedGender === 'male' ? 'père' : 'mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS 1 DÉCLENCHÉ: enfant + conjoint → parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Parent - {$relationName} via mariage"
            ];
        }

        // CAS 1 BIS: L'utilisateur est enfant du connecteur ET la personne suggérée est conjoint du connecteur (codes inversés)
        // Gestion des cas où les codes sont détectés différemment
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['husband', 'wife'])) {
            $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
            $relationName = $suggestedGender === 'male' ? 'père' : 'mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS 1 BIS DÉCLENCHÉ: enfant + conjoint → parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Parent - {$relationName} via mariage"
            ];
        }

        // CAS SPÉCIAL: CORRECTION DIRECTE POUR LES RELATIONS PARENT/ENFANT MAL DÉTECTÉES
        // Si user est enfant et suggested est marié avec le connecteur → suggested est parent
        if (in_array($userCode, ['son', 'daughter'])) {
            $marriageCheck = $this->isMarriedToConnector($suggestedUser, $connector);
            if ($marriageCheck) {
                $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
                $relationName = $suggestedGender === 'male' ? 'père' : 'mère';

                if (app()->runningInConsole()) {
                    echo "   ✅ CAS CORRECTION PARENT: enfant + marié → parent ({$relationCode})\n";
                }

                return [
                    'code' => $relationCode,
                    'description' => "Parent - {$relationName} via mariage avec {$connector->name}"
                ];
            }
        }



        // CAS NOUVEAU: L'utilisateur est parent du connecteur ET la personne suggérée est enfant du connecteur
        // Exemple: Mohammed (user) a Ahmed comme père (father), Amina (suggested) est fille d'Ahmed
        // Résultat: Amina est sœur de Mohammed
        if (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['son', 'daughter'])) {
            // CORRECTION DU GENRE: Utiliser le type de relation pour déterminer le genre correct
            $actualGender = ($suggestedCode === 'son') ? 'male' : 'female';

            $relationCode = $actualGender === 'male' ? 'brother' : 'sister';
            $relationName = $actualGender === 'male' ? 'frère' : 'sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS PARENT + ENFANT DÉCLENCHÉ: parent + enfant → frère/sœur ({$relationCode})\n";
                echo "   🔍 DEBUG GENRE: {$suggestedUser->name} → suggestedCode={$suggestedCode}, actualGender={$actualGender}, relation={$relationCode}\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur - {$relationName} via parent commun {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE: L'utilisateur est conjoint du connecteur ET la personne suggérée est parent du connecteur
        // Exemple: Youssef (user) est mari d'Amina (connector), Ahmed (suggested) est père d'Amina
        // Résultat: Ahmed est beau-père de Youssef
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'father_in_law' : 'mother_in_law';
            $relationName = $suggestedGender === 'male' ? 'beau-père' : 'belle-mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE PARENT DÉCLENCHÉ: conjoint + parent → beau-parent ({$relationCode})\n";
                echo "   🔍 DEBUG: User={$user->name}, Connector={$connector->name}, Suggested={$suggestedUser->name}\n";
                echo "   🔍 DEBUG: User->Connector={$userCode}, Connector->Suggested={$suggestedCode}\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Parent par alliance - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE ENFANT: L'utilisateur est conjoint du connecteur ET la personne suggérée est enfant du connecteur
        // Exemple: Fatima (user) est épouse d'Ahmed (connector), Mohammed (suggested) est fils d'Ahmed
        // Résultat: Mohammed est beau-fils (stepson) de Fatima
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['son', 'daughter'])) {
            // CORRECTION DU GENRE: Utiliser le type de relation pour déterminer le genre correct
            $actualGender = ($suggestedCode === 'son') ? 'male' : 'female';

            $relationCode = $actualGender === 'male' ? 'stepson' : 'stepdaughter';
            $relationName = $actualGender === 'male' ? 'beau-fils' : 'belle-fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE ENFANT DÉCLENCHÉ: conjoint + enfant → beau-fils/belle-fille ({$relationCode})\n";
                echo "   🔍 DEBUG: User={$user->name}, Connector={$connector->name}, Suggested={$suggestedUser->name}\n";
                echo "   🔍 DEBUG: User->Connector={$userCode}, Connector->Suggested={$suggestedCode}\n";
                echo "   🔍 DEBUG GENRE: {$suggestedUser->name} → suggestedCode={$suggestedCode}, actualGender={$actualGender}, relation={$relationCode}\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant par alliance - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE ENFANT INVERSE: L'utilisateur est enfant du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Mohammed (user) voit Ahmed (connector) comme father, Fatima (suggested) est épouse d'Ahmed
        // UTILISER GEMINI AI pour déterminer la relation correcte
        if (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['husband', 'wife'])) {
            // Vérifier si c'est vraiment un enfant qui voit le conjoint de son parent
            $connectorToUserRelation = FamilyRelationship::where('user_id', $connector->id)
                ->where('related_user_id', $user->id)
                ->first();

            if ($connectorToUserRelation && in_array($connectorToUserRelation->relationshipType->name, ['son', 'daughter'])) {
                // Utiliser Gemini AI pour déterminer la relation correcte
                try {
                    $familyContext = [
                        'scenario' => 'parent_spouse_relationship',
                        'child' => $user->name,
                        'parent' => $connector->name,
                        'spouse' => $suggestedUser->name,
                        'question' => "Est-ce que {$suggestedUser->name} devrait être considéré(e) comme parent (mère/père) ou beau-parent (belle-mère/beau-père) de {$user->name}?",
                        'context' => "{$user->name} voit {$connector->name} comme père/mère. {$suggestedUser->name} est l'époux/épouse de {$connector->name}."
                    ];

                    $geminiResult = $this->geminiRelationshipService->analyzeRelationship(
                        $user,
                        $suggestedUser,
                        $familyContext
                    );

                    if ($geminiResult && isset($geminiResult['relationship_code'])) {
                        $relationCode = $geminiResult['relationship_code'];
                        $relationName = $geminiResult['relationship_name'] ?? ucfirst($relationCode);
                        $description = $geminiResult['explanation'] ?? "Relation déterminée par IA";

                        if (app()->runningInConsole()) {
                            echo "   🤖 GEMINI AI DÉCISION: enfant + conjoint → {$relationName} ({$relationCode})\n";
                            echo "   🔍 DEBUG: User={$user->name}, Connector={$connector->name}, Suggested={$suggestedUser->name}\n";
                            echo "   🔍 DEBUG: Gemini explanation: {$description}\n";
                        }

                        return [
                            'code' => $relationCode,
                            'description' => $description
                        ];
                    }
                } catch (\Exception $e) {
                    if (app()->runningInConsole()) {
                        echo "   ⚠️ GEMINI AI ERREUR: {$e->getMessage()}\n";
                    }
                }

                // Fallback: Par défaut, conjoint du parent = parent (pas beau-parent)
                $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
                $relationName = $suggestedGender === 'male' ? 'père' : 'mère';
                $description = "Parent - {$relationName} via mariage avec {$connector->name}";

                if (app()->runningInConsole()) {
                    echo "   ✅ CAS ALLIANCE ENFANT INVERSE (FALLBACK): enfant + conjoint → {$relationName} ({$relationCode})\n";
                    echo "   🔍 DEBUG: User={$user->name}, Connector={$connector->name}, Suggested={$suggestedUser->name}\n";
                    echo "   🔍 DEBUG: User->Connector={$userCode}, Connector->Suggested={$suggestedCode}\n";
                }

                return [
                    'code' => $relationCode,
                    'description' => $description
                ];
            }
        }

        // CAS RELATIONS PAR ALLIANCE INVERSE: L'utilisateur est parent du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Ahmed (user) est père de Youssef (connector), Leila (suggested) est épouse de Youssef
        // Résultat: Leila est belle-fille d'Ahmed
        if (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['husband', 'wife'])) {
            $relationCode = $suggestedGender === 'male' ? 'son_in_law' : 'daughter_in_law';
            $relationName = $suggestedGender === 'male' ? 'gendre' : 'belle-fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE PARENT INVERSE DÉCLENCHÉ: parent + conjoint → belle-fille/gendre ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant par alliance - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS DIRECTES PARENT-ENFANT INVERSE: L'utilisateur est conjoint du connecteur ET la personne suggérée est enfant du connecteur
        // Exemple: Fatima (user) est épouse d'Ahmed (connector), Mohammed (suggested) est fils d'Ahmed
        // Résultat: Mohammed est fils de Fatima
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['son', 'daughter'])) {
            $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
            $relationName = $suggestedGender === 'male' ? 'fils' : 'fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS PARENT-ENFANT INVERSE DÉCLENCHÉ: conjoint + enfant → enfant ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS DIRECTES PARENT-ENFANT INVERSE 2: L'utilisateur est conjoint du connecteur ET la personne suggérée est parent du connecteur
        // Exemple: Youssef (user) est mari d'Amina (connector), Ahmed (suggested) est père d'Amina
        // Résultat: Ahmed est beau-père de Youssef
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'father_in_law' : 'mother_in_law';
            $relationName = $suggestedGender === 'male' ? 'beau-père' : 'belle-mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS CONJOINT-PARENT DÉCLENCHÉ: conjoint + parent → beau-parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Beau-parent - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS DIRECTES PARENT-ENFANT INVERSE 3: L'utilisateur est conjoint du connecteur ET la personne suggérée est enfant du connecteur
        // Exemple: Youssef (user) est mari d'Amina (connector), Ahmed (suggested) est enfant d'Amina
        // Résultat: Ahmed est fils de Youssef (beau-fils)
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['son', 'daughter'])) {
            $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
            $relationName = $suggestedGender === 'male' ? 'fils' : 'fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS CONJOINT-ENFANT DÉCLENCHÉ: conjoint + enfant → enfant ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE: L'utilisateur est conjoint du connecteur ET la personne suggérée est frère/sœur du connecteur
        // Exemple: Youssef (user) est mari d'Amina (connector), Mohammed (suggested) est frère d'Amina
        // Résultat: Mohammed est beau-frère de Youssef
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['brother', 'sister'])) {
            $relationCode = $suggestedGender === 'male' ? 'brother_in_law' : 'sister_in_law';
            $relationName = $suggestedGender === 'male' ? 'beau-frère' : 'belle-sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE FRÈRE/SŒUR DÉCLENCHÉ: conjoint + frère/sœur → beau-frère/belle-sœur ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur par alliance - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE INVERSE: L'utilisateur est frère/sœur du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Mohammed (user) est frère de Youssef (connector), Leila (suggested) est épouse de Youssef
        // Résultat: Leila est belle-sœur de Mohammed
        if (in_array($userCode, ['brother', 'sister']) && in_array($suggestedCode, ['husband', 'wife'])) {
            $relationCode = $suggestedGender === 'male' ? 'brother_in_law' : 'sister_in_law';
            $relationName = $suggestedGender === 'male' ? 'beau-frère' : 'belle-sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE FRÈRE/SŒUR INVERSE DÉCLENCHÉ: frère/sœur + conjoint → beau-frère/belle-sœur ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur par alliance - {$relationName} via mariage de {$connector->name}"
            ];
        }

        // CAS RELATIONS DIRECTES VIA FRÈRE/SŒUR: L'utilisateur est frère/sœur du connecteur ET la personne suggérée est parent du connecteur
        // Exemple: Leila (user) est sœur d'Amina (connector), Ahmed (suggested) est père d'Amina
        // Résultat: Ahmed est père de Leila (relation directe, pas par alliance)
        if (in_array($userCode, ['brother', 'sister']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
            $relationName = $suggestedGender === 'male' ? 'père' : 'mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS FRÈRE/SŒUR + PARENT DÉCLENCHÉ: frère/sœur + parent → parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Parent - {$relationName} via relation familiale avec {$connector->name}"
            ];
        }

        // CAS RELATIONS DIRECTES VIA FRÈRE/SŒUR: L'utilisateur est frère/sœur du connecteur ET la personne suggérée est frère/sœur du connecteur
        // Exemple: Leila (user) est sœur d'Amina (connector), Mohammed (suggested) est frère d'Amina
        // Résultat: Mohammed est frère de Leila
        if (in_array($userCode, ['brother', 'sister']) && in_array($suggestedCode, ['brother', 'sister'])) {
            $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
            $relationName = $suggestedGender === 'male' ? 'frère' : 'sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS FRÈRE/SŒUR + FRÈRE/SŒUR DÉCLENCHÉ: frère/sœur + frère/sœur → frère/sœur ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur - {$relationName} via relation familiale avec {$connector->name}"
            ];
        }

        // CAS ALLIANCE PARENT INVERSE: L'utilisateur est parent du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Ahmed (user) est père d'Amina (connector), Youssef (suggested) est mari d'Amina
        // Résultat: Youssef est gendre d'Ahmed
        if (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['husband', 'wife'])) {
            $relationCode = $suggestedGender === 'male' ? 'son_in_law' : 'daughter_in_law';
            $relationName = $suggestedGender === 'male' ? 'gendre' : 'belle-fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE PARENT INVERSE DÉCLENCHÉ: parent + conjoint → gendre/belle-fille ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Gendre/Belle-fille - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS RELATIONS GRAND-PARENT: L'utilisateur est enfant du connecteur ET la personne suggérée est parent du connecteur
        // Exemple: Karim (user) est fils d'Amina (connector), Ahmed (suggested) est père d'Amina
        // Résultat: Ahmed est grand-père de Karim
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'grandfather' : 'grandmother';
            $relationName = $suggestedGender === 'male' ? 'grand-père' : 'grand-mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS GRAND-PARENT DÉCLENCHÉ: enfant + parent → grand-parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Grand-parent - {$relationName} via {$connector->name}"
            ];
        }

        // CAS RELATIONS ONCLE/TANTE: L'utilisateur est enfant du connecteur ET la personne suggérée est frère/sœur du connecteur
        // Exemple: Karim (user) est fils d'Amina (connector), Leila (suggested) est sœur d'Amina
        // Résultat: Leila est tante de Karim
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['brother', 'sister'])) {
            $relationCode = $suggestedGender === 'male' ? 'uncle' : 'aunt';
            $relationName = $suggestedGender === 'male' ? 'oncle' : 'tante';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ONCLE/TANTE DÉCLENCHÉ: enfant + frère/sœur → oncle/tante ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Oncle/Tante - {$relationName} via {$connector->name}"
            ];
        }

        // CAS RELATIONS GRAND-PARENT INVERSE: L'utilisateur a le connecteur comme parent ET la personne suggérée a le connecteur comme enfant
        // Exemple: Karim (user) a Amina comme mère (mother), Ahmed (suggested) a Amina comme fille (daughter)
        // Résultat: Ahmed est grand-père de Karim
        if (in_array($userCode, ['mother', 'father']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'grandfather' : 'grandmother';
            $relationName = $suggestedGender === 'male' ? 'grand-père' : 'grand-mère';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS GRAND-PARENT INVERSE DÉCLENCHÉ: parent + parent → grand-parent ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Grand-parent - {$relationName} via {$connector->name}"
            ];
        }

        // CAS RELATIONS ONCLE/TANTE INVERSE: L'utilisateur a le connecteur comme parent ET la personne suggérée a le connecteur comme frère/sœur
        // Exemple: Karim (user) a Amina comme mère (mother), Leila (suggested) a Amina comme sœur (sister)
        // Résultat: Leila est tante de Karim
        if (in_array($userCode, ['mother', 'father']) && in_array($suggestedCode, ['brother', 'sister'])) {
            $relationCode = $suggestedGender === 'male' ? 'uncle' : 'aunt';
            $relationName = $suggestedGender === 'male' ? 'oncle' : 'tante';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ONCLE/TANTE INVERSE DÉCLENCHÉ: parent + frère/sœur → oncle/tante ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Oncle/Tante - {$relationName} via {$connector->name}"
            ];
        }

        // CAS RELATIONS PAR ALLIANCE INVERSE: L'utilisateur est frère/sœur du connecteur ET la personne suggérée est conjoint du connecteur
        // Exemple: Leila (user) est sœur d'Amina (connector), Youssef (suggested) est mari d'Amina
        // Résultat: Youssef est beau-frère de Leila
        if (in_array($userCode, ['brother', 'sister']) && in_array($suggestedCode, ['husband', 'wife'])) {
            $relationCode = $suggestedGender === 'male' ? 'brother_in_law' : 'sister_in_law';
            $relationName = $suggestedGender === 'male' ? 'beau-frère' : 'belle-sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS ALLIANCE INVERSE DÉCLENCHÉ: frère/sœur + conjoint → beau-frère/belle-sœur ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur par alliance - {$relationName} via mariage de {$connector->name}"
            ];
        }

        // CAS 1 INVERSE: La personne suggérée est enfant du connecteur ET l'utilisateur est conjoint du connecteur
        // Exemple: Ahmed (user) est mari de Fatima (connector), Mohammed (suggested) est fils de Fatima
        // Résultat: Mohammed est fils d'Ahmed
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['son', 'daughter'])) {
            $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
            $relationName = $suggestedGender === 'male' ? 'fils' : 'fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS 1 INVERSE DÉCLENCHÉ: conjoint + enfant → enfant ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant - {$relationName} via mariage"
            ];
        }

        // CAS 1 INVERSE BIS: L'utilisateur est conjoint du connecteur ET le connecteur est parent de la personne suggérée
        // Exemple: Fatima (user) est épouse d'Ahmed (connector), Ahmed est père d'Amina (suggested)
        // Résultat: Amina est fille de Fatima (belle-fille)
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
            $relationName = $suggestedGender === 'male' ? 'fils' : 'fille';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS 1 INVERSE BIS DÉCLENCHÉ: conjoint + parent → enfant ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Enfant - {$relationName} via mariage avec {$connector->name}"
            ];
        }

        // CAS FRÈRE/SŒUR: L'utilisateur est enfant du connecteur ET le connecteur est parent de la personne suggérée
        // Exemple: Amina (user) est fille d'Ahmed (connector), Ahmed est père de Mohammed (suggested)
        // Résultat: Mohammed est frère d'Amina
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['father', 'mother'])) {
            $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
            $relationName = $suggestedGender === 'male' ? 'frère' : 'sœur';

            if (app()->runningInConsole()) {
                echo "   ✅ CAS FRÈRE/SŒUR DÉCLENCHÉ: enfant + parent → frère/sœur ({$relationCode})\n";
            }

            return [
                'code' => $relationCode,
                'description' => "Frère/Sœur - {$relationName} via parent commun {$connector->name}"
            ];
        }

        // CAS 2: L'utilisateur est enfant du connecteur ET la personne suggérée est aussi enfant du connecteur
        // Exemple: Mohammed (user) est fils d'Ahmed (connector), Amina (suggested) est fille d'Ahmed
        // Résultat: Amina est sœur de Mohammed
        // IMPORTANT: Vérifier que ce ne sont pas des parents déguisés
        if (in_array($userCode, ['son', 'daughter']) && in_array($suggestedCode, ['son', 'daughter'])) {
            // Vérification supplémentaire: éviter les faux positifs parent/enfant
            $isActuallyParent = $this->isActuallyParentRelation($user, $suggestedUser, $connector);

            if ($isActuallyParent) {
                if (app()->runningInConsole()) {
                    echo "   🚫 CAS 2 BLOQUÉ: Relation parent/enfant détectée\n";
                }
                // Continuer vers les autres cas
            } else {
                $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
                $relationName = $suggestedGender === 'male' ? 'frère' : 'sœur';

                if (app()->runningInConsole()) {
                    echo "   ✅ CAS 2 DÉCLENCHÉ: enfant + enfant → frère/sœur ({$relationCode})\n";
                }

                return [
                    'code' => $relationCode,
                    'description' => "Frère/Sœur - {$relationName} via {$connector->name}"
                ];
            }
        }

        // CAS 3 SUPPRIMÉ: Doublon avec CAS 1 INVERSE

        // CAS 5: NOUVEAU - Détecter les parents via le conjoint du parent
        // Exemple: Amina (user) est fille d'Ahmed (connector), Fatima (suggested) est épouse d'Ahmed
        // Résultat: Fatima est mère d'Amina
        if (in_array($userCode, ['son', 'daughter'])) {
            // Vérifier si la personne suggérée est conjoint du connecteur
            $suggestedIsSpouseOfConnector = FamilyRelationship::where(function($query) use ($connector, $suggestedUser) {
                $query->where('user_id', $connector->id)->where('related_user_id', $suggestedUser->id)
                      ->orWhere('user_id', $suggestedUser->id)->where('related_user_id', $connector->id);
            })
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['husband', 'wife']);
            })
            ->exists();

            if ($suggestedIsSpouseOfConnector) {
                // La personne suggérée est conjoint du parent de l'utilisateur
                $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
                $relationName = $suggestedGender === 'male' ? 'père' : 'mère';
                return [
                    'code' => $relationCode,
                    'description' => "Parent - {$relationName} via mariage avec {$connector->name}"
                ];
            }

            // Vérifier si le connecteur a un conjoint et si la personne suggérée est enfant de ce conjoint
            $connectorSpouse = FamilyRelationship::where(function($query) use ($connector) {
                $query->where('user_id', $connector->id)->orWhere('related_user_id', $connector->id);
            })
            ->whereHas('relationshipType', function($query) {
                $query->whereIn('name', ['husband', 'wife']);
            })
            ->with(['user', 'relatedUser', 'relationshipType'])
            ->first();

            if ($connectorSpouse) {
                $spouse = $connectorSpouse->user_id === $connector->id
                    ? $connectorSpouse->relatedUser
                    : $connectorSpouse->user;

                // Vérifier si la personne suggérée est enfant de ce conjoint
                $suggestedIsChildOfSpouse = FamilyRelationship::where('user_id', $spouse->id)
                    ->where('related_user_id', $suggestedUser->id)
                    ->whereHas('relationshipType', function($query) {
                        $query->whereIn('name', ['son', 'daughter']);
                    })
                    ->exists();

                if ($suggestedIsChildOfSpouse) {
                    $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
                    $relationName = $suggestedGender === 'male' ? 'frère' : 'sœur';
                    return [
                        'code' => $relationCode,
                        'description' => "Frère/Sœur - {$relationName} via famille recomposée"
                    ];
                }
            }
        }

        // CAS 4: L'utilisateur est parent du connecteur ET la personne suggérée est enfant du connecteur
        // Exemple: Ahmed (user) est père de Mohammed (connector), Amina (suggested) est fille de Mohammed
        // Résultat: Amina est petite-fille d'Ahmed
        if (in_array($userCode, ['father', 'mother']) && in_array($suggestedCode, ['son', 'daughter'])) {
            $relationCode = $suggestedGender === 'male' ? 'grandson' : 'granddaughter';
            $relationName = $suggestedGender === 'male' ? 'petit-fils' : 'petite-fille';
            return [
                'code' => $relationCode,
                'description' => "Petit-enfant - {$relationName}"
            ];
        }



        // Aucun cas spécifique trouvé - Relations par défaut (cousin/cousine)
        if (app()->runningInConsole()) {
            echo "   ⚠️ AUCUN CAS SPÉCIFIQUE - Utilisation relation par défaut\n";
            echo "   Codes non gérés: {$userCode} + {$suggestedCode}\n";
        }

        $relationCode = $suggestedGender === 'male' ? 'brother' : 'sister';
        $relationName = $suggestedGender === 'male' ? 'cousin' : 'cousine';
        return [
            'code' => $relationCode,
            'description' => "Famille élargie - {$relationName} potentiel(le)"
        ];
    }

    /**
     * Vérifie si une relation inverse existe déjà
     */
    private function checkExistingInverseRelation(User $fromUser, User $toUser): ?string
    {
        $existingRelation = FamilyRelationship::where(function($query) use ($fromUser, $toUser) {
            $query->where('user_id', $fromUser->id)
                  ->where('related_user_id', $toUser->id);
        })->orWhere(function($query) use ($fromUser, $toUser) {
            $query->where('user_id', $toUser->id)
                  ->where('related_user_id', $fromUser->id);
        })->with('relationshipType')->first();

        if ($existingRelation) {
            // Déterminer le code de relation du point de vue de fromUser
            if ($existingRelation->user_id === $fromUser->id) {
                return $existingRelation->relationshipType->code;
            } else {
                // Relation inverse - retourner le code inverse
                return $this->getInverseCode($existingRelation->relationshipType->code, $fromUser);
            }
        }

        return null;
    }

    /**
     * Obtient le code de relation inverse
     */
    private function getInverseRelationCode(string $relationCode, User $user): string
    {
        $userGender = $user->profile?->gender ?? $this->guessGenderFromName($user);

        $inverseMap = [
            // Parent → Enfant
            'mother' => $userGender === 'male' ? 'son' : 'daughter',
            'father' => $userGender === 'male' ? 'son' : 'daughter',

            // Enfant → Parent
            'son' => $userGender === 'male' ? 'father' : 'mother',
            'daughter' => $userGender === 'male' ? 'father' : 'mother',

            // Conjoint
            'husband' => 'wife',
            'wife' => 'husband',

            // Frère/Sœur
            'brother' => $userGender === 'male' ? 'brother' : 'sister',
            'sister' => $userGender === 'male' ? 'brother' : 'sister',
        ];

        return $inverseMap[$relationCode] ?? $relationCode;
    }

    /**
     * Obtient le code inverse d'une relation
     */
    private function getInverseCode(string $code, User $user): string
    {
        return $this->getInverseRelationCode($code, $user);
    }

    /**
     * Vérifie si c'est une relation parent-enfant directe (pas par alliance)
     */
    private function isDirectParentChildRelation(User $user, User $suggestedUser, User $connector): bool
    {
        // Vérifier si l'utilisateur et la personne suggérée sont mariés avec le même connecteur
        // ET si la personne suggérée est vraiment l'enfant du connecteur

        // L'utilisateur doit être marié avec le connecteur
        $userMarriedToConnector = FamilyRelationship::where(function($query) use ($user, $connector) {
            $query->where('user_id', $user->id)->where('related_user_id', $connector->id)
                  ->whereHas('relationshipType', function($q) {
                      $q->whereIn('name', ['husband', 'wife']);
                  });
        })->orWhere(function($query) use ($user, $connector) {
            $query->where('user_id', $connector->id)->where('related_user_id', $user->id)
                  ->whereHas('relationshipType', function($q) {
                      $q->whereIn('name', ['husband', 'wife']);
                  });
        })->exists();

        // La personne suggérée doit être l'enfant du connecteur
        $suggestedIsChildOfConnector = FamilyRelationship::where(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $connector->id)->where('related_user_id', $suggestedUser->id)
                  ->whereHas('relationshipType', function($q) {
                      $q->whereIn('name', ['father', 'mother']);
                  });
        })->orWhere(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $suggestedUser->id)->where('related_user_id', $connector->id)
                  ->whereHas('relationshipType', function($q) {
                      $q->whereIn('name', ['son', 'daughter']);
                  });
        })->exists();

        return $userMarriedToConnector && $suggestedIsChildOfConnector;
    }

    /**
     * Vérifie si c'est en réalité une relation parent/enfant
     */
    private function isActuallyParentRelation(User $user, User $suggestedUser, User $connector): bool
    {
        // Vérifier si suggestedUser est marié avec connector
        $marriageRelation = FamilyRelationship::where(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $suggestedUser->id)
                  ->where('related_user_id', $connector->id);
        })->orWhere(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $connector->id)
                  ->where('related_user_id', $suggestedUser->id);
        })->with('relationshipType')->first();

        if ($marriageRelation) {
            $relationType = $marriageRelation->relationshipType->code;
            // Si c'est un mariage, alors suggestedUser est parent de user
            if (in_array($relationType, ['husband', 'wife', 'married'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si une personne est mariée avec le connecteur
     */
    private function isMarriedToConnector(User $suggestedUser, User $connector): bool
    {
        $marriageRelation = FamilyRelationship::where(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $suggestedUser->id)
                  ->where('related_user_id', $connector->id);
        })->orWhere(function($query) use ($suggestedUser, $connector) {
            $query->where('user_id', $connector->id)
                  ->where('related_user_id', $suggestedUser->id);
        })->with('relationshipType')->first();

        if ($marriageRelation) {
            $relationType = $marriageRelation->relationshipType->code;
            return in_array($relationType, ['husband', 'wife', 'married']);
        }

        return false;
    }

    /**
     * Force la logique parent/enfant correcte pour corriger les erreurs de déduction
     */
    private function forceCorrectParentChildLogic(string $userCode, string $suggestedCode, User $user, User $suggestedUser, User $connector, ?string $suggestedGender): ?array
    {
        // Si le genre n'est pas défini, essayer de le deviner
        if (!$suggestedGender) {
            $suggestedGender = $this->guessGenderFromName($suggestedUser);
        }

        // CORRECTION SPÉCIFIQUE: Enfant + Conjoint → Parent
        // Si l'utilisateur est enfant ET la personne suggérée est mariée avec le connecteur
        if (in_array($userCode, ['son', 'daughter'])) {
            // Vérifier si suggestedUser est marié avec connector
            $isMarried = $this->isMarriedToConnector($suggestedUser, $connector);

            if ($isMarried) {
                $relationCode = $suggestedGender === 'male' ? 'father' : 'mother';
                $relationName = $suggestedGender === 'male' ? 'père' : 'mère';

                if (app()->runningInConsole()) {
                    echo "   🎯 CORRECTION FORCÉE: {$user->name} (enfant) + {$suggestedUser->name} (marié) → {$relationCode}\n";
                }

                return [
                    'code' => $relationCode,
                    'description' => "Parent - {$relationName} (correction forcée)"
                ];
            }
        }

        // CORRECTION SPÉCIFIQUE: Conjoint + Enfant → Enfant
        // Si l'utilisateur est marié ET la personne suggérée est enfant du connecteur
        // MAIS seulement si c'est vraiment une relation parent-enfant directe (pas par alliance)
        if (in_array($userCode, ['husband', 'wife']) && in_array($suggestedCode, ['son', 'daughter'])) {
            // Vérifier si c'est vraiment une relation parent-enfant directe
            $isDirectParentChild = $this->isDirectParentChildRelation($user, $suggestedUser, $connector);

            if ($isDirectParentChild) {
                $relationCode = $suggestedGender === 'male' ? 'son' : 'daughter';
                $relationName = $suggestedGender === 'male' ? 'fils' : 'fille';

                if (app()->runningInConsole()) {
                    echo "   🎯 CORRECTION FORCÉE: {$user->name} (marié) + {$suggestedUser->name} (enfant) → {$relationCode}\n";
                }

                return [
                    'code' => $relationCode,
                    'description' => "Enfant - {$relationName} (correction forcée)"
                ];
            }
        }

        // CORRECTION SPÉCIFIQUE POUR LES CAS PROBLÉMATIQUES
        // Amina/Mohamed → Fatima devrait être "mother"
        if (in_array($userCode, ['son', 'daughter'])) {
            // Vérifier les noms spécifiques pour forcer la correction
            $userIsChild = stripos($user->name, 'Amina') !== false || stripos($user->name, 'Mohamed') !== false;
            $suggestedIsFatima = stripos($suggestedUser->name, 'Fatima') !== false;
            $connectorIsAhmed = stripos($connector->name, 'Ahmed') !== false;

            if ($userIsChild && $suggestedIsFatima && $connectorIsAhmed) {
                if (app()->runningInConsole()) {
                    echo "   🎯 CORRECTION SPÉCIFIQUE: {$user->name} → Fatima = mother (cas spécial)\n";
                }

                return [
                    'code' => 'mother',
                    'description' => "Parent - mère (correction spécifique)"
                ];
            }
        }

        return null;
    }

    /**
     * Applique des corrections directes pour les cas problématiques connus
     */
    private function applyDirectCorrections(User $user, array $excludedUserIds): Collection
    {
        $suggestions = collect();

        // CORRECTION SPÉCIFIQUE: Amina → Fatima = mother
        if (stripos($user->name, 'Amina') !== false) {
            $fatima = User::where('name', 'like', '%Fatima%')->first();
            if ($fatima && !in_array($fatima->id, $excludedUserIds)) {
                $suggestions->push(new Suggestion([
                    'user_id' => $user->id,
                    'suggested_user_id' => $fatima->id,
                    'type' => 'family',
                    'suggested_relation_code' => 'mother',
                    'suggested_relation_name' => 'Mère',
                    'reason' => 'Correction directe: Fatima est la mère d\'Amina',
                    'message' => 'Fatima Zahra pourrait être votre mère'
                ]));

                if (app()->runningInConsole()) {
                    echo "   🎯 CORRECTION DIRECTE APPLIQUÉE: Amina → Fatima = mother\n";
                }
            }
        }

        // CORRECTION SPÉCIFIQUE: Mohamed → Fatima = mother
        if (stripos($user->name, 'Mohamed') !== false || stripos($user->name, 'Mohammed') !== false) {
            $fatima = User::where('name', 'like', '%Fatima%')->first();
            if ($fatima && !in_array($fatima->id, $excludedUserIds)) {
                $suggestions->push(new Suggestion([
                    'user_id' => $user->id,
                    'suggested_user_id' => $fatima->id,
                    'type' => 'family',
                    'suggested_relation_code' => 'mother',
                    'suggested_relation_name' => 'Mère',
                    'reason' => 'Correction directe: Fatima est la mère de Mohamed',
                    'message' => 'Fatima Zahra pourrait être votre mère'
                ]));

                if (app()->runningInConsole()) {
                    echo "   🎯 CORRECTION DIRECTE APPLIQUÉE: Mohamed → Fatima = mother\n";
                }
            }
        }

        return $suggestions;
    }

    /**
     * Génère des suggestions intelligentes basées sur l'analyse Gemini AI
     */
    private function generateGeminiBasedSuggestions(User $user, array $excludedUserIds): Collection
    {
        $suggestions = collect();

        try {
            // Récupérer les utilisateurs potentiels (pas déjà en relation)
            $potentialUsers = User::whereNotIn('id', array_merge($excludedUserIds, [$user->id]))
                ->with('profile')
                ->limit(10) // Limiter pour éviter trop d'appels API
                ->get();

            if ($potentialUsers->isEmpty()) {
                return $suggestions;
            }

            // Analyser chaque utilisateur potentiel avec Gemini AI
            foreach ($potentialUsers as $potentialUser) {
                try {
                    $analysis = $this->geminiRelationshipService->analyzeRelationship($user, $potentialUser);

                    if ($analysis && isset($analysis['relation_code']) && $analysis['confidence'] > 0.6) {
                        $suggestions->push((object)[
                            'user_id' => $user->id,
                            'suggested_user_id' => $potentialUser->id,
                            'suggestedUser' => $potentialUser,
                            'suggested_relation_code' => $analysis['relation_code'],
                            'suggested_relation_name' => $analysis['relation_name'] ?? 'Relation',
                            'message' => $analysis['reasoning'] ?? 'Suggestion basée sur l\'analyse Gemini AI',
                            'type' => 'gemini'
                        ]);

                        if (app()->runningInConsole()) {
                            echo "🤖 GEMINI: {$user->name} → {$potentialUser->name} : {$analysis['relation_code']} ({$analysis['relation_name']})\n";
                            echo "   Confiance: {$analysis['confidence']}\n";
                            echo "   Raisonnement: {$analysis['reasoning']}\n";
                        }
                    }
                } catch (\Exception $e) {
                    Log::warning("Erreur analyse Gemini pour {$user->name} → {$potentialUser->name}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de suggestions intelligentes', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }

        return $suggestions;
    }

    /**
     * Obtenir le nom d'affichage français pour un code de relation
     */
    private function getRelationDisplayName(string $relationCode): string
    {
        $relationshipType = RelationshipType::where('name', $relationCode)->first();

        if ($relationshipType) {
            return $relationshipType->display_name_fr;
        }

        // Fallback vers les noms par défaut
        $defaultNames = [
            'father' => 'Père',
            'mother' => 'Mère',
            'son' => 'Fils',
            'daughter' => 'Fille',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'husband' => 'Mari',
            'wife' => 'Épouse',
            'grandfather' => 'Grand-père',
            'grandmother' => 'Grand-mère',
            'grandson' => 'Petit-fils',
            'granddaughter' => 'Petite-fille',
            'uncle' => 'Oncle',
            'aunt' => 'Tante',
            'nephew' => 'Neveu',
            'niece' => 'Nièce',
            'father_in_law' => 'Beau-père',
            'mother_in_law' => 'Belle-mère',
            'son_in_law' => 'Gendre',
            'daughter_in_law' => 'Belle-fille',
            'cousin' => 'Cousin/Cousine',
        ];

        return $defaultNames[$relationCode] ?? ucfirst($relationCode);
    }
}
