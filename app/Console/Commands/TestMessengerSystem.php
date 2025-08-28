<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\DB;

class TestMessengerSystem extends Command
{
    protected $signature = 'test:messenger-system {--user-id=1 : ID de l\'utilisateur qui recevra les messages}';
    protected $description = 'Tester le système Messenger avec envoi de messages simulés';

    public function handle()
    {
        $userId = $this->option('user-id');
        $this->info("🔊 Test du système Messenger pour l'utilisateur ID: {$userId}");
        $this->newLine();

        // 1. Vérifier l'utilisateur
        $targetUser = User::find($userId);
        if (!$targetUser) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("👤 Utilisateur cible: {$targetUser->name} ({$targetUser->email})");

        // 2. Créer un utilisateur expéditeur de test
        $sender = $this->createTestSender();

        // 3. Créer ou récupérer une conversation
        $conversation = $this->getOrCreateTestConversation($targetUser, $sender);

        // 4. Tester l'API des conversations
        $this->testConversationsAPI($targetUser);

        // 5. Simuler l'envoi de messages
        $this->simulateMessages($conversation, $sender, $targetUser);

        $this->newLine();
        $this->info('✅ Test du système Messenger terminé');
        $this->info('🌐 Ouvrez http://yamsoo.test/test-messenger pour voir l\'interface');
        $this->info('🌐 Ouvrez http://yamsoo.test/dashboard pour voir l\'icône Messenger');
    }

    private function createTestSender()
    {
        $sender = User::firstOrCreate(
            ['email' => 'messenger.test@example.com'],
            [
                'name' => 'Test Messenger Bot',
                'password' => bcrypt('password')
            ]
        );

        $this->line("✅ Expéditeur de test: {$sender->name} (ID: {$sender->id})");
        return $sender;
    }

    private function getOrCreateTestConversation($targetUser, $sender)
    {
        // Chercher une conversation existante entre ces deux utilisateurs
        $conversation = Conversation::whereHas('participants', function ($query) use ($targetUser) {
            $query->where('user_id', $targetUser->id);
        })
        ->whereHas('participants', function ($query) use ($sender) {
            $query->where('user_id', $sender->id);
        })
        ->where('type', 'private')
        ->first();

        if (!$conversation) {
            // Créer une nouvelle conversation
            $conversation = Conversation::create([
                'type' => 'private',
                'name' => 'Test Messenger',
                'created_by' => $sender->id,
                'is_active' => true
            ]);

            // Ajouter les participants
            $conversation->participants()->attach([
                $targetUser->id => [
                    'role' => 'member',
                    'status' => 'active',
                    'notifications_enabled' => true,
                    'joined_at' => now()
                ],
                $sender->id => [
                    'role' => 'member',
                    'status' => 'active',
                    'notifications_enabled' => true,
                    'joined_at' => now()
                ]
            ]);

            $this->line("✅ Nouvelle conversation créée (ID: {$conversation->id})");
        } else {
            $this->line("✅ Conversation existante trouvée (ID: {$conversation->id})");
        }

        return $conversation;
    }

    private function testConversationsAPI($user)
    {
        $this->info('📡 Test de l\'API des conversations');

        try {
            // Simuler l'appel API
            $conversations = DB::table('conversations as c')
                ->join('conversation_participants as cp', 'c.id', '=', 'cp.conversation_id')
                ->where('cp.user_id', $user->id)
                ->where('cp.status', 'active')
                ->select('c.id', 'c.name', 'c.type', 'c.updated_at')
                ->orderBy('c.updated_at', 'desc')
                ->limit(10)
                ->get();

            $this->line("✅ API conversations: {$conversations->count()} conversations trouvées");

            foreach ($conversations as $conv) {
                $unreadCount = DB::table('messages')
                    ->where('conversation_id', $conv->id)
                    ->where('user_id', '!=', $user->id)
                    ->whereNotExists(function ($query) use ($user) {
                        $query->select(DB::raw(1))
                              ->from('message_reads')
                              ->whereColumn('message_reads.message_id', 'messages.id')
                              ->where('message_reads.user_id', $user->id);
                    })
                    ->count();

                $this->line("   • {$conv->name} ({$conv->type}): {$unreadCount} non lus");
            }
        } catch (\Exception $e) {
            $this->error("❌ Erreur API: " . $e->getMessage());
        }

        $this->newLine();
    }

    private function simulateMessages($conversation, $sender, $targetUser)
    {
        $this->info('📨 Simulation d\'envoi de messages');

        $messages = [
            'Salut ! Test du système Messenger 👋',
            'Est-ce que tu reçois bien les notifications ?',
            'Le badge devrait se mettre à jour en temps réel !',
            'Clique sur l\'icône 💬 pour voir le dropdown'
        ];

        foreach ($messages as $index => $content) {
            $this->line("📤 Envoi du message " . ($index + 1) . "...");

            // Créer le message
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $sender->id,
                'content' => $content,
                'type' => 'text'
            ]);

            // Mettre à jour la conversation
            $conversation->touch();

            // Broadcaster l'événement
            try {
                broadcast(new MessageSent($message, $sender));
                $this->line("✅ Événement MessageSent broadcasté");
            } catch (\Exception $e) {
                $this->error("❌ Erreur broadcast: " . $e->getMessage());
            }

            // Envoyer la notification
            try {
                $targetUser->notify(new NewMessageNotification($message, $sender));
                $this->line("✅ Notification envoyée à {$targetUser->name}");
            } catch (\Exception $e) {
                $this->error("❌ Erreur notification: " . $e->getMessage());
            }

            $this->newLine();

            // Attendre 3 secondes entre chaque message
            if ($index < count($messages) - 1) {
                $this->line("⏳ Attente de 3 secondes...");
                sleep(3);
            }
        }

        $this->info('💡 Résultats attendus:');
        $this->line('   • Badge rouge avec le nombre de messages non lus');
        $this->line('   • Dropdown avec la liste des conversations');
        $this->line('   • Son de notification pour chaque nouveau message');
        $this->line('   • Mise à jour en temps réel via Echo');
    }
}
