<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Broadcast;

class TestChatSystem extends Command
{
    protected $signature = 'test:chat-system';
    protected $description = 'Test complet du système de chat temps réel';

    public function handle()
    {
        $this->info('🧪 TEST COMPLET DU SYSTÈME DE CHAT');
        $this->newLine();
        
        // 1. Test de la configuration
        $this->testConfiguration();
        
        // 2. Test des utilisateurs et conversations
        $this->testUsersAndConversations();
        
        // 3. Test des événements broadcast
        $this->testBroadcastEvents();
        
        // 4. Test de l'ordre des messages
        $this->testMessageOrdering();
        
        // 5. Test des groupes
        $this->testGroupMessages();
        
        $this->newLine();
        $this->info('✅ Tests terminés !');
        
        return 0;
    }
    
    private function testConfiguration()
    {
        $this->info('📋 1. Test de la configuration');
        
        // Vérifier BROADCAST_CONNECTION
        $broadcastDriver = config('broadcasting.default');
        if ($broadcastDriver === 'reverb') {
            $this->line('✅ BROADCAST_CONNECTION = reverb');
        } else {
            $this->error("❌ BROADCAST_CONNECTION = {$broadcastDriver} (devrait être 'reverb')");
        }
        
        // Vérifier les variables Reverb
        $reverbKey = config('broadcasting.connections.reverb.key');
        if ($reverbKey) {
            $this->line('✅ REVERB_APP_KEY configuré');
        } else {
            $this->error('❌ REVERB_APP_KEY manquant');
        }
        
        // Vérifier les variables VITE
        $viteKey = env('VITE_REVERB_APP_KEY');
        if ($viteKey) {
            $this->line('✅ VITE_REVERB_APP_KEY configuré');
        } else {
            $this->error('❌ VITE_REVERB_APP_KEY manquant');
        }
        
        $this->newLine();
    }
    
    private function testUsersAndConversations()
    {
        $this->info('👥 2. Test des utilisateurs et conversations');
        
        // Créer/récupérer les utilisateurs de test
        $alice = User::firstOrCreate(
            ['email' => 'alice@test.com'],
            ['name' => 'Alice Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        
        $bob = User::firstOrCreate(
            ['email' => 'bob@test.com'],
            ['name' => 'Bob Test', 'password' => bcrypt('password'), 'email_verified_at' => now()]
        );
        
        $this->line("✅ Alice créée/trouvée (ID: {$alice->id})");
        $this->line("✅ Bob créé/trouvé (ID: {$bob->id})");
        
        // Créer une conversation privée
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $alice->id))
            ->whereHas('participants', fn($q) => $q->where('user_id', $bob->id))
            ->first();
        
        if (!$conversation) {
            DB::transaction(function () use (&$conversation, $alice, $bob) {
                $conversation = Conversation::create([
                    'type' => 'private',
                    'created_by' => $alice->id,
                    'last_message_at' => now(),
                ]);
                
                $conversation->participants()->attach([$alice->id, $bob->id]);
            });
            
            $this->line("✅ Conversation privée créée (ID: {$conversation->id})");
        } else {
            $this->line("✅ Conversation privée trouvée (ID: {$conversation->id})");
        }
        
        $this->newLine();
    }
    
    private function testBroadcastEvents()
    {
        $this->info('📡 3. Test des événements broadcast');
        
        $alice = User::where('email', 'alice@test.com')->first();
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $alice->id))
            ->first();
        
        // Créer un message de test
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $alice->id,
            'content' => 'Test broadcast ' . now()->format('H:i:s'),
            'type' => 'text',
        ]);
        
        $message->load('user.profile');
        
        try {
            // Tester l'événement
            $event = new MessageSent($message, $alice);
            
            // Vérifier que l'événement implémente ShouldBroadcastNow
            if ($event instanceof \Illuminate\Contracts\Broadcasting\ShouldBroadcastNow) {
                $this->line('✅ Event implémente ShouldBroadcastNow (broadcast immédiat)');
            } else {
                $this->error('❌ Event n\'implémente pas ShouldBroadcastNow');
            }
            
            // Vérifier le canal
            $channels = $event->broadcastOn();
            $channelName = $channels[0]->name ?? 'unknown';
            if ($channelName === "private-conversation.{$conversation->id}") {
                $this->line("✅ Canal correct: {$channelName}");
            } else {
                $this->error("❌ Canal incorrect: {$channelName}");
            }
            
            // Vérifier le nom de l'événement
            $eventName = $event->broadcastAs();
            if ($eventName === 'message.sent') {
                $this->line("✅ Nom d'événement correct: {$eventName}");
            } else {
                $this->error("❌ Nom d'événement incorrect: {$eventName}");
            }
            
            // Déclencher l'événement
            broadcast($event);
            $this->line('✅ Événement broadcast déclenché');
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur broadcast: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function testMessageOrdering()
    {
        $this->info('📝 4. Test de l\'ordre des messages');
        
        $alice = User::where('email', 'alice@test.com')->first();
        $bob = User::where('email', 'bob@test.com')->first();
        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn($q) => $q->where('user_id', $alice->id))
            ->first();
        
        // Créer 3 messages dans l'ordre
        $messages = [];
        for ($i = 1; $i <= 3; $i++) {
            $messages[] = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $i % 2 === 1 ? $alice->id : $bob->id,
                'content' => "Message de test #{$i}",
                'type' => 'text',
            ]);
            
            usleep(100000); // 0.1 seconde entre chaque message
        }
        
        // Vérifier l'ordre
        $orderedMessages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();
        
        $isCorrectOrder = true;
        for ($i = 0; $i < count($messages); $i++) {
            if ($orderedMessages[$i]->id !== $messages[$i]->id) {
                $isCorrectOrder = false;
                break;
            }
        }
        
        if ($isCorrectOrder) {
            $this->line('✅ Ordre des messages correct (ASC par created_at)');
        } else {
            $this->error('❌ Ordre des messages incorrect');
        }
        
        $this->newLine();
    }
    
    private function testGroupMessages()
    {
        $this->info('👥 5. Test des messages de groupe');
        
        $alice = User::where('email', 'alice@test.com')->first();
        $bob = User::where('email', 'bob@test.com')->first();
        
        // Créer un groupe de test
        $group = Conversation::firstOrCreate([
            'type' => 'group',
            'name' => 'Groupe Test',
            'created_by' => $alice->id,
        ], [
            'last_message_at' => now(),
        ]);
        
        // Ajouter les participants
        $group->participants()->syncWithoutDetaching([$alice->id, $bob->id]);
        
        $this->line("✅ Groupe créé/trouvé (ID: {$group->id})");
        
        // Créer un message de groupe
        $groupMessage = Message::create([
            'conversation_id' => $group->id,
            'user_id' => $alice->id,
            'content' => 'Message de groupe test',
            'type' => 'text',
        ]);
        
        $groupMessage->load('user.profile');
        
        // Tester l'événement de groupe
        try {
            broadcast(new MessageSent($groupMessage, $alice));
            $this->line('✅ Message de groupe broadcast');
        } catch (\Exception $e) {
            $this->error("❌ Erreur broadcast groupe: " . $e->getMessage());
        }
        
        $this->newLine();
    }
}
