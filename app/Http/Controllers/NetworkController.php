<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\User;
use App\Models\RelationshipRequest;
use App\Models\FamilyRelationship;
use App\Services\NetworkService;
use App\Services\SearchService;
use App\Services\FamilyRelationService;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Inertia\Response;

class NetworkController extends Controller
{
    public function __construct(
        private NetworkService $networkService,
        private SearchService $searchService,
        private FamilyRelationService $familyRelationService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $users = $this->searchService->searchUsers('', $user, 20);
        $connections = $this->networkService->getUserNetwork($user);
        $connectedUsers = $this->networkService->getConnectedUsers($user);
        $pendingConnections = $this->networkService->getPendingConnections($user);

        // Récupérer les relations existantes (seulement où l'utilisateur est user_id pour éviter les doublons)
        $existingRelations = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get()
            ->map(function($relation) use ($user) {
                return [
                    'related_user_name' => $relation->relatedUser->name,
                    'related_user_email' => $relation->relatedUser->email,
                    'relationship_name' => $relation->relationshipType->display_name_fr,
                    'created_at' => $relation->created_at->toISOString(),
                ];
            });

        // Récupérer les demandes en attente (reçues par l'utilisateur)
        $pendingRequests = RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['requester.profile', 'relationshipType', 'inverseRelationshipType'])
            ->get()
            ->map(function($request) {
                $locale = app()->getLocale();
                $isRTL = $locale === 'ar';

                // Pour les demandes REÇUES : afficher la relation DEMANDÉE (ce que le requester sera pour le target)


                return [
                    'id' => $request->id,
                    'requester_name' => $request->requester->name,
                    'requester_email' => $request->requester->email,
                    'relationship_name' => $isRTL
                        ? ($request->relationshipType->display_name_ar ?? $request->relationshipType->display_name_fr)
                        : $request->relationshipType->display_name_fr,
                    'message' => $request->message,
                    'mother_name' => $request->mother_name,
                    'created_at' => $request->created_at->toISOString(),
                ];
            });

        // Récupérer les demandes envoyées par l'utilisateur
        $sentRequests = RelationshipRequest::where('requester_id', $user->id)
            ->where('status', 'pending')
            ->with(['targetUser.profile', 'relationshipType', 'inverseRelationshipType'])
            ->get()
            ->map(function($request) {
                $locale = app()->getLocale();
                $isRTL = $locale === 'ar';

                // Pour les demandes ENVOYÉES : afficher la relation demandée (ce que le requester veut être)
                return [
                    'id' => $request->id,
                    'target_user_id' => $request->target_user_id,
                    'target_user_name' => $request->targetUser->name,
                    'target_user_email' => $request->targetUser->email,
                    'relationship_name' => $isRTL
                        ? ($request->relationshipType->display_name_ar ?? $request->relationshipType->display_name_fr)
                        : $request->relationshipType->display_name_fr,
                    'created_at' => $request->created_at->toISOString(),
                ];
            });

        // Filtrer les utilisateurs pour exclure ceux déjà en famille
        $familyMemberIds = $existingRelations->pluck('related_user_email')->toArray();
        $filteredUsers = $users->filter(function($userItem) use ($familyMemberIds, $user) {
            return !in_array($userItem->email, $familyMemberIds) && $userItem->id !== $user->id;
        });

        // Liste des IDs des membres de la famille (pour compatibilité)
        $familyMemberIds = [];
        if ($user->family) {
            $familyMemberIds = $user->family->members->pluck('id')->toArray();
        }

        $relationshipTypes = \App\Models\RelationshipType::ordered()->get()->map(function($type) {
            return [
                'id' => $type->id,
                'name_fr' => $type->display_name_fr,
                'display_name_fr' => $type->display_name_fr,
                'display_name_ar' => $type->display_name_ar,
                'display_name_en' => $type->display_name_en,
                'name' => $type->name,
                'category' => $type->category,
                'generation_level' => $type->generation_level,
                'requires_mother_name' => false, // Supprimé de la nouvelle structure
            ];
        });

        // Récupérer les relations existantes (seulement où l'utilisateur est user_id pour éviter les doublons)
        $existingRelations = FamilyRelationship::where('user_id', $user->id)
            ->where('status', 'accepted')
            ->with(['user.profile', 'relatedUser.profile', 'relationshipType'])
            ->get()
            ->map(function($relation) {
                return [
                    'related_user_name' => $relation->relatedUser->name,
                    'related_user_email' => $relation->relatedUser->email,
                    'relationship_name' => $relation->relationshipType->display_name_fr,
                    'created_at' => $relation->created_at->toISOString(),
                ];
            });

        // Récupérer les demandes en attente
        $pendingRequests = RelationshipRequest::where('target_user_id', $user->id)
            ->where('status', 'pending')
            ->with(['requester.profile', 'relationshipType'])
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'requester_name' => $request->requester->name,
                    'requester_email' => $request->requester->email,
                    'relationship_name' => $request->relationshipType->display_name_fr,
                    'message' => $request->message,
                    'mother_name' => $request->mother_name,
                    'created_at' => $request->created_at->toISOString(),
                ];
            });

        return Inertia::render('Networks', [
            'users' => $filteredUsers->values(),
            'connections' => $connections,
            'connectedUsers' => $connectedUsers,
            'pendingConnections' => $pendingConnections,
            'familyMemberIds' => $familyMemberIds,
            'relationshipTypes' => $relationshipTypes,
            'existingRelations' => $existingRelations,
            'pendingRequests' => $pendingRequests,
            'sentRequests' => $sentRequests,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'connected_user_id' => 'required|exists:users,id',
        ]);

        $user = $request->user();

        // Vérifier que l'utilisateur ne se connecte pas à lui-même
        if ($user->id === $validated['connected_user_id']) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous connecter à vous-même.']);
        }

        // Utiliser le service pour créer la connexion
        $this->networkService->createConnection($user, $validated['connected_user_id']);

        return back()->with('success', 'Demande de connexion envoyée avec succès.');
    }

    public function destroy(Network $network): \Illuminate\Http\RedirectResponse
    {
        $this->networkService->removeConnection($network);
        return back()->with('success', 'Connexion supprimée avec succès.');
    }

    public function search(Request $request): Response
    {
        $user = $request->user();
        $search = $request->get('search', '');

        $users = $this->searchService->searchUsers($search, $user, 20);
        $connections = $this->networkService->getUserNetwork($user);

        return Inertia::render('Networks', [
            'users' => $users,
            'connections' => $connections,
            'search' => $search,
        ]);
    }
}
