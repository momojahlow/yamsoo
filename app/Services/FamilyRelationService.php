<?php

namespace App\Services;

use App\Events\RelationshipAccepted;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Models\Conversation;
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
        if ($requester && $target && $createdRelationship) {
            event(new RelationshipAccepted($requester, $target, $request));

            // Déclencher l'événement MemberAdded pour les nouvelles suggestions
            event(new \App\Events\MemberAdded($target, $requester, $createdRelationship));
        }

        return $createdRelationship;
    }

    /**
     * Créer directement une relation familiale sans passer par une demande
     */
    public function createDirectRelationship(
        User $requester,
        User $target,
        RelationshipType $relationshipType,
        string $message = ''
    ): FamilyRelationship {
        $createdRelationship = null;

        DB::transaction(function () use ($requester, $target, $relationshipType, $message, &$createdRelationship) {
            // Créer la relation familiale principale
            $createdRelationship = FamilyRelationship::create([
                'user_id' => $requester->id,
                'related_user_id' => $target->id,
                'relationship_type_id' => $relationshipType->id,
                'status' => 'accepted',
                'accepted_at' => now(),
                'created_automatically' => true,
            ]);

            // Créer la relation inverse si nécessaire
            $inverseType = $this->getInverseRelationshipType($relationshipType->id, $requester, $target);
            if ($inverseType) {
                FamilyRelationship::create([
                    'user_id' => $target->id,
                    'related_user_id' => $requester->id,
                    'relationship_type_id' => $inverseType->id,
                    'status' => 'accepted',
                    'accepted_at' => now(),
                    'created_automatically' => true,
                ]);
            }
        });

        // Déduire les relations automatiques
        if ($createdRelationship) {
            // Déduire les relations pour le demandeur
            $deducedForRequester = $this->intelligentRelationshipService->deduceRelationships(
                $requester,
                $target,
                $relationshipType->code
            );
            $this->intelligentRelationshipService->createDeducedRelationships($deducedForRequester);

            // Déduire les relations pour la cible (relation inverse)
            $inverseType = $this->getInverseRelationshipType($relationshipType->id, $requester, $target);
            if ($inverseType) {
                $deducedForTarget = $this->intelligentRelationshipService->deduceRelationships(
                    $target,
                    $requester,
                    $inverseType->code
                );
                $this->intelligentRelationshipService->createDeducedRelationships($deducedForTarget);
            }
        }

        Log::info("Relation familiale créée directement", [
            'requester' => $requester->name,
            'target' => $target->name,
            'relation' => $relationshipType->name_fr,
            'message' => $message
        ]);

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

    private function getInverseRelationshipType(int $relationshipTypeId, ?User $requester = null, ?User $target = null): ?RelationshipType
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

    /**
     * Obtenir les membres de la famille pour la messagerie
     */
    public function getFamilyMembersForMessaging(User $user): Collection
    {
        $relationships = $this->getUserRelationships($user);

        return $relationships->map(function ($relationship) use ($user) {
            $relatedUser = $relationship->user_id === $user->id
                ? $relationship->relatedUser
                : $relationship->user;

            $relationshipType = $relationship->relationshipType;

            return [
                'id' => $relatedUser->id,
                'name' => $relatedUser->name,
                'email' => $relatedUser->email,
                'avatar' => $relatedUser->profile?->avatar,
                'relationship' => $relationshipType->name_fr,
                'relationship_code' => $relationshipType->code,
                'is_online' => $relatedUser->isOnline(),
                'last_seen_at' => $relatedUser->last_seen_at
            ];
        })->sortBy('name');
    }

    /**
     * Créer automatiquement une conversation lors de l'acceptation d'une relation
     */
    public function createConversationForNewRelation(User $user1, User $user2): ?Conversation
    {
        // Vérifier si une conversation existe déjà
        $existingConversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();

        if ($existingConversation) {
            return $existingConversation;
        }

        // Créer une nouvelle conversation
        try {
            DB::beginTransaction();

            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $user1->id,
                'last_message_at' => now()
            ]);

            // Ajouter les participants
            $conversation->addParticipant($user1, true);
            $conversation->addParticipant($user2);

            DB::commit();
            return $conversation;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création de conversation automatique', [
                'user1_id' => $user1->id,
                'user2_id' => $user2->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtenir les suggestions de conversations basées sur les relations familiales
     */
    public function getConversationSuggestions(User $user): Collection
    {
        $familyMembers = $this->getFamilyMembersForMessaging($user);

        // Récupérer les conversations existantes de l'utilisateur
        $existingConversationUserIds = $user->conversations()
            ->where('type', 'private')
            ->with('participants')
            ->get()
            ->flatMap(function ($conversation) use ($user) {
                return $conversation->participants
                    ->where('id', '!=', $user->id)
                    ->pluck('id');
            });

        // Filtrer les membres de famille sans conversation
        return $familyMembers->filter(function ($member) use ($existingConversationUserIds) {
            return !$existingConversationUserIds->contains($member['id']);
        })->take(5);
    }

    /**
     * Créer un groupe familial automatiquement
     */
    public function createFamilyGroupConversation(User $creator, ?string $groupName = null): ?Conversation
    {
        $familyMembers = $this->getFamilyMembersForMessaging($creator);

        if ($familyMembers->count() < 2) {
            return null; // Pas assez de membres pour un groupe
        }

        $groupName = $groupName ?: "Famille {$creator->name}";

        try {
            DB::beginTransaction();

            $conversation = Conversation::create([
                'name' => $groupName,
                'type' => 'group',
                'created_by' => $creator->id,
                'last_message_at' => now()
            ]);

            // Ajouter le créateur
            $conversation->addParticipant($creator, true);

            // Ajouter les membres de la famille
            foreach ($familyMembers as $member) {
                $familyMember = User::find($member['id']);
                if ($familyMember) {
                    $conversation->addParticipant($familyMember);
                }
            }

            DB::commit();
            return $conversation;

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erreur lors de la création du groupe familial', [
                'creator_id' => $creator->id,
                'group_name' => $groupName,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Analyser la relation entre l'utilisateur connecté et un autre utilisateur (Fonction Yamsoo)
     */
    public function analyzeRelationshipBetweenUsers(User $currentUser, User $targetUser): array
    {
        // Vérifier s'il s'agit du même utilisateur
        if ($currentUser->id === $targetUser->id) {
            return [
                'has_relation' => false,
                'relation_type' => 'self',
                'relation_name' => 'C\'est vous !',
                'relation_description' => 'Vous consultez votre propre profil.',
                'relation_path' => [],
                'confidence' => 100,
                'yamsoo_message' => '🤳 C\'est votre profil !',
            ];
        }

        // Chercher une relation directe
        $directRelation = $this->findDirectRelation($currentUser, $targetUser);
        if ($directRelation) {
            return [
                'has_relation' => true,
                'relation_type' => 'direct',
                'relation_name' => $directRelation['name'],
                'relation_description' => $directRelation['description'],
                'relation_path' => [$directRelation['name']],
                'confidence' => 100,
                'yamsoo_message' => "🎯 {$targetUser->name} est votre {$directRelation['name']} !",
                'relationship_data' => $directRelation['relationship'],
            ];
        }

        // Chercher une relation indirecte (à travers d'autres personnes)
        $indirectRelation = $this->findIndirectRelation($currentUser, $targetUser);
        if ($indirectRelation) {
            return [
                'has_relation' => true,
                'relation_type' => 'indirect',
                'relation_name' => $indirectRelation['name'],
                'relation_description' => $indirectRelation['description'],
                'relation_path' => $indirectRelation['path'],
                'confidence' => $indirectRelation['confidence'],
                'yamsoo_message' => "🔗 {$targetUser->name} est {$indirectRelation['name']} (via {$indirectRelation['via']}) !",
                'intermediate_users' => $indirectRelation['intermediate_users'],
            ];
        }

        // Aucune relation trouvée
        return [
            'has_relation' => false,
            'relation_type' => 'none',
            'relation_name' => 'Aucune relation',
            'relation_description' => 'Aucune relation familiale détectée entre vous et cet utilisateur.',
            'relation_path' => [],
            'confidence' => 0,
            'yamsoo_message' => "❌ Aucune relation familiale trouvée avec {$targetUser->name}.",
            'suggestion' => 'Vous pouvez envoyer une demande de relation si vous pensez être de la même famille.',
        ];
    }

    /**
     * Trouver une relation directe entre deux utilisateurs
     */
    private function findDirectRelation(User $currentUser, User $targetUser): ?array
    {
        // Chercher dans les relations où currentUser est user_id
        $relation = FamilyRelationship::where('user_id', $currentUser->id)
            ->where('related_user_id', $targetUser->id)
            ->where('status', 'accepted')
            ->with('relationshipType')
            ->first();

        if ($relation) {
            return [
                'name' => $relation->relationshipType->name_fr,
                'description' => "Relation directe : {$relation->relationshipType->name_fr}",
                'relationship' => $relation,
            ];
        }

        // Chercher dans les relations où currentUser est related_user_id
        $relation = FamilyRelationship::where('related_user_id', $currentUser->id)
            ->where('user_id', $targetUser->id)
            ->where('status', 'accepted')
            ->with('relationshipType')
            ->first();

        if ($relation) {
            // Trouver la relation inverse
            $inverseRelation = $this->getInverseRelationshipType($relation->relationship_type_id, $targetUser, $currentUser);
            $relationName = $inverseRelation ? $inverseRelation->name_fr : $relation->relationshipType->name_fr;

            return [
                'name' => $relationName,
                'description' => "Relation directe : {$relationName}",
                'relationship' => $relation,
            ];
        }

        return null;
    }

    /**
     * Trouver une relation indirecte entre deux utilisateurs (maximum 2 degrés de séparation)
     */
    private function findIndirectRelation(User $currentUser, User $targetUser): ?array
    {
        // Obtenir tous les proches du currentUser
        $currentUserRelations = $this->getUserRelationships($currentUser);

        // Obtenir tous les proches du targetUser
        $targetUserRelations = $this->getUserRelationships($targetUser);

        // Chercher des connexions communes
        foreach ($currentUserRelations as $currentRelation) {
            $intermediateUserId = $currentRelation->user_id === $currentUser->id
                ? $currentRelation->related_user_id
                : $currentRelation->user_id;

            foreach ($targetUserRelations as $targetRelation) {
                $targetIntermediateUserId = $targetRelation->user_id === $targetUser->id
                    ? $targetRelation->related_user_id
                    : $targetRelation->user_id;

                // Si les deux ont une relation avec la même personne
                if ($intermediateUserId === $targetIntermediateUserId) {
                    $intermediateUser = User::find($intermediateUserId);

                    // Déterminer les relations
                    $currentToIntermediate = $this->getRelationName($currentRelation, $currentUser);
                    $targetToIntermediate = $this->getRelationName($targetRelation, $targetUser);

                    // Calculer la relation indirecte
                    $indirectRelationName = $this->calculateIndirectRelation($currentToIntermediate, $targetToIntermediate);

                    return [
                        'name' => $indirectRelationName,
                        'description' => "Relation via {$intermediateUser->name} : vous êtes {$currentToIntermediate} de {$intermediateUser->name}, et {$targetUser->name} est {$targetToIntermediate} de {$intermediateUser->name}",
                        'path' => [$currentToIntermediate, $intermediateUser->name, $targetToIntermediate],
                        'confidence' => 85,
                        'via' => $intermediateUser->name,
                        'intermediate_users' => [$intermediateUser],
                    ];
                }
            }
        }

        return null;
    }

    /**
     * Obtenir le nom de la relation depuis un objet FamilyRelationship
     */
    private function getRelationName(FamilyRelationship $relationship, User $user): string
    {
        if ($relationship->user_id === $user->id) {
            return $relationship->relationshipType->name_fr;
        } else {
            // Relation inverse
            $inverseType = $this->getInverseRelationshipType($relationship->relationship_type_id,
                User::find($relationship->user_id), $user);
            return $inverseType ? $inverseType->name_fr : $relationship->relationshipType->name_fr;
        }
    }

    /**
     * Calculer la relation indirecte basée sur deux relations directes
     */
    private function calculateIndirectRelation(string $relation1, string $relation2): string
    {
        // Logique simplifiée pour calculer les relations indirectes
        $relationMap = [
            'frère-frère' => 'beau-frère ou cousin',
            'frère-sœur' => 'belle-sœur ou cousine',
            'sœur-sœur' => 'belle-sœur ou cousine',
            'père-fils' => 'frère',
            'père-fille' => 'sœur',
            'mère-fils' => 'frère',
            'mère-fille' => 'sœur',
            'fils-père' => 'grand-père',
            'fille-père' => 'grand-père',
            'fils-mère' => 'grand-mère',
            'fille-mère' => 'grand-mère',
        ];

        $key = strtolower($relation1 . '-' . $relation2);
        return $relationMap[$key] ?? "parent éloigné (via {$relation1}/{$relation2})";
    }

}
