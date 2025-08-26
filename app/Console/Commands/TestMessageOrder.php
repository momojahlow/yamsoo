<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Carbon\Carbon;

class TestMessageOrder extends Command
{
    protected $signature = 'test:message-order';
    protected $description = 'Tester l\'ordre des messages';

    public function handle()
    {
        $this->info('🧪 Test de l\'ordre des messages');
        
        // Récupérer ou créer des utilisateurs de test
        $user1 = User::firstOrCreate(['email' => 'user1@test.com'], [
            'name' => 'User 1',
            'password' => bcrypt('password')
        ]);
        
        $user2 = User::firstOrCreate(['email' => 'user2@test.com'], [
            'name' => 'User 2', 
            'password' => bcrypt('password')
        ]);
        
        // Créer une conversation de test
        $conversation = Conversation::create([
            'type' => 'private',
            'name' => 'Test Conversation',
            'created_by' => $user1->id,
            'last_message_at' => now()
        ]);
        
        // Ajouter les participants
        $conversation->participants()->attach([$user1->id, $user2->id]);
        
        // Créer des messages avec des timestamps différents
        $messages = [];
        for ($i = 1; $i <= 5; $i++) {
            $message = Message::create([
                'conversation_id' => $conversation->id,
                'user_id' => $i % 2 == 0 ? $user1->id : $user2->id,
                'content' => "Message $i - " . now()->format('H:i:s'),
                'type' => 'text',
                'created_at' => Carbon::now()->addSeconds($i)
            ]);
            $messages[] = $message;
            
            // Petit délai pour s'assurer que les timestamps sont différents
            sleep(1);
        }
        
        $this->info("✅ Créé {$conversation->id} avec 5 messages");
        
        // Tester l'ordre avec la méthode du contrôleur
        $loadedMessages = $conversation->messages()
            ->with('user.profile')
            ->orderBy('created_at', 'asc')
            ->get();
            
        $this->info('📋 Ordre des messages (ASC - du plus ancien au plus récent):');
        foreach ($loadedMessages as $msg) {
            $this->line("- Message {$msg->id}: {$msg->content} ({$msg->created_at})");
        }
        
        $this->info("🔗 URL de test: http://yamsoo.test/simple-messaging?selectedContactId={$user2->id}");
        
        return 0;
    }
}
