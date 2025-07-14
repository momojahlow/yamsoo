<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class EmailService
{
    public function sendWelcomeEmail(User $user): void
    {
        Mail::send('emails.welcome', ['user' => $user], function (Message $message) use ($user) {
            $message->to($user->email)
                    ->subject('Bienvenue sur Yamsoo - Connectez votre famille');
        });
    }

    public function sendRelationshipRequestEmail(User $requester, User $target, string $relationshipType): void
    {
        Mail::send('emails.relationship-request', [
            'requester' => $requester,
            'target' => $target,
            'relationshipType' => $relationshipType,
        ], function (Message $message) use ($target) {
            $message->to($target->email)
                    ->subject('Nouvelle demande de relation familiale sur Yamsoo');
        });
    }

    public function sendRelationshipAcceptedEmail(User $accepter, User $requester, string $relationshipType): void
    {
        Mail::send('emails.relationship-accepted', [
            'accepter' => $accepter,
            'requester' => $requester,
            'relationshipType' => $relationshipType,
        ], function (Message $message) use ($requester) {
            $message->to($requester->email)
                    ->subject('Votre demande de relation familiale a été acceptée');
        });
    }

    public function sendNewMessageEmail(User $sender, User $recipient): void
    {
        Mail::send('emails.new-message', [
            'sender' => $sender,
            'recipient' => $recipient,
        ], function (Message $message) use ($recipient) {
            $message->to($recipient->email)
                    ->subject('Nouveau message familial sur Yamsoo');
        });
    }

    public function sendPasswordResetEmail(User $user, string $resetLink): void
    {
        Mail::send('emails.password-reset', [
            'user' => $user,
            'resetLink' => $resetLink,
        ], function (Message $message) use ($user) {
            $message->to($user->email)
                    ->subject('Réinitialisation de votre mot de passe Yamsoo');
        });
    }

    public function sendEmailVerificationEmail(User $user, string $verificationLink): void
    {
        Mail::send('emails.verify-email', [
            'user' => $user,
            'verificationLink' => $verificationLink,
        ], function (Message $message) use ($user) {
            $message->to($user->email)
                    ->subject('Vérifiez votre adresse email Yamsoo');
        });
    }

    public function sendFamilyInvitationEmail(User $inviter, string $inviteeEmail, string $familyName): void
    {
        Mail::send('emails.family-invitation', [
            'inviter' => $inviter,
            'familyName' => $familyName,
        ], function (Message $message) use ($inviteeEmail) {
            $message->to($inviteeEmail)
                    ->subject('Invitation à rejoindre une famille sur Yamsoo');
        });
    }

    public function sendNotificationEmail(User $user, string $notificationType, array $data = []): void
    {
        Mail::send("emails.notifications.{$notificationType}", [
            'user' => $user,
            'data' => $data,
        ], function (Message $message) use ($user, $notificationType) {
            $message->to($user->email)
                    ->subject("Nouvelle notification Yamsoo - {$notificationType}");
        });
    }
}
