<?php

namespace App\Http\Controllers;

use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Services\FamilyRelationService;
use App\Services\EventService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FamilyRelationController extends Controller
{
    public function __construct(
        private FamilyRelationService $familyRelationService,
        private EventService $eventService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $relationships = $this->familyRelationService->getUserRelationships($user);
        $pendingRequests = $this->familyRelationService->getPendingRequests($user);
        $relationshipTypes = $this->familyRelationService->getRelationshipTypes();
        $familyStats = $this->familyRelationService->getFamilyStatistics($user);

        return Inertia::render('FamilyRelations', [
            'relationships' => $relationships,
            'pendingRequests' => $pendingRequests,
            'relationshipTypes' => $relationshipTypes,
            'familyStats' => $familyStats,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'relationship_type_id' => 'required|exists:relationship_types,id',
            'message' => 'nullable|string|max:500',
            'mother_name' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        // Chercher l'utilisateur par email
        $targetUser = \App\Models\User::where('email', $validated['email'])->first();

        if (!$targetUser) {
            return back()->withErrors(['email' => 'Aucun utilisateur trouvé avec cet email.']);
        }

        // Vérifier que l'utilisateur ne fait pas une demande à lui-même
        if ($user->id === $targetUser->id) {
            return back()->withErrors(['email' => 'Vous ne pouvez pas créer une relation avec vous-même.']);
        }

        // Créer la demande de relation
        $relationshipRequest = $this->familyRelationService->createRelationshipRequest(
            $user,
            $targetUser->id,
            $validated['relationship_type_id'],
            $validated['message'] ?? '',
            $validated['mother_name'] ?? null
        );

        // Charger la relation relationshipType pour éviter un null
        $relationshipRequest->load('relationshipType');

        // Déclencher l'événement
        $this->eventService->handleRelationshipRequest($relationshipRequest);

        return back()->with('success', 'Demande de relation envoyée avec succès.');
    }

    public function accept(Request $request, int $requestId): \Illuminate\Http\RedirectResponse
    {
        $relationshipRequest = RelationshipRequest::findOrFail($requestId);

        // Vérifier que l'utilisateur connecté est bien le destinataire de la demande
        if ($relationshipRequest->target_user_id !== $request->user()->id) {
            return back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à accepter cette demande.']);
        }

        // Accepter la relation
        $this->familyRelationService->acceptRelationshipRequest($relationshipRequest);

        // Déclencher l'événement
        $this->eventService->handleRelationshipAccepted($relationshipRequest);

        return back()->with('success', 'Relation acceptée avec succès.');
    }

    public function reject(Request $request, int $requestId): \Illuminate\Http\RedirectResponse
    {
        $relationshipRequest = RelationshipRequest::findOrFail($requestId);

        // Vérifier que l'utilisateur connecté est bien le destinataire de la demande
        if ($relationshipRequest->target_user_id !== $request->user()->id) {
            return back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à rejeter cette demande.']);
        }

        // Rejeter la relation
        $this->familyRelationService->rejectRelationshipRequest($relationshipRequest);

        return back()->with('success', 'Relation rejetée.');
    }

    public function destroy(FamilyRelationship $relationship): \Illuminate\Http\RedirectResponse
    {
        // Vérifier que l'utilisateur connecté est bien impliqué dans cette relation
        if ($relationship->user_id !== request()->user()->id &&
            $relationship->related_user_id !== request()->user()->id) {
            return back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à supprimer cette relation.']);
        }

        $this->familyRelationService->deleteRelationship($relationship);

        return back()->with('success', 'Relation supprimée avec succès.');
    }

    public function search(Request $request): Response
    {
        $user = $request->user();
        $query = $request->get('query', '');

        $potentialRelatives = $this->familyRelationService->searchPotentialRelatives($user, $query);
        $relationshipTypes = $this->familyRelationService->getRelationshipTypes();

        return Inertia::render('FamilyRelations/Search', [
            'potentialRelatives' => $potentialRelatives,
            'relationshipTypes' => $relationshipTypes,
            'searchQuery' => $query,
        ]);
    }

    public function tree(Request $request): Response
    {
        $user = $request->user();
        $familyTree = $this->familyRelationService->getFamilyTree($user);

        return Inertia::render('FamilyRelations/Tree', [
            'familyTree' => $familyTree,
        ]);
    }

    public function statistics(Request $request): Response
    {
        $user = $request->user();
        $stats = $this->familyRelationService->getFamilyStatistics($user);

        return Inertia::render('FamilyRelations/Statistics', [
            'statistics' => $stats,
        ]);
    }

    public function searchUserByEmail(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'error' => 'Aucun utilisateur trouvé avec cet email.'
            ], 404);
        }

        // Vérifier que l'utilisateur ne cherche pas lui-même
        if ($user->id === $request->user()->id) {
            return response()->json([
                'error' => 'Vous ne pouvez pas créer une relation avec vous-même.'
            ], 400);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile' => $user->profile
            ]
        ]);
    }
}
