<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\User;
use App\Models\RelationshipType;
use App\Services\SuggestionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    public function __construct(
        private SuggestionService $suggestionService
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        // Générer de nouvelles suggestions si nécessaire
        try {
            $this->suggestionService->generateSuggestions($user);
        } catch (\Exception $e) {
            // Si le service échoue, créer des suggestions de test
            \Log::warning('SuggestionService failed, creating test suggestions: ' . $e->getMessage());
            $this->createTestSuggestions($user);
        }

        $suggestions = $this->suggestionService->getUserSuggestions($user);
        $pendingSuggestions = $this->suggestionService->getPendingSuggestions($user);
        $acceptedSuggestions = $this->suggestionService->getAcceptedSuggestions($user);

        // Si aucune suggestion, créer des suggestions de test
        if (empty($suggestions)) {
            $this->createTestSuggestions($user);
            $suggestions = $this->suggestionService->getUserSuggestions($user);
        }

        return Inertia::render('Suggestions', [
            'suggestions' => $suggestions,
            'pendingSuggestions' => $pendingSuggestions,
            'acceptedSuggestions' => $acceptedSuggestions,
        ]);
    }

    /**
     * Rafraîchir les suggestions pour l'utilisateur actuel
     */
    public function refresh(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Supprimer les anciennes suggestions
        $this->suggestionService->clearOldSuggestions($user);

        // Générer de nouvelles suggestions
        $this->suggestionService->generateSuggestions($user);

        return back()->with('success', 'Suggestions mises à jour avec succès.');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'suggested_user_id' => 'required|exists:users,id',
            'type' => 'required|string|max:100',
            'message' => 'nullable|string|max:500',
        ]);

        $user = $request->user();

        // Vérifier que l'utilisateur ne se suggère pas lui-même
        if ($user->id === $validated['suggested_user_id']) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas vous suggérer vous-même.']);
        }

        // Utiliser le service pour créer la suggestion
        $this->suggestionService->createSuggestion(
            $user,
            $validated['suggested_user_id'],
            $validated['type'],
            $validated['message'] ?? ''
        );

        return back()->with('success', 'Suggestion envoyée avec succès.');
    }

    public function update(Request $request, Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
            'corrected_relation_code' => 'nullable|string|in:father,mother,son,daughter,brother,sister,husband,wife,grandfather,grandmother,grandson,granddaughter,uncle,aunt,nephew,niece,father_in_law,mother_in_law,brother_in_law,sister_in_law,stepson,stepdaughter',
        ]);

        if ($validated['status'] === 'accepted') {
            $this->suggestionService->acceptSuggestion(
                $suggestion,
                $validated['corrected_relation_code'] ?? null
            );
        } else {
            $this->suggestionService->rejectSuggestion($suggestion);
        }

        $statusMessage = $validated['status'] === 'accepted' ? 'acceptée' : 'rejetée';
        return back()->with('success', "Suggestion {$statusMessage} avec succès.");
    }

    /**
     * Envoyer une demande de relation basée sur une suggestion
     */
    public function sendRelationRequest(Request $request, Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $validRelations = $this->getValidRelationCodes();

        $validated = $request->validate([
            'relation_code' => 'required|string|in:' . implode(',', $validRelations),
        ]);

        // Envoyer une demande de relation au lieu d'accepter directement
        $this->suggestionService->sendRelationRequestFromSuggestion(
            $suggestion,
            $validated['relation_code']
        );

        return back()->with('success', 'Demande de relation envoyée avec succès.');
    }

    /**
     * Accepter une suggestion avec une relation corrigée (méthode legacy)
     */
    public function acceptWithCorrection(Request $request, Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $validRelations = $this->getValidRelationCodes();

        $validated = $request->validate([
            'relation_code' => 'required|string|in:' . implode(',', $validRelations),
        ]);

        $this->suggestionService->acceptSuggestion(
            $suggestion,
            $validated['relation_code']
        );

        return back()->with('success', 'Suggestion acceptée avec la relation corrigée.');
    }

    public function destroy(Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $this->suggestionService->deleteSuggestion($suggestion);
        return back()->with('success', 'Suggestion supprimée avec succès.');
    }

    /**
     * Créer des suggestions de test si aucune suggestion n'existe
     */
    private function createTestSuggestions(User $user): void
    {
        // Vérifier si l'utilisateur a déjà des suggestions
        if (Suggestion::where('user_id', $user->id)->exists()) {
            return;
        }

        // Récupérer d'autres utilisateurs pour créer des suggestions
        $otherUsers = User::where('id', '!=', $user->id)->limit(3)->get();

        if ($otherUsers->isEmpty()) {
            return;
        }

        $relations = [
            'father' => 'Père',
            'mother' => 'Mère',
            'brother' => 'Frère',
            'sister' => 'Sœur',
            'son' => 'Fils',
            'daughter' => 'Fille'
        ];

        $suggestions = [];
        foreach ($otherUsers as $otherUser) {
            $relationCode = array_rand($relations);
            $suggestions[] = [
                'user_id' => $user->id,
                'suggested_user_id' => $otherUser->id,
                'suggested_relation_code' => $relationCode,
                'suggested_relation_name' => $relations[$relationCode],
                'reason' => 'Suggestion de test générée automatiquement',
                'type' => 'automatic',
                'confidence_score' => rand(70, 95),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($suggestions)) {
            Suggestion::insert($suggestions);
        }
    }

    /**
     * Obtenir la liste des codes de relations valides depuis la base de données
     */
    private function getValidRelationCodes(): array
    {
        try {
            return RelationshipType::pluck('name')->toArray();
        } catch (\Exception $e) {
            // Fallback avec les relations de base si la table n'existe pas
            return [
                'father', 'mother', 'son', 'daughter', 'brother', 'sister',
                'husband', 'wife', 'grandfather', 'grandmother', 'grandson', 'granddaughter',
                'uncle', 'aunt', 'nephew', 'niece', 'cousin',
                'father_in_law', 'mother_in_law', 'son_in_law', 'daughter_in_law',
                'brother_in_law', 'sister_in_law', 'stepson', 'stepdaughter'
            ];
        }
    }
}
