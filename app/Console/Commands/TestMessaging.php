<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class TestMessaging extends Command
{
    protected $signature = 'test:messaging-dates';
    protected $description = 'Tester et corriger les dates de messagerie';

    public function handle()
    {
        $this->info('🔍 Test des dates de messagerie...');
        
        // Vérifier les conversations
        $conversations = Conversation::all();
        $this->info("📊 {$conversations->count()} conversations trouvées");
        
        $problematicConversations = 0;
        foreach ($conversations as $conv) {
            if (!$conv->last_message_at) {
                $this->warn("⚠️  Conversation {$conv->id} sans last_message_at");
                $conv->update(['last_message_at' => now()]);
                $problematicConversations++;
            }
        }
        
        if ($problematicConversations > 0) {
            $this->info("✅ {$problematicConversations} conversations corrigées");
        }
        
        // Vérifier les messages
        $messages = Message::all();
        $this->info("📊 {$messages->count()} messages trouvés");
        
        $problematicMessages = 0;
        foreach ($messages as $msg) {
            if (!$msg->created_at) {
                $this->warn("⚠️  Message {$msg->id} sans created_at");
                $msg->update(['created_at' => now()]);
                $problematicMessages++;
            }
        }
        
        if ($problematicMessages > 0) {
            $this->info("✅ {$problematicMessages} messages corrigés");
        }
        
        // Test de formatage des dates
        $this->info('🧪 Test de formatage des dates...');
        
        $testDates = [
            now()->toISOString(),
            now()->subHours(2)->toISOString(),
            now()->subDays(1)->toISOString(),
            now()->subWeeks(1)->toISOString(),
            null,
            '',
            'invalid-date'
        ];
        
        foreach ($testDates as $date) {
            try {
                if (!$date) {
                    $this->line("Date vide: OK (pas d'erreur)");
                    continue;
                }
                
                $dateObj = new \DateTime($date);
                $formatted = $dateObj->format('H:i');
                $this->line("Date {$date}: {$formatted} ✅");
            } catch (\Exception $e) {
                $this->error("Date {$date}: ERREUR - {$e->getMessage()}");
            }
        }
        
        // Afficher un exemple de conversation
        $conversation = Conversation::with(['messages.user', 'participants'])->first();
        if ($conversation) {
            $this->info('📝 Exemple de conversation:');
            $this->line("ID: {$conversation->id}");
            $this->line("Type: {$conversation->type}");
            $this->line("Last message at: {$conversation->last_message_at}");
            $this->line("Messages: {$conversation->messages->count()}");
            $this->line("Participants: {$conversation->participants->count()}");
            
            if ($conversation->messages->isNotEmpty()) {
                $lastMessage = $conversation->messages->last();
                $this->line("Dernier message: \"{$lastMessage->content}\" par {$lastMessage->user->name}");
                $this->line("Créé le: {$lastMessage->created_at}");
            }
        }
        
        $this->info('✅ Test terminé !');
        
        return 0;
    }
}
