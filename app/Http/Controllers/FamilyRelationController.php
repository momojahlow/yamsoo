<?php

namespace App\Http\Controllers;

use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\RelationshipType;
use App\Models\User;
use App\Services\FamilyRelationService;
use App\Services\EventService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function store(Request $request)
    {
        Log::info('Début de la création de demande de relation', [
            'user_id' => Auth::id(),
            'request_data' => $request->all()
        ]);

        try {
            $user = Auth::user();

            $validated = $request->validate([
                'email' => 'required|email|exists:users,email',
                'relationship_type_id' => 'required|integer|exists:relationship_types,id',
                'message' => 'nullable|string|max:500',
                'mother_name' => 'nullable|string|max:100',
            ]);

            $targetUser = User::where('email', $validated['email'])->first();

            if ($targetUser->id === $user->id) {
                return back()->withErrors(['email' => 'Vous ne pouvez pas faire une demande de relation avec vous-même.']);
            }

            // Vérifier qu'une demande n'est pas déjà en cours entre ces deux utilisateurs
            $existingRequest = RelationshipRequest::where(function ($query) use ($user, $targetUser) {
                $query->where('requester_id', $user->id)
                      ->where('target_user_id', $targetUser->id);
            })->orWhere(function ($query) use ($user, $targetUser) {
                $query->where('requester_id', $targetUser->id)
                      ->where('target_user_id', $user->id);
            })->where('status', 'pending')->first();

            if ($existingRequest) {
                return back()->withErrors(['email' => 'Une demande de relation est déjà en cours avec cet utilisateur.']);
            }

            // Vérifier qu'une relation n'existe pas déjà
            $existingRelation = FamilyRelationship::where(function ($query) use ($user, $targetUser) {
                $query->where('user_id', $user->id)
                      ->where('related_user_id', $targetUser->id);
            })->orWhere(function ($query) use ($user, $targetUser) {
                $query->where('user_id', $targetUser->id)
                      ->where('related_user_id', $user->id);
            })->where('status', 'accepted')->first();

            if ($existingRelation) {
                return back()->withErrors(['email' => 'Vous avez déjà une relation familiale avec cet utilisateur.']);
            }

            // Créer directement sans service pour debug
            Log::info('Création de la demande de relation', [
                'requester_id' => $user->id,
                'target_user_id' => $targetUser->id,
                'relationship_type_id' => $validated['relationship_type_id']
            ]);

            $relationshipRequest = RelationshipRequest::create([
                'requester_id' => $user->id,
                'target_user_id' => $targetUser->id,
                'relationship_type_id' => $validated['relationship_type_id'],
                'message' => $validated['message'] ?? '',
                'mother_name' => $validated['mother_name'] ?? null,
                'status' => 'pending',
            ]);

            // Vérifier immédiatement si la création a réussi
            if (!$relationshipRequest || !$relationshipRequest->exists) {
                Log::error('Échec de la création de la demande de relation');
                return back()->withErrors(['general' => 'Échec de la création de la demande.']);
            }

            // Recharger depuis la base pour vérifier la persistance
            $verification = RelationshipRequest::find($relationshipRequest->id);
            if (!$verification) {
                Log::error('La demande n\'a pas été persistée en base de données');
                return back()->withErrors(['general' => 'Erreur de persistance des données.']);
            }

            Log::info('Demande de relation créée avec succès', [
                'request_id' => $relationshipRequest->id,
                'verification_id' => $verification->id,
                'status' => $verification->status
            ]);

            // Charger les relations pour l'événement
            $relationshipRequest->load(['requester', 'targetUser', 'relationshipType']);

            // Déclencher l'événement seulement si tout est OK
            try {
                $this->eventService->handleRelationshipRequest($relationshipRequest);
            } catch (\Exception $e) {
                Log::warning('Erreur lors de l\'envoi de l\'événement (mais demande créée)', [
                    'error' => $e->getMessage()
                ]);
            }

            return back()->with('success', 'Demande de relation envoyée avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la demande de relation', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => Auth::id()
            ]);

            return back()->withErrors(['general' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
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

    public function cancel(Request $request, int $requestId): \Illuminate\Http\RedirectResponse
    {
        $relationshipRequest = RelationshipRequest::findOrFail($requestId);

        // Vérifier que l'utilisateur connecté est bien l'expéditeur de la demande
        if ($relationshipRequest->requester_id !== $request->user()->id) {
            return back()->withErrors(['error' => 'Vous n\'êtes pas autorisé à annuler cette demande.']);
        }

        // Supprimer la demande
        $relationshipRequest->delete();

        return back()->with('success', 'Demande annulée avec succès.');
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




