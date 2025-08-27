<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class GroupController extends Controller
{
    use AuthorizesRequests;
    /**
     * Afficher la liste des groupes de l'utilisateur
     */
    public function index()
    {
        $user = Auth::user();

        // Récupérer tous les groupes où l'utilisateur est participant actif
        $groups = Conversation::where('type', 'group')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('conversation_participants.user_id', $user->id)
                      ->where('conversation_participants.status', 'active');
            })
            ->with([
                'participants' => function ($query) {
                    $query->select('users.id', 'users.name', 'users.email')
                          ->where('conversation_participants.status', 'active')
                          ->orderByRaw("CASE WHEN conversation_participants.role = 'owner' THEN 1 WHEN conversation_participants.role = 'admin' THEN 2 ELSE 3 END")
                          ->orderBy('conversation_participants.joined_at');
                },
                'participants.profile:user_id,avatar_url',
                'lastMessage:id,content,created_at,user_id',
                'lastMessage.user:id,name'
            ])
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(function ($group) use ($user) {
                // Déterminer le rôle de l'utilisateur
                $userParticipant = $group->participants->firstWhere('id', $user->id);
                $userRole = $userParticipant?->pivot->role ?? 'member';

                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'description' => $group->description,
                    'avatar' => $group->avatar,
                    'type' => $group->type,
                    'visibility' => $group->visibility,
                    'max_participants' => $group->max_participants,
                    'participants_count' => $group->participants->count(),
                    'last_activity_at' => $group->last_activity_at,
                    'participants' => $group->participants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->name,
                            'email' => $participant->email,
                            'avatar' => $participant->profile?->avatar_url,
                            'pivot' => [
                                'role' => $participant->pivot->role,
                                'status' => $participant->pivot->status,
                                'nickname' => $participant->pivot->nickname,
                                'joined_at' => $participant->pivot->joined_at,
                                'notifications_enabled' => $participant->pivot->notifications_enabled,
                            ]
                        ];
                    }),
                    'can_manage' => in_array($userRole, ['admin', 'owner']),
                    'user_role' => $userRole,
                ];
            });

        return Inertia::render('Groups/Index', [
            'groups' => $groups
        ]);
    }

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

            // Ajouter le créateur comme propriétaire (owner)
            $conversation->participants()->attach($user->id, [
                'joined_at' => now(),
                'is_admin' => true, // Compatibilité
                'role' => 'owner',
                'status' => 'active',
                'notifications_enabled' => true
            ]);

            // Ajouter les autres participants comme membres simples
            $participantIds = collect($request->participants)
                ->filter(fn($id) => $id != $user->id)
                ->map(fn($id) => [
                    'user_id' => $id,
                    'joined_at' => now(),
                    'is_admin' => false, // Compatibilité
                    'role' => 'member',
                    'status' => 'active',
                    'notifications_enabled' => true
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

    /**
     * Mettre à jour un groupe (nom, description)
     */
    public function update(Request $request, Conversation $group)
    {
        // Vérifier que c'est un groupe
        if ($group->type !== 'group') {
            abort(404);
        }

        // Utiliser la policy pour vérifier les permissions
        $this->authorize('updateSettings', $group);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $oldName = $group->name;
        $group->update($request->only(['name', 'description']));

        // Enregistrer l'activité si le nom a changé
        if ($oldName !== $request->name) {
            \App\Models\ConversationActivity::logNameChanged($group->id, Auth::id(), $oldName, $request->name);
        }

        return back()->with('success', 'Groupe mis à jour avec succès');
    }

    /**
     * Supprimer un groupe
     */
    public function destroy(Conversation $group)
    {
        // Vérifier que c'est un groupe
        if ($group->type !== 'group') {
            abort(404);
        }

        // Utiliser la policy pour vérifier les permissions
        $this->authorize('delete', $group);

        $groupName = $group->name;
        $group->delete();

        return redirect('/groups')->with('success', "Le groupe \"{$groupName}\" a été supprimé");
    }

    /**
     * Retirer un participant du groupe
     */
    public function removeParticipant(Conversation $group, User $participant)
    {
        // Vérifier que c'est un groupe
        if ($group->type !== 'group') {
            abort(404);
        }

        // Utiliser la policy pour vérifier les permissions
        $this->authorize('removeMembers', $group);

        // Ne pas permettre de retirer le propriétaire
        $participantData = $group->participants()->where('user_id', $participant->id)->first();
        if ($participantData && $participantData->pivot->role === 'owner') {
            abort(403, 'Impossible de retirer le propriétaire du groupe');
        }

        // Marquer comme parti au lieu de supprimer
        $group->participants()->updateExistingPivot($participant->id, [
            'status' => 'banned',
            'left_at' => now(),
        ]);

        // Enregistrer l'activité
        \App\Models\ConversationActivity::log($group->id, Auth::id(), 'removed', [
            'removed_user_id' => $participant->id,
            'removed_user_name' => $participant->name,
        ]);

        return back()->with('success', "{$participant->name} a été retiré du groupe");
    }

    /**
     * Modifier le rôle d'un participant
     */
    public function updateParticipantRole(Request $request, Conversation $group, User $participant)
    {
        // Vérifier que c'est un groupe
        if ($group->type !== 'group') {
            abort(404);
        }

        // Utiliser la policy pour vérifier les permissions
        $this->authorize('manageRoles', $group);

        $request->validate([
            'role' => 'required|in:member,admin',
        ]);

        $participantData = $group->participants()->where('user_id', $participant->id)->first();
        if (!$participantData) {
            abort(404, 'Participant non trouvé');
        }

        $oldRole = $participantData->pivot->role;
        $newRole = $request->role;

        $group->participants()->updateExistingPivot($participant->id, [
            'role' => $newRole,
        ]);

        // Enregistrer l'activité
        \App\Models\ConversationActivity::logRoleChanged($group->id, $participant->id, $oldRole, $newRole, Auth::id());

        $action = $newRole === 'admin' ? 'promu administrateur' : 'rétrogradé membre';
        return back()->with('success', "{$participant->name} a été {$action}");
    }

    /**
     * Permettre à un utilisateur de quitter un groupe
     */
    public function leaveGroup(Conversation $group)
    {
        $user = Auth::user();

        // Vérifier que c'est un groupe
        if ($group->type !== 'group') {
            abort(404);
        }

        $userParticipant = $group->participants()->where('user_id', $user->id)->first();
        if (!$userParticipant) {
            abort(404, 'Vous ne faites pas partie de ce groupe');
        }

        // Le propriétaire ne peut pas quitter son propre groupe, il doit le supprimer
        if ($userParticipant->pivot->role === 'owner') {
            return back()->with('error', 'En tant que propriétaire, vous ne pouvez pas quitter le groupe. Vous devez le supprimer ou transférer la propriété.');
        }

        // Marquer comme parti
        $group->participants()->updateExistingPivot($user->id, [
            'status' => 'left',
            'left_at' => now(),
        ]);

        // Enregistrer l'activité
        \App\Models\ConversationActivity::logLeft($group->id, $user->id);

        // Message système
        \App\Models\Message::create([
            'conversation_id' => $group->id,
            'user_id' => $user->id,
            'content' => "{$user->name} a quitté le groupe",
            'type' => 'system',
        ]);

        return redirect('/groups')->with('success', "Vous avez quitté le groupe \"{$group->name}\"");
    }

    /**
     * Transférer la propriété d'un groupe (pour permettre au propriétaire de quitter)
     */
    public function transferOwnership(Request $request, Conversation $group)
    {
        $user = Auth::user();

        // Vérifier que c'est un groupe et que l'utilisateur est propriétaire
        if ($group->type !== 'group') {
            abort(404);
        }

        $userParticipant = $group->participants()->where('user_id', $user->id)->first();
        if (!$userParticipant || $userParticipant->pivot->role !== 'owner') {
            abort(403, 'Seul le propriétaire peut transférer la propriété');
        }

        $request->validate([
            'new_owner_id' => 'required|exists:users,id',
        ]);

        $newOwner = $group->participants()->where('user_id', $request->new_owner_id)->first();
        if (!$newOwner) {
            return back()->with('error', 'Le nouvel propriétaire doit être membre du groupe');
        }

        DB::beginTransaction();
        try {
            // Transférer la propriété
            $group->participants()->updateExistingPivot($request->new_owner_id, [
                'role' => 'owner',
                'is_admin' => true,
            ]);

            // Rétrograder l'ancien propriétaire en membre
            $group->participants()->updateExistingPivot($user->id, [
                'role' => 'member',
                'is_admin' => false,
            ]);

            // Enregistrer l'activité
            \App\Models\ConversationActivity::log($group->id, $user->id, 'ownership_transferred', [
                'new_owner_id' => $request->new_owner_id,
                'new_owner_name' => $newOwner->name,
            ]);

            DB::commit();
            return back()->with('success', "La propriété du groupe a été transférée à {$newOwner->name}");
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors du transfert de propriété');
        }
    }
}
