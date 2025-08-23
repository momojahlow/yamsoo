<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GroupController extends Controller
{
    /**
     * Afficher la page de création de groupe
     */
    public function create()
    {
        $user = Auth::user();

        // Récupérer les contacts de l'utilisateur (membres de la famille)
        $familyMembers = $user->getRelatedUsers();

        $contacts = $familyMembers->map(function ($member) use ($user) {
            // Trouver la relation pour obtenir le type
            $relation = \App\Models\FamilyRelationship::where(function($query) use ($user, $member) {
                $query->where('user_id', $user->id)->where('related_user_id', $member->id);
            })->orWhere(function($query) use ($user, $member) {
                $query->where('user_id', $member->id)->where('related_user_id', $user->id);
            })->with('relationshipType')->first();

            return [
                'id' => $member->id,
                'name' => $member->name,
                'avatar' => $member->profile?->avatar_url,
                'relation' => $relation?->relationshipType?->display_name_fr ?? $relation?->relationshipType?->name ?? 'Famille',
            ];
        });

        return Inertia::render('Groups/Create', [
            'contacts' => $contacts
        ]);
    }

    /**
     * Créer un nouveau groupe
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'participants' => 'required|array|min:1',
            'participants.*' => 'exists:users,id',
        ]);

        $user = $request->user();

        try {
            DB::beginTransaction();

            // Créer la conversation de groupe
            $conversation = Conversation::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => 'group',
                'created_by' => $user->id,
                'last_message_at' => now(),
            ]);

            // Ajouter le créateur comme admin
            $conversation->participants()->attach($user->id, [
                'joined_at' => now(),
                'is_admin' => true
            ]);

            // Ajouter les autres participants
            $participantIds = collect($request->participants)
                ->filter(fn($id) => $id != $user->id)
                ->map(fn($id) => [
                    'user_id' => $id,
                    'joined_at' => now(),
                    'is_admin' => false
                ])
                ->toArray();

            if (!empty($participantIds)) {
                $conversation->participants()->attach($participantIds);
            }

            // Créer un message de bienvenue
            Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $user->id,
                'content' => "🎉 Groupe \"{$request->name}\" créé ! Bienvenue à tous !",
                'type' => 'system',
            ]);

            DB::commit();

            return redirect("/messagerie?selectedContactId=group_{$conversation->id}")
                ->with('success', 'Groupe créé avec succès !');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Erreur lors de la création du groupe')
                ->withInput();
        }
    }

    /**
     * Afficher les détails d'un groupe
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur fait partie du groupe
        if (!$conversation->participants->contains($user)) {
            abort(403, 'Accès non autorisé');
        }

        $participants = $conversation->participants->map(function ($participant) {
            $pivot = $participant->pivot;
            return [
                'id' => $participant->id,
                'name' => $participant->name,
                'avatar' => $participant->profile?->avatar_url,
                'is_admin' => $pivot->is_admin ?? false,
                'joined_at' => $pivot->joined_at,
                'is_online' => $participant->isOnline(),
            ];
        });

        return Inertia::render('Groups/Show', [
            'group' => [
                'id' => $conversation->id,
                'name' => $conversation->name,
                'description' => $conversation->description,
                'created_by' => $conversation->created_by,
                'created_at' => $conversation->created_at,
                'participants_count' => $participants->count(),
            ],
            'participants' => $participants,
            'isAdmin' => $conversation->participants()
                ->where('user_id', $user->id)
                ->first()
                ->pivot
                ->is_admin ?? false
        ]);
    }

    /**
     * Ajouter un participant au groupe
     */
    public function addParticipant(Request $request, Conversation $conversation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        // Vérifier que l'utilisateur est admin du groupe
        $userParticipant = $conversation->participants()
            ->where('user_id', $user->id)
            ->first();

        if (!$userParticipant || !$userParticipant->pivot->is_admin) {
            return redirect()->back()->with('error', 'Seuls les administrateurs peuvent ajouter des participants');
        }

        $newUser = User::findOrFail($request->user_id);

        // Vérifier que l'utilisateur n'est pas déjà dans le groupe
        if ($conversation->participants->contains($newUser)) {
            return redirect()->back()->with('error', 'Cet utilisateur fait déjà partie du groupe');
        }

        // Ajouter le participant
        $conversation->participants()->attach($newUser->id, [
            'joined_at' => now(),
            'is_admin' => false
        ]);

        // Message système
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => "{$newUser->name} a été ajouté au groupe par {$user->name}",
            'type' => 'system',
        ]);

        return redirect()->back()->with('success', 'Participant ajouté avec succès');
    }

    /**
     * Quitter le groupe
     */
    public function leave(Conversation $conversation)
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur fait partie du groupe
        if (!$conversation->participants->contains($user)) {
            return redirect()->back()->with('error', 'Vous ne faites pas partie de ce groupe');
        }

        // Retirer l'utilisateur du groupe
        $conversation->participants()->detach($user->id);

        // Message système
        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => "{$user->name} a quitté le groupe",
            'type' => 'system',
        ]);

        return redirect('/messagerie')->with('success', 'Vous avez quitté le groupe');
    }
}
