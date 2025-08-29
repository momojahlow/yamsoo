<?php

namespace App\Console\Commands;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Console\Command;

class TestPusher extends Command
{
    protected $signature = 'test:pusher';
    protected $description = 'Test Pusher broadcasting';

    public function handle()
    {
        $this->info('🚀 Test Pusher Broadcasting...');

        // Récupérer un message existant
        $message = Message::with('user')->first();
        if (!$message) {
            $this->error('❌ Aucun message trouvé');
            return;
        }

        $this->info("📨 Test avec message ID: {$message->id}");
        $this->info("👤 Utilisateur: {$message->user->name}");
        $this->info("💬 Conversation: {$message->conversation_id}");

        // Déclencher l'événement
        $this->info('📡 Déclenchement événement MessageSent...');
        broadcast(new MessageSent($message, $message->user));

        $this->info('✅ Événement diffusé !');
        $this->info('🔍 Vérifiez la console du navigateur pour voir si le message arrive');
    }
}
