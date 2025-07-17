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
                    'relationship_name' => $relation->relationshipType->name_fr,
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
                    'relationship_name' => $request->relationshipType->name_fr,
                    'message' => $request->message,
                    'mother_name' => $request->mother_name,
                    'created_at' => $request->created_at->toISOString(),
                ];
            });

        // Récupérer les demandes envoyées par l'utilisateur
        $sentRequests = RelationshipRequest::where('requester_id', $user->id)
            ->where('status', 'pending')
            ->with(['targetUser.profile', 'relationshipType'])
            ->get()
            ->map(function($request) {
                return [
                    'id' => $request->id,
                    'target_user_id' => $request->target_user_id,
                    'target_user_email' => $request->targetUser->email,
                    'relationship_name' => $request->relationshipType->name_fr,
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

        $relationshipTypes = \App\Models\RelationshipType::all()->map(function($type) {
            return [
                'id' => $type->id,
                'name_fr' => $type->name_fr,
                'requires_mother_name' => $type->requires_mother_name ?? false,
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
                    'relationship_name' => $relation->relationshipType->name_fr,
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
                    'relationship_name' => $request->relationshipType->name_fr,
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
