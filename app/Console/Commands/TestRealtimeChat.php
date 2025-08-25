<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class TestRealtimeChat extends Command
{
    protected $signature = 'test:realtime-chat';
    protected $description = 'Tester le chat en temps réel entre 2 utilisateurs';

    public function handle()
    {
        $this->info('🚀 Test du chat en temps réel');
        
        // Récupérer ou créer 2 utilisateurs de test
        $user1 = User::firstOrCreate(
            ['email' => 'user1@test.com'],
            [
                'name' => 'Alice Dupont',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );
        
        $user2 = User::firstOrCreate(
            ['email' => 'user2@test.com'],
            [
                'name' => 'Bob Martin',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );
        
        $this->info("👤 User 1: {$user1->name} (ID: {$user1->id})");
        $this->info("👤 User 2: {$user2->name} (ID: {$user2->id})");
        
        // Créer ou trouver une conversation entre eux
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', function ($query) use ($user1) {
                $query->where('user_id', $user1->id);
            })
            ->whereHas('participants', function ($query) use ($user2) {
                $query->where('user_id', $user2->id);
            })
            ->first();
        
        if (!$conversation) {
            DB::transaction(function () use (&$conversation, $user1, $user2) {
                $conversation = Conversation::create([
                    'type' => 'private',
                    'created_by' => $user1->id,
                    'last_message_at' => now(),
                ]);
                
                $conversation->participants()->attach([$user1->id, $user2->id]);
            });
            
            $this->info("💬 Nouvelle conversation créée (ID: {$conversation->id})");
        } else {
            $this->info("💬 Conversation existante trouvée (ID: {$conversation->id})");
        }
        
        // Compter les messages existants
        $messageCount = $conversation->messages()->count();
        $this->info("📊 Messages existants: {$messageCount}");
        
        // Simuler l'envoi de messages
        $this->info("\n🧪 Test d'envoi de messages...");
        
        // Message de Alice
        $message1 = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
            'content' => "Salut Bob ! Comment ça va ? 👋",
            'type' => 'text',
        ]);
        
        $message1->load('user.profile');
        $conversation->update(['last_message_at' => now()]);
        
        $this->info("✅ Message 1 envoyé par {$user1->name}");
        
        // Déclencher l'événement Reverb
        try {
            broadcast(new MessageSent($message1, $user1));
            $this->info("📡 Événement Reverb déclenché pour message 1");
        } catch (\Exception $e) {
            $this->error("❌ Erreur Reverb: " . $e->getMessage());
        }
        
        sleep(1);
        
        // Message de Bob
        $message2 = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user2->id,
            'content' => "Salut Alice ! Ça va très bien, merci ! Et toi ? 😊",
            'type' => 'text',
        ]);
        
        $message2->load('user.profile');
        $conversation->update(['last_message_at' => now()]);
        
        $this->info("✅ Message 2 envoyé par {$user2->name}");
        
        // Déclencher l'événement Reverb
        try {
            broadcast(new MessageSent($message2, $user2));
            $this->info("📡 Événement Reverb déclenché pour message 2");
        } catch (\Exception $e) {
            $this->error("❌ Erreur Reverb: " . $e->getMessage());
        }
        
        sleep(1);
        
        // Message de Alice
        $message3 = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user1->id,
            'content' => "Super ! J'ai testé le nouveau système de chat, il marche parfaitement ! 🎉",
            'type' => 'text',
        ]);
        
        $message3->load('user.profile');
        $conversation->update(['last_message_at' => now()]);
        
        $this->info("✅ Message 3 envoyé par {$user1->name}");
        
        // Déclencher l'événement Reverb
        try {
            broadcast(new MessageSent($message3, $user1));
            $this->info("📡 Événement Reverb déclenché pour message 3");
        } catch (\Exception $e) {
            $this->error("❌ Erreur Reverb: " . $e->getMessage());
        }
        
        // Afficher les statistiques finales
        $totalMessages = $conversation->messages()->count();
        $this->info("\n📊 Statistiques finales:");
        $this->info("- Total messages: {$totalMessages}");
        $this->info("- Messages ajoutés: " . ($totalMessages - $messageCount));
        
        // Afficher les derniers messages
        $this->info("\n📋 Derniers messages:");
        $recentMessages = $conversation->messages()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        foreach ($recentMessages->reverse() as $msg) {
            $time = $msg->created_at->format('H:i:s');
            $this->line("[{$time}] {$msg->user->name}: {$msg->content}");
        }
        
        $this->info("\n🌐 URLs de test:");
        $this->info("👤 Alice se connecte: https://yamsoo.test/messagerie?selectedContactId={$user2->id}");
        $this->info("👤 Bob se connecte: https://yamsoo.test/messagerie?selectedContactId={$user1->id}");
        
        $this->info("\n🎯 Instructions de test:");
        $this->info("1. Ouvrez 2 onglets de navigateur (ou 2 navigateurs différents)");
        $this->info("2. Connectez-vous avec user1@test.com dans le premier");
        $this->info("3. Connectez-vous avec user2@test.com dans le second");
        $this->info("4. Allez sur les URLs ci-dessus dans chaque onglet");
        $this->info("5. Envoyez des messages et vérifiez qu'ils apparaissent instantanément");
        
        $this->info("\n✅ Test terminé ! Le chat temps réel est configuré.");
        
        return 0;
    }
}
