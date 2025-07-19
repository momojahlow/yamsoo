<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;

class MessagingSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer quelques utilisateurs existants
        $users = User::limit(5)->get();
        
        if ($users->count() < 2) {
            $this->command->info('Pas assez d\'utilisateurs pour créer des conversations de test.');
            return;
        }

        // Créer des conversations privées
        for ($i = 0; $i < min(3, $users->count() - 1); $i++) {
            $user1 = $users[$i];
            $user2 = $users[$i + 1];

            // Créer une conversation privée
            $conversation = Conversation::create([
                'type' => 'private',
                'created_by' => $user1->id,
                'last_message_at' => now()->subHours(rand(1, 24))
            ]);

            // Ajouter les participants
            $conversation->addParticipant($user1, true);
            $conversation->addParticipant($user2);

            // Créer quelques messages
            $messages = [
                [
                    'user_id' => $user1->id,
                    'content' => 'Salut ! Comment ça va ?',
                    'created_at' => now()->subHours(rand(2, 24))
                ],
                [
                    'user_id' => $user2->id,
                    'content' => 'Ça va bien merci ! Et toi ?',
                    'created_at' => now()->subHours(rand(1, 23))
                ],
                [
                    'user_id' => $user1->id,
                    'content' => 'Très bien aussi ! Tu fais quoi ce weekend ?',
                    'created_at' => now()->subHours(rand(1, 22))
                ],
                [
                    'user_id' => $user2->id,
                    'content' => 'Je pensais organiser un repas de famille. Tu es libre ?',
                    'created_at' => now()->subMinutes(rand(10, 60))
                ]
            ];

            foreach ($messages as $messageData) {
                $message = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $messageData['user_id'],
                    'content' => $messageData['content'],
                    'type' => 'text',
                    'created_at' => $messageData['created_at'],
                    'updated_at' => $messageData['created_at']
                ]);

                // Ajouter quelques réactions aléatoirement
                if (rand(1, 3) === 1) {
                    $reactingUser = $messageData['user_id'] === $user1->id ? $user2 : $user1;
                    $emojis = ['👍', '❤️', '😂', '😮', '😢', '😡'];
                    
                    MessageReaction::create([
                        'message_id' => $message->id,
                        'user_id' => $reactingUser->id,
                        'emoji' => $emojis[array_rand($emojis)]
                    ]);
                }
            }

            // Mettre à jour le timestamp de la conversation
            $conversation->update(['last_message_at' => $messages[count($messages) - 1]['created_at']]);
        }

        // Créer une conversation de groupe si on a assez d'utilisateurs
        if ($users->count() >= 3) {
            $groupConversation = Conversation::create([
                'name' => 'Famille ' . $users->first()->name,
                'type' => 'group',
                'created_by' => $users->first()->id,
                'last_message_at' => now()->subHours(rand(1, 12))
            ]);

            // Ajouter tous les utilisateurs au groupe
            foreach ($users->take(4) as $index => $user) {
                $groupConversation->addParticipant($user, $index === 0);
            }

            // Messages de groupe
            $groupMessages = [
                [
                    'user_id' => $users[0]->id,
                    'content' => 'Salut tout le monde ! J\'ai créé ce groupe pour qu\'on puisse tous rester en contact.',
                    'created_at' => now()->subHours(rand(6, 12))
                ],
                [
                    'user_id' => $users[1]->id,
                    'content' => 'Super idée ! Merci ' . $users[0]->name . ' 👍',
                    'created_at' => now()->subHours(rand(5, 11))
                ],
                [
                    'user_id' => $users[2]->id,
                    'content' => 'Parfait pour organiser les réunions de famille !',
                    'created_at' => now()->subHours(rand(4, 10))
                ],
                [
                    'user_id' => $users[0]->id,
                    'content' => 'Exactement ! On pourra partager les photos et les nouvelles facilement.',
                    'created_at' => now()->subHours(rand(2, 8))
                ],
                [
                    'user_id' => $users[1]->id,
                    'content' => 'Au fait, quelqu\'un a des nouvelles de grand-mère ?',
                    'created_at' => now()->subMinutes(rand(30, 120))
                ]
            ];

            foreach ($groupMessages as $messageData) {
                $message = Message::create([
                    'conversation_id' => $groupConversation->id,
                    'user_id' => $messageData['user_id'],
                    'content' => $messageData['content'],
                    'type' => 'text',
                    'created_at' => $messageData['created_at'],
                    'updated_at' => $messageData['created_at']
                ]);

                // Réactions plus fréquentes dans les groupes
                if (rand(1, 2) === 1) {
                    $reactingUsers = $users->where('id', '!=', $messageData['user_id'])->take(rand(1, 2));
                    $emojis = ['👍', '❤️', '😂', '👏', '🎉'];
                    
                    foreach ($reactingUsers as $reactingUser) {
                        MessageReaction::create([
                            'message_id' => $message->id,
                            'user_id' => $reactingUser->id,
                            'emoji' => $emojis[array_rand($emojis)]
                        ]);
                    }
                }
            }

            $groupConversation->update(['last_message_at' => $groupMessages[count($groupMessages) - 1]['created_at']]);
        }

        $this->command->info('Données de messagerie créées avec succès !');
        $this->command->info('- Conversations privées : ' . min(3, $users->count() - 1));
        $this->command->info('- Conversations de groupe : ' . ($users->count() >= 3 ? 1 : 0));
    }
}
