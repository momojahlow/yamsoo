<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuggestionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $suggestions = Suggestion::where('user_id', $user->id)
            ->with('suggestedUser')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Suggestions', [
            'suggestions' => $suggestions,
        ]);
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

        // Vérifier qu'il n'y a pas déjà une suggestion en cours
        $existingSuggestion = Suggestion::where('user_id', $user->id)
            ->where('suggested_user_id', $validated['suggested_user_id'])
            ->where('status', 'pending')
            ->first();

        if ($existingSuggestion) {
            return back()->withErrors(['error' => 'Une suggestion existe déjà pour cet utilisateur.']);
        }

        Suggestion::create([
            'user_id' => $user->id,
            'suggested_user_id' => $validated['suggested_user_id'],
            'type' => $validated['type'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Suggestion envoyée avec succès.');
    }

    public function update(Request $request, Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $suggestion->update(['status' => $validated['status']]);

        $statusMessage = $validated['status'] === 'accepted' ? 'acceptée' : 'rejetée';
        return back()->with('success', "Suggestion {$statusMessage} avec succès.");
    }

    public function destroy(Suggestion $suggestion): \Illuminate\Http\RedirectResponse
    {
        $suggestion->delete();
        return back()->with('success', 'Suggestion supprimée avec succès.');
    }
}
