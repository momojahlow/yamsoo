<?php

namespace App\Http\Controllers;

use App\Models\Network;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NetworkController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Récupérer tous les utilisateurs sauf l'utilisateur connecté
        $users = User::where('id', '!=', $user->id)
            ->with('profile')
            ->get();

        // Récupérer les connexions existantes
        $connections = Network::where('user_id', $user->id)
            ->orWhere('connected_user_id', $user->id)
            ->with(['user', 'connectedUser'])
            ->get();

        return Inertia::render('Networks', [
            'users' => $users,
            'connections' => $connections,
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

        // Vérifier qu'il n'y a pas déjà une connexion
        $existingConnection = Network::where(function ($query) use ($user, $validated) {
            $query->where('user_id', $user->id)
                  ->where('connected_user_id', $validated['connected_user_id']);
        })->orWhere(function ($query) use ($user, $validated) {
            $query->where('user_id', $validated['connected_user_id'])
                  ->where('connected_user_id', $user->id);
        })->first();

        if ($existingConnection) {
            return back()->withErrors(['error' => 'Une connexion existe déjà avec cet utilisateur.']);
        }

        Network::create([
            'user_id' => $user->id,
            'connected_user_id' => $validated['connected_user_id'],
            'status' => 'connected',
        ]);

        return back()->with('success', 'Connexion établie avec succès.');
    }

    public function destroy(Network $network): \Illuminate\Http\RedirectResponse
    {
        $network->delete();
        return back()->with('success', 'Connexion supprimée avec succès.');
    }

    public function search(Request $request): Response
    {
        $user = $request->user();
        $search = $request->get('search', '');

        $users = User::where('id', '!=', $user->id)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->with('profile')
            ->get();

        return Inertia::render('Networks', [
            'users' => $users,
            'search' => $search,
        ]);
    }
}
