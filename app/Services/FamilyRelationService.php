<?php

namespace App\Services;

use App\Events\RelationshipAccepted;
use App\Models\User;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Models\Conversation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FamilyRelationService
{
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
        ?string $motherName = null
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

        // Vérifier qu'une relation n'existe pas déjà
        $existingRelation = FamilyRelationship::where('user_id', $requester->id)
            ->where('related_user_id', $targetUserId)
            ->where('relationship_type_id', $relationshipTypeId)
            ->first();

        if ($existingRelation) {
            throw new \InvalidArgumentException('Une relation de ce type existe déjà entre ces utilisateurs.');
        }

        // Vérifier qu'une demande n'existe pas déjà
        $existingRequest = RelationshipRequest::where('requester_id', $requester->id)
            ->where('target_user_id', $targetUserId)
            ->where('relationship_type_id', $relationshipTypeId)
            ->where('status', 'pending')
            ->first();

        if ($existingRequest) {
            throw new \InvalidArgumentException('Une demande de relation de ce type est déjà en attente entre ces utilisateurs.');
        }

        // Calculer la relation inverse
        $inverseRelationType = $this->getInverseRelationshipType($relationshipTypeId, $requester, $targetUser);

        // Créer la demande avec vérification
        $request = RelationshipRequest::create([
            'requester_id' => $requester->id,
            'target_user_id' => $targetUserId,
            'relationship_type_id' => $relationshipTypeId,
            'inverse_relationship_type_id' => $inverseRelationType ? $inverseRelationType->id : null,
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

        // Relations automatiques désactivées - seules les relations directes sont créées
        $requester = User::find($request->requester_id);
        $target = User::find($request->target_user_id);

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

        // Relations automatiques désactivées - seules les relations directes sont créées

        Log::info("Relation familiale créée directement", [
            'requester' => $requester->name,
            'target' => $target->name,
            'relation' => $relationshipType->display_name_fr,
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
            $type = $relationship->relationshipType->display_name_fr;
            $statistics['by_type'][$type] = ($statistics['by_type'][$type] ?? 0) + 1;

            // Classer par génération
            $code = $relationship->relationshipType->name;
            if (in_array($code, ['father', 'mother', 'grandfather_paternal', 'grandmother_paternal', 'grandfather_maternal', 'grandmother_maternal', 'uncle_paternal', 'aunt_paternal', 'uncle_maternal', 'aunt_maternal'])) {
                $statistics['by_generation']['ancestors']++;
            } elseif (in_array($code, ['brother', 'sister', 'husband', 'wife', 'cousin'])) {
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

    /**
     * Obtient le type de relation inverse (publique)
     */
    public function getPublicInverseRelationshipType(int $relationshipTypeId, ?User $requester = null, ?User $target = null): ?RelationshipType
    {
        return $this->getInverseRelationshipType($relationshipTypeId, $requester, $target);
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

    public function getInverseRelationshipType(int $relationshipTypeId, ?User $requester = null, ?User $target = null): ?RelationshipType
    {
        // Récupérer le type de relation actuel
        $currentType = RelationshipType::find($relationshipTypeId);
        if (!$currentType) {
            return null;
        }

        // Logique corrigée pour les relations parent-enfant
        switch ($currentType->name) {
            // Si quelqu'un demande à être "father" ou "mother" pour la cible,
            // alors la cible voit le demandeur comme "son" ou "daughter"
            case 'father':
            case 'mother':
                return $requester ? $this->getChildRelationByGender($requester) : null;

            // Si quelqu'un demande à être "son" ou "daughter" pour la cible,
            // alors la cible voit le demandeur comme "father" ou "mother"
            case 'son':
            case 'daughter':
                return $requester ? $this->getParentRelationByGender($requester) : null;

            // Relations symétriques - la relation inverse dépend du genre du demandeur
            case 'brother':
                return $requester ? $this->getSiblingRelationByGender($requester) : null;
            case 'sister':
                return $requester ? $this->getSiblingRelationByGender($requester) : null;

            // Relations de mariage
            case 'husband':
                return RelationshipType::where('name', 'wife')->first();
            case 'wife':
                return RelationshipType::where('name', 'husband')->first();

            // Relations par alliance
            case 'father_in_law':
                return $requester ? $this->getChildInLawRelationByGender($requester) : null;
            case 'mother_in_law':
                return $requester ? $this->getChildInLawRelationByGender($requester) : null;
            case 'son_in_law':
                return $requester ? $this->getParentInLawRelationByGender($requester) : null;
            case 'daughter_in_law':
                return $requester ? $this->getParentInLawRelationByGender($requester) : null;
            case 'brother_in_law':
                return $requester ? $this->getSiblingInLawRelationByGender($requester) : null;
            case 'sister_in_law':
                return $requester ? $this->getSiblingInLawRelationByGender($requester) : null;

            // Relations oncle/tante - neveu/nièce
            case 'uncle':
                return $requester ? $this->getNephewNieceRelationByGender($requester) : null;
            case 'aunt':
                return $requester ? $this->getNephewNieceRelationByGender($requester) : null;
            case 'nephew':
                return $requester ? $this->getUncleAuntRelationByGender($requester) : null;
            case 'niece':
                return $requester ? $this->getUncleAuntRelationByGender($requester) : null;

            // Relations grand-parent - petit-enfant
            case 'grandfather':
                return $requester ? $this->getGrandchildRelationByGender($requester) : null;
            case 'grandmother':
                return $requester ? $this->getGrandchildRelationByGender($requester) : null;
            case 'grandson':
                return $requester ? $this->getGrandparentRelationByGender($requester) : null;
            case 'granddaughter':
                return $requester ? $this->getGrandparentRelationByGender($requester) : null;

            default:
                return null;
        }
    }

    /**
     * Retourne la relation parent appropriée selon le genre
     */
    private function getParentRelationByGender(User $parent): ?RelationshipType
    {
        $parentGender = $parent->profile?->gender;

        // Si le genre n'est pas défini, essayer de le deviner par le prénom
        if (!$parentGender) {
            $parentGender = $this->guessGenderFromName($parent->name);
        }

        if ($parentGender === 'male') {
            return RelationshipType::where('name', 'father')->first();
        } elseif ($parentGender === 'female') {
            return RelationshipType::where('name', 'mother')->first();
        }

        // Par défaut, retourner mère si le genre n'est toujours pas déterminé
        // (changé de father à mother car plus probable dans ce contexte)
        return RelationshipType::where('name', 'mother')->first();
    }

    /**
     * Retourne la relation enfant appropriée selon le genre
     */
    private function getChildRelationByGender(User $child): ?RelationshipType
    {
        $childGender = $child->profile?->gender;

        // Si le genre n'est pas défini dans le profil, essayer de le deviner à partir du nom
        if (!$childGender) {
            $childGender = $this->guessGenderFromName($child->name);
        }

        if ($childGender === 'male') {
            return RelationshipType::where('name', 'son')->first();
        } elseif ($childGender === 'female') {
            return RelationshipType::where('name', 'daughter')->first();
        }

        // Par défaut, retourner fils si le genre n'est pas défini
        return RelationshipType::where('name', 'son')->first();
    }

    /**
     * Retourne la relation frère/sœur inverse appropriée selon le genre du demandeur
     * Si le demandeur est un homme, la relation inverse est "brother"
     * Si le demandeur est une femme, la relation inverse est "sister"
     * MAIS pour la relation inverse, c'est l'opposé !
     */
    private function getSiblingRelationByGender(User $requester): ?RelationshipType
    {
        $requesterGender = $requester->profile?->gender;

        if (!$requesterGender) {
            $requesterGender = $this->guessGenderFromName($requester->name);
        }

        // Pour la relation inverse : si le demandeur est une femme, le target sera "brother"
        if ($requesterGender === 'female') {
            return RelationshipType::where('name', 'brother')->first();
        } elseif ($requesterGender === 'male') {
            return RelationshipType::where('name', 'sister')->first();
        }

        return RelationshipType::where('name', 'brother')->first();
    }

    /**
     * Retourne la relation beau-frère/belle-sœur appropriée selon le genre
     */
    private function getSiblingInLawRelationByGender(User $sibling): ?RelationshipType
    {
        $siblingGender = $sibling->profile?->gender;

        if (!$siblingGender) {
            $siblingGender = $this->guessGenderFromName($sibling->name);
        }

        if ($siblingGender === 'male') {
            return RelationshipType::where('name', 'brother_in_law')->first();
        } elseif ($siblingGender === 'female') {
            return RelationshipType::where('name', 'sister_in_law')->first();
        }

        return RelationshipType::where('name', 'brother_in_law')->first();
    }

    /**
     * Retourne la relation beau-fils/belle-fille appropriée selon le genre
     */
    private function getChildInLawRelationByGender(User $child): ?RelationshipType
    {
        // Logique simple et directe basée sur le nom
        $firstName = strtolower(explode(' ', trim($child->name))[0]);
        $femaleNames = ['fatima', 'amina', 'leila', 'nadia', 'sara', 'zineb', 'hanae', 'zahra'];

        if (in_array($firstName, $femaleNames)) {
            return RelationshipType::where('name', 'daughter_in_law')->first();
        }
        return RelationshipType::where('name', 'son_in_law')->first();
    }

    /**
     * Retourne la relation beau-père/belle-mère appropriée selon le genre
     */
    private function getParentInLawRelationByGender(User $parent): ?RelationshipType
    {
        $parentGender = $parent->profile?->gender;

        if (!$parentGender) {
            $parentGender = $this->guessGenderFromName($parent->name);
        }

        if ($parentGender === 'male') {
            return RelationshipType::where('name', 'father_in_law')->first();
        } elseif ($parentGender === 'female') {
            return RelationshipType::where('name', 'mother_in_law')->first();
        }

        return RelationshipType::where('name', 'father_in_law')->first();
    }

    /**
     * Retourne la relation neveu/nièce appropriée selon le genre
     */
    private function getNephewNieceRelationByGender(User $person): ?RelationshipType
    {
        $gender = $person->profile?->gender;

        if (!$gender) {
            $gender = $this->guessGenderFromName($person->name);
        }

        if ($gender === 'male') {
            return RelationshipType::where('name', 'nephew')->first();
        } elseif ($gender === 'female') {
            return RelationshipType::where('name', 'niece')->first();
        }

        return RelationshipType::where('name', 'nephew')->first();
    }

    /**
     * Retourne la relation oncle/tante appropriée selon le genre
     */
    private function getUncleAuntRelationByGender(User $person): ?RelationshipType
    {
        $gender = $person->profile?->gender;

        if (!$gender) {
            $gender = $this->guessGenderFromName($person->name);
        }

        if ($gender === 'male') {
            return RelationshipType::where('name', 'uncle')->first();
        } elseif ($gender === 'female') {
            return RelationshipType::where('name', 'aunt')->first();
        }

        return RelationshipType::where('name', 'uncle')->first();
    }

    /**
     * Retourne la relation petit-enfant appropriée selon le genre
     */
    private function getGrandchildRelationByGender(User $person): ?RelationshipType
    {
        // Logique simple et directe basée sur le nom
        $firstName = strtolower(explode(' ', trim($person->name))[0]);
        $femaleNames = ['fatima', 'amina', 'leila', 'nadia', 'sara', 'zineb', 'hanae', 'zahra'];

        if (in_array($firstName, $femaleNames)) {
            return RelationshipType::where('name', 'granddaughter')->first();
        }
        return RelationshipType::where('name', 'grandson')->first();
    }

    /**
     * Retourne la relation grand-parent appropriée selon le genre
     */
    private function getGrandparentRelationByGender(User $person): ?RelationshipType
    {
        $gender = $person->profile?->gender;

        if (!$gender) {
            $gender = $this->guessGenderFromName($person->name);
        }

        if ($gender === 'male') {
            return RelationshipType::where('name', 'grandfather')->first();
        } elseif ($gender === 'female') {
            return RelationshipType::where('name', 'grandmother')->first();
        }

        return RelationshipType::where('name', 'grandfather')->first();
    }

    /**
     * Deviner le genre à partir du prénom
     */
    private function guessGenderFromName(string $name): ?string
    {
        // Extraire le prénom (premier mot)
        $firstName = explode(' ', trim($name))[0];
        $firstName = strtolower($firstName);

        // Prénoms féminins courants
        $femaleNames = [
            'fatima', 'zahra', 'amina', 'khadija', 'aicha', 'maryam', 'sara', 'leila', 'nadia', 'samira',
            'marie', 'sophie', 'julie', 'claire', 'anne', 'isabelle', 'catherine', 'sylvie', 'martine',
            'nour', 'yasmine', 'salma', 'iman', 'rajae', 'zineb', 'houda', 'siham', 'karima'
        ];

        // Prénoms masculins courants
        $maleNames = [
            'mohammed', 'ahmed', 'hassan', 'omar', 'ali', 'youssef', 'karim', 'said', 'abdelkader', 'rachid',
            'pierre', 'jean', 'michel', 'philippe', 'alain', 'nicolas', 'christophe', 'laurent', 'david',
            'abderrahim', 'mustapha', 'khalid', 'nabil', 'fouad', 'tarik', 'amine', 'othmane'
        ];

        if (in_array($firstName, $femaleNames)) {
            return 'female';
        } elseif (in_array($firstName, $maleNames)) {
            return 'male';
        }

        // Si le prénom se termine par 'a', probablement féminin
        if (str_ends_with($firstName, 'a') || str_ends_with($firstName, 'e')) {
            return 'female';
        }

        return null; // Impossible de déterminer
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
                'relationship' => $relationshipType->display_name_fr,
                'relationship_code' => $relationshipType->name,
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
                'name' => $relation->relationshipType->display_name_fr,
                'description' => "Relation directe : {$relation->relationshipType->display_name_fr}",
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
            $relationName = $inverseRelation ? $inverseRelation->display_name_fr : $relation->relationshipType->display_name_fr;

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
            return $relationship->relationshipType->display_name_fr;
        } else {
            // Relation inverse
            $inverseType = $this->getInverseRelationshipType($relationship->relationship_type_id,
                User::find($relationship->user_id), $user);
            return $inverseType ? $inverseType->display_name_fr : $relationship->relationshipType->display_name_fr;
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
