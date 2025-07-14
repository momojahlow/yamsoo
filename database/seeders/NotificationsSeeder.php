<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        $notifications = [
            // Notifications pour Ahmed Benali
            [
                'user_email' => 'ahmed.benali@example.com',
                'message' => 'Nouvelle demande de relation : Adil Benslimane souhaite être votre frère',
                'type' => 'relationship_request',
                'data' => json_encode(['requester_id' => $users->where('email', 'adil.benslimane@example.com')->first()->id]),
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ],
            [
                'user_email' => 'ahmed.benali@example.com',
                'message' => 'Anniversaire de famille : C\'est l\'anniversaire de votre fille Amina aujourd\'hui !',
                'type' => 'birthday',
                'data' => json_encode(['family_member_id' => $users->where('email', 'amina.tazi@example.com')->first()->id]),
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(3),
            ],

            // Notifications pour Fatima Zahra
            [
                'user_email' => 'fatima.zahra@example.com',
                'title' => 'Réunion familiale',
                'message' => 'Rappel : Réunion familiale ce dimanche à 14h',
                'type' => 'family_event',
                'data' => json_encode(['event_date' => now()->addDays(2)->format('Y-m-d H:i:s')]),
                'read_at' => null,
                'created_at' => now()->subDays(1),
            ],

            // Notifications pour Mohammed Alami
            [
                'user_email' => 'mohammed.alami@example.com',
                'title' => 'Nouveau membre de la famille',
                'message' => 'Leila Mansouri a rejoint votre arbre généalogique',
                'type' => 'family_update',
                'data' => json_encode(['new_member_id' => $users->where('email', 'leila.mansouri@example.com')->first()->id]),
                'read_at' => now()->subHours(2),
                'created_at' => now()->subHours(4),
            ],

            // Notifications pour Amina Tazi
            [
                'user_email' => 'amina.tazi@example.com',
                'title' => 'Félicitations !',
                'message' => 'Vos parents sont fiers de votre réussite à l\'examen !',
                'type' => 'achievement',
                'data' => json_encode(['achievement_type' => 'academic_success']),
                'read_at' => null,
                'created_at' => now()->subHours(1),
            ],

            // Notifications pour Youssef Bennani
            [
                'user_email' => 'youssef.bennani@example.com',
                'title' => 'Suggestion de relation',
                'message' => 'Nous pensons que Sara Benjelloun pourrait être votre cousine',
                'type' => 'suggestion',
                'data' => json_encode(['suggested_user_id' => $users->where('email', 'sara.benjelloun@example.com')->first()->id]),
                'read_at' => null,
                'created_at' => now()->subDays(2),
            ],

            // Notifications pour Karim El Fassi
            [
                'user_email' => 'karim.elfassi@example.com',
                'title' => 'Document familial découvert',
                'message' => 'Un nouveau document a été ajouté à votre arbre généalogique',
                'type' => 'document',
                'data' => json_encode(['document_type' => 'birth_certificate']),
                'read_at' => now()->subHours(6),
                'created_at' => now()->subDays(1),
            ],

            // Notifications pour Nadia Berrada
            [
                'user_email' => 'nadia.berrada@example.com',
                'title' => 'Message de votre sœur',
                'message' => 'Zineb El Khayat vous a envoyé un message privé',
                'type' => 'message',
                'data' => json_encode(['sender_id' => $users->where('email', 'zineb.elkhayat@example.com')->first()->id]),
                'read_at' => null,
                'created_at' => now()->subHours(30),
            ],

            // Notifications pour Hassan Idrissi
            [
                'user_email' => 'hassan.idrissi@example.com',
                'title' => 'Recette familiale partagée',
                'message' => 'Hanae Mernissi a partagé une recette de famille avec vous',
                'type' => 'recipe',
                'data' => json_encode(['recipe_name' => 'Couscous traditionnel']),
                'read_at' => null,
                'created_at' => now()->subHours(12),
            ],

            // Notifications pour Sara Benjelloun
            [
                'user_email' => 'sara.benjelloun@example.com',
                'title' => 'Exposition d\'art',
                'message' => 'Votre famille vous soutient pour votre prochaine exposition !',
                'type' => 'support',
                'data' => json_encode(['support_type' => 'art_exhibition']),
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(3),
            ],

            // Notifications pour Omar Cherkaoui
            [
                'user_email' => 'omar.cherkaoui@example.com',
                'title' => 'Consultation juridique',
                'message' => 'Un membre de votre famille a besoin de conseils juridiques',
                'type' => 'help_request',
                'data' => json_encode(['help_type' => 'legal_advice']),
                'read_at' => null,
                'created_at' => now()->subDays(1),
            ],

            // Notifications pour Zineb El Khayat
            [
                'user_email' => 'zineb.elkhayat@example.com',
                'title' => 'Publication de livre',
                'message' => 'Votre famille est impatiente de lire votre nouveau livre !',
                'type' => 'achievement',
                'data' => json_encode(['achievement_type' => 'book_publication']),
                'read_at' => null,
                'created_at' => now()->subHours(2),
            ],

            // Notifications pour Adil Benslimane
            [
                'user_email' => 'adil.benslimane@example.com',
                'title' => 'Photo de famille',
                'message' => 'Une nouvelle photo a été ajoutée à votre album familial',
                'type' => 'photo',
                'data' => json_encode(['photo_count' => 1]),
                'read_at' => now()->subHours(4),
                'created_at' => now()->subHours(6),
            ],

            // Notifications pour Hanae Mernissi
            [
                'user_email' => 'hanae.mernissi@example.com',
                'title' => 'Séance de thérapie',
                'message' => 'Rappel : Séance de thérapie familiale demain à 10h',
                'type' => 'appointment',
                'data' => json_encode(['appointment_time' => now()->addDay()->setTime(10, 0)->format('Y-m-d H:i:s')]),
                'read_at' => null,
                'created_at' => now()->subHours(8),
            ],

            // Notifications pour Rachid Alaoui
            [
                'user_email' => 'rachid.alaoui@example.com',
                'title' => 'Cérémonie religieuse',
                'message' => 'Préparation de la cérémonie religieuse de famille ce weekend',
                'type' => 'religious_event',
                'data' => json_encode(['event_type' => 'family_ceremony']),
                'read_at' => now()->subHours(1),
                'created_at' => now()->subHours(3),
            ],
        ];

        foreach ($notifications as $notificationData) {
            $user = $users->where('email', $notificationData['user_email'])->first();
            if ($user) {
                Notification::create([
                    'user_id' => $user->id,
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'data' => $notificationData['data'] ?? null,
                    'read_at' => $notificationData['read_at'] ?? null,
                    'created_at' => $notificationData['created_at'] ?? now(),
                    'updated_at' => $notificationData['created_at'] ?? now(),
                ]);
            }
        }
    }
}
