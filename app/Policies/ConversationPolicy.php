<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Conversation;

class ConversationPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir la conversation
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants()->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->exists();
    }

    /**
     * Déterminer si l'utilisateur peut envoyer des messages dans la conversation
     */
    public function sendMessage(User $user, Conversation $conversation): bool
    {
        return $this->view($user, $conversation);
    }

    /**
     * Déterminer si l'utilisateur peut gérer la conversation (admin/owner)
     */
    public function manage(User $user, Conversation $conversation): bool
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->first();

        return $participant && in_array($participant->pivot->role, ['admin', 'owner']);
    }

    /**
     * Déterminer si l'utilisateur peut supprimer la conversation (owner seulement)
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->first();

        return $participant && $participant->pivot->role === 'owner';
    }

    /**
     * Déterminer si l'utilisateur peut ajouter des membres
     */
    public function addMembers(User $user, Conversation $conversation): bool
    {
        return $this->manage($user, $conversation);
    }

    /**
     * Déterminer si l'utilisateur peut retirer des membres
     */
    public function removeMembers(User $user, Conversation $conversation): bool
    {
        return $this->manage($user, $conversation);
    }

    /**
     * Déterminer si l'utilisateur peut promouvoir/rétrograder des membres
     */
    public function manageRoles(User $user, Conversation $conversation): bool
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->first();

        // Seul le propriétaire peut gérer les rôles
        return $participant && $participant->pivot->role === 'owner';
    }

    /**
     * Déterminer si l'utilisateur peut quitter la conversation
     */
    public function leave(User $user, Conversation $conversation): bool
    {
        $participant = $conversation->participants()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNull('left_at')
            ->first();

        // Le propriétaire ne peut pas quitter, il doit transférer la propriété ou supprimer
        return $participant && $participant->pivot->role !== 'owner';
    }

    /**
     * Déterminer si l'utilisateur peut modifier les paramètres de la conversation
     */
    public function updateSettings(User $user, Conversation $conversation): bool
    {
        return $this->manage($user, $conversation);
    }

    /**
     * Déterminer si l'utilisateur peut transférer la propriété
     */
    public function transferOwnership(User $user, Conversation $conversation): bool
    {
        return $this->delete($user, $conversation); // Même permission que supprimer
    }
}
