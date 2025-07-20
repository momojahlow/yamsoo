<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;


class TestMessagingImprovements extends Command
{
    protected $signature = 'test:messaging-improvements';
    protected $description = 'Tester les améliorations du système de messagerie';

    public function handle()
    {
        $this->info('🧪 TEST DES AMÉLIORATIONS DU SYSTÈME DE MESSAGERIE');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia
        $nadia = User::where('name', 'like', '%Nadia%')->first();
        
        if (!$nadia) {
            $this->error('❌ Nadia non trouvée');
            return 1;
        }

        $this->info("👤 UTILISATRICE DE TEST : {$nadia->name} (ID: {$nadia->id})");
        $this->newLine();

        // Test 1: Statistiques de messagerie
        $this->info('1️⃣ TEST DES STATISTIQUES DE MESSAGERIE :');
        $this->testMessagingStats($nadia);
        $this->newLine();

        // Test 2: Messages non lus
        $this->info('2️⃣ TEST DES MESSAGES NON LUS :');
        $this->testUnreadMessages($nadia);
        $this->newLine();

        // Test 3: Conversations actives
        $this->info('3️⃣ TEST DES CONVERSATIONS ACTIVES :');
        $this->testActiveConversations($nadia);
        $this->newLine();

        // Test 4: Fonctionnalités avancées
        $this->info('4️⃣ TEST DES FONCTIONNALITÉS AVANCÉES :');
        $this->testAdvancedFeatures($nadia);
        $this->newLine();

        $this->info('🎯 TESTS TERMINÉS !');
        $this->newLine();

        // Recommandations
        $this->info('💡 RECOMMANDATIONS POUR TESTER L\'INTERFACE :');
        $this->line('   1. Visitez http://yamsoo.test/messages');
        $this->line('   2. Vérifiez le badge de messages non lus dans la sidebar');
        $this->line('   3. Testez l\'envoi de nouveaux messages');
        $this->line('   4. Vérifiez les notifications en temps réel');
        $this->line('   5. Testez la recherche avancée (si implémentée)');

        return 0;
    }

    private function testMessagingStats(User $user): void
    {
        try {
            // Obtenir les statistiques directement
            $userConversations = $user->conversations()->pluck('conversations.id');
            $totalMessages = Message::whereIn('conversation_id', $userConversations)->count();
            $totalConversations = $userConversations->count();

            // Messages non lus
            $unreadMessages = Message::whereIn('conversation_id', $userConversations)
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->count();

            // Conversations avec messages non lus
            $unreadConversations = Conversation::whereIn('id', $userConversations)
                ->whereHas('messages', function ($query) use ($user) {
                    $query->where('user_id', '!=', $user->id)
                          ->whereNull('read_at');
                })
                ->count();

            $this->line("   📊 Total messages : {$totalMessages}");
            $this->line("   💬 Total conversations : {$totalConversations}");
            $this->line("   📩 Messages non lus : {$unreadMessages}");
            $this->line("   🔔 Conversations non lues : {$unreadConversations}");

            if ($unreadMessages > 0) {
                $this->line("   ✅ Badge de messages non lus devrait s'afficher dans la sidebar");
            } else {
                $this->line("   ℹ️  Aucun message non lu - badge non visible");
            }

        } catch (\Exception $e) {
            $this->line("   ❌ Erreur lors du test des statistiques : {$e->getMessage()}");
        }
    }

    private function testUnreadMessages(User $user): void
    {
        // Trouver les messages non lus
        $userConversations = $user->conversations()->pluck('conversations.id');
        
        $unreadMessages = Message::whereIn('conversation_id', $userConversations)
            ->where('user_id', '!=', $user->id)
            ->whereNull('read_at')
            ->with(['user', 'conversation'])
            ->latest()
            ->take(5)
            ->get();

        if ($unreadMessages->isEmpty()) {
            $this->line("   ℹ️  Aucun message non lu trouvé");
            
            // Créer un message non lu pour le test
            $conversation = $user->conversations()->first();
            if ($conversation) {
                $otherUser = $conversation->participants()
                    ->where('user_id', '!=', $user->id)
                    ->first();
                
                if ($otherUser) {
                    $testMessage = Message::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $otherUser->id,
                        'content' => '🧪 Message de test pour les notifications (créé automatiquement)',
                        'type' => 'text'
                    ]);
                    
                    $this->line("   ✨ Message de test créé pour simuler un message non lu");
                    $this->line("      De : {$otherUser->name}");
                    $this->line("      Contenu : {$testMessage->content}");
                }
            }
        } else {
            $this->line("   📩 Messages non lus trouvés :");
            foreach ($unreadMessages as $message) {
                $sender = $message->user;
                $conversation = $message->conversation;
                $content = strlen($message->content) > 50 
                    ? substr($message->content, 0, 50) . '...' 
                    : $message->content;
                
                $this->line("      • {$sender->name} dans {$conversation->name} : {$content}");
            }
        }
    }

    private function testActiveConversations(User $user): void
    {
        $conversations = $user->conversations()
            ->with(['participants', 'messages' => function($query) {
                $query->latest()->take(1);
            }])
            ->get();

        $this->line("   💬 Conversations actives : {$conversations->count()}");
        
        foreach ($conversations->take(3) as $conversation) {
            $participantNames = $conversation->participants()
                ->where('user_id', '!=', $user->id)
                ->pluck('name')
                ->join(', ');
            
            $lastMessage = $conversation->messages->first();
            $lastActivity = $lastMessage 
                ? $lastMessage->created_at->diffForHumans()
                : 'Aucun message';
            
            $this->line("      • {$conversation->name} avec {$participantNames} - {$lastActivity}");
        }
    }

    private function testAdvancedFeatures(User $user): void
    {
        // Test des fichiers partagés
        $userConversations = $user->conversations()->pluck('conversations.id');
        $messagesWithFiles = Message::whereIn('conversation_id', $userConversations)
            ->whereNotNull('file_path')
            ->count();

        $this->line("   📎 Messages avec fichiers : {$messagesWithFiles}");

        // Test des réactions
        $messagesWithReactions = Message::whereIn('conversation_id', $userConversations)
            ->whereHas('reactions')
            ->count();

        $this->line("   😀 Messages avec réactions : {$messagesWithReactions}");

        // Test des messages édités
        $editedMessages = Message::whereIn('conversation_id', $userConversations)
            ->whereNotNull('edited_at')
            ->count();

        $this->line("   ✏️ Messages édités : {$editedMessages}");

        // Test des réponses
        $repliedMessages = Message::whereIn('conversation_id', $userConversations)
            ->whereNotNull('reply_to_id')
            ->count();

        $this->line("   💭 Messages en réponse : {$repliedMessages}");

        // Recommandations
        if ($messagesWithFiles === 0) {
            $this->line("   💡 Testez le partage de fichiers dans l'interface");
        }
        
        if ($messagesWithReactions === 0) {
            $this->line("   💡 Testez les réactions aux messages");
        }
    }
}
