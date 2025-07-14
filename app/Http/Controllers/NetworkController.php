<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\User;
use App\Services\NetworkService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NetworkController extends Controller
{
    public function __construct(
        private NetworkService $networkService,
        private SearchService $searchService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        $users = $this->searchService->searchUsers('', $user, 20);
        $connections = $this->networkService->getUserNetwork($user);
        $connectedUsers = $this->networkService->getConnectedUsers($user);
        $pendingConnections = $this->networkService->getPendingConnections($user);

        // Liste des IDs des membres de la famille
        $familyMemberIds = [];
        if ($user->family) {
            $familyMemberIds = $user->family->members->pluck('id')->toArray();
        }

        $relationshipTypes = \App\Models\RelationshipType::all()->map(function($type) {
            return [
                'id' => $type->id,
                'name_fr' => $type->name_fr,
            ];
        });

        return Inertia::render('Networks', [
            'users' => $users,
            'connections' => $connections,
            'connectedUsers' => $connectedUsers,
            'pendingConnections' => $pendingConnections,
            'familyMemberIds' => $familyMemberIds,
            'relationshipTypes' => $relationshipTypes,
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
