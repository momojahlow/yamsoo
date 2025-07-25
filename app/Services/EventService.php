<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\FamilyRelationship;
use App\Models\RelationshipRequest;
use App\Models\Notification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventService
{
    public function __construct(
        private NotificationService $notificationService,
        private EmailService $emailService
    ) {}

    public function handleNewMessage(Message $message): void
    {
        // Créer une notification pour le destinataire
        $this->notificationService->createNotification(
            $message->receiver,
            'new_message',
            "Nouveau message de {$message->sender->name}",
            [
                'message_id' => $message->id,
                'sender_id' => $message->sender->id,
                'sender_name' => $message->sender->name,
                'content_preview' => substr($message->content, 0, 100),
            ]
        );

        // Envoyer un email si l'utilisateur a activé les notifications par email
        if ($this->shouldSendEmail($message->receiver, 'new_message')) {
            $this->emailService->sendNewMessageEmail($message->sender, $message->receiver);
        }

        // Déclencher un événement pour les notifications en temps réel
        event(new \App\Events\NewMessageReceived($message));
    }

    public function handleRelationshipRequest(RelationshipRequest $request): void
    {
        // Créer une notification pour l'utilisateur cible
        $this->notificationService->createNotification(
            $request->targetUser,
            'relationship_request',
            "{$request->requester->name} souhaite être votre {$request->relationshipType->name}",
            [
                'request_id' => $request->id,
                'requester_id' => $request->requester->id,
                'requester_name' => $request->requester->name,
                'relationship_type' => $request->relationshipType->name,
                'message' => $request->message,
            ]
        );

        // Envoyer un email
        if ($this->shouldSendEmail($request->targetUser, 'relationship_request')) {
            $this->emailService->sendRelationshipRequestEmail(
                $request->requester,
                $request->targetUser,
                $request->relationshipType->name ?? 'Relation'
            );
        }

        // Déclencher un événement
        event(new \App\Events\RelationshipRequestSent($request));
    }

    public function handleRelationshipAccepted(RelationshipRequest $request): void
    {
        // Créer une notification pour le demandeur
        $this->notificationService->createNotification(
            $request->requester,
            'relationship_accepted',
            "{$request->targetUser->name} a accepté votre demande de relation",
            [
                'request_id' => $request->id,
                'target_user_id' => $request->targetUser->id,
                'target_user_name' => $request->targetUser->name,
                'relationship_type' => $request->relationshipType->name ?? 'Relation',
            ]
        );

        // Envoyer un email
        if ($this->shouldSendEmail($request->requester, 'relationship_accepted')) {
            $this->emailService->sendRelationshipAcceptedEmail(
                $request->targetUser,
                $request->requester,
                $request->relationshipType->name_fr ?? 'Relation'
            );
        }

        // Déclencher un événement
        event(new \App\Events\RelationshipAccepted($request->requester, $request->targetUser, $request));
    }

    public function handleFamilyInvitation(User $inviter, User $invitee, Family $family): void
    {
        // Créer une notification pour l'invité
        $this->notificationService->createNotification(
            $invitee,
            'family_invitation',
            "{$inviter->name} vous invite à rejoindre la famille {$family->name}",
            [
                'inviter_id' => $inviter->id,
                'inviter_name' => $inviter->name,
                'family_id' => $family->id,
                'family_name' => $family->name,
            ]
        );

        // Envoyer un email
        if ($this->shouldSendEmail($invitee, 'family_invitation')) {
            $this->emailService->sendFamilyInvitationEmail($inviter, $invitee->email, $family->name);
        }

        // Déclencher un événement
        event(new \App\Events\FamilyInvitationSent($inviter, $invitee, $family));
    }

    public function handleProfileUpdate(User $user): void
    {
        // Notifier les membres de la famille des changements de profil
        if ($user->family) {
            foreach ($user->family->members as $member) {
                if ($member->id !== $user->id) {
                    $this->notificationService->createNotification(
                        $member,
                        'profile_updated',
                        "{$user->name} a mis à jour son profil",
                        [
                            'user_id' => $user->id,
                            'user_name' => $user->name,
                        ]
                    );
                }
            }
        }

        // Déclencher un événement
        event(new \App\Events\ProfileUpdated($user));
    }

    public function handleUserRegistration(User $user): void
    {
        // Envoyer un email de bienvenue
        $this->emailService->sendWelcomeEmail($user);

        // Créer une notification de bienvenue
        $this->notificationService->createNotification(
            $user,
            'welcome',
            'Bienvenue sur Yamsoo ! Commencez par compléter votre profil.',
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
            ]
        );

        // Déclencher un événement
        event(new \App\Events\UserRegistered($user));
    }

    public function handleMessageReaction(Message $message, User $reactor, string $reaction): void
    {
        // Créer une notification pour l'expéditeur du message
        $this->notificationService->createNotification(
            $message->sender,
            'message_reaction',
            "{$reactor->name} a réagi à votre message",
            [
                'message_id' => $message->id,
                'reactor_id' => $reactor->id,
                'reactor_name' => $reactor->name,
                'reaction' => $reaction,
            ]
        );

        // Déclencher un événement
        event(new \App\Events\MessageReactionAdded($message, $reactor, $reaction));
    }

    public function handleFamilyEvent(Family $family, string $eventType, array $data = []): void
    {
        // Notifier tous les membres de la famille
        foreach ($family->members as $member) {
            $this->notificationService->createNotification(
                $member,
                'family_event',
                "Nouvel événement dans votre famille : {$eventType}",
                array_merge($data, [
                    'family_id' => $family->id,
                    'family_name' => $family->name,
                    'event_type' => $eventType,
                ])
            );
        }

        // Déclencher un événement
        event(new \App\Events\FamilyEventCreated($family, $eventType, $data));
    }

    public function handleSystemMaintenance(string $message, array $affectedUsers = []): void
    {
        if (empty($affectedUsers)) {
            // Notifier tous les utilisateurs actifs
            $users = User::whereHas('messages', function ($query) {
                $query->where('created_at', '>=', now()->subDays(30));
            })->get();
        } else {
            $users = User::whereIn('id', $affectedUsers)->get();
        }

        foreach ($users as $user) {
            $this->notificationService->createNotification(
                $user,
                'system_maintenance',
                $message,
                [
                    'message' => $message,
                    'timestamp' => now()->toISOString(),
                ]
            );
        }

        // Déclencher un événement
        event(new \App\Events\SystemMaintenance($message, $affectedUsers));
    }

    private function shouldSendEmail(User $user, string $notificationType): bool
    {
        // Vérifier les préférences de notification de l'utilisateur
        // À implémenter selon la logique métier
        return true;
    }

    public function logEvent(string $eventType, array $data = []): void
    {
        Log::info("Event: {$eventType}", $data);
    }

    public function getEventHistory(User $user, int $limit = 50): array
    {
        // Récupérer l'historique des événements pour un utilisateur
        // À implémenter selon les besoins
        return [];
    }
}
