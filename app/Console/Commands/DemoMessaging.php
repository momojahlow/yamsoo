<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class DemoMessaging extends Command
{
    protected $signature = 'demo:messaging';
    protected $description = 'Démonstration du système de messagerie';

    public function handle()
    {
        $this->info('🚀 DÉMONSTRATION DU SYSTÈME DE MESSAGERIE YAMSOO');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        // Statistiques générales
        $this->info('📊 STATISTIQUES ACTUELLES :');
        $this->line('   👥 Utilisateurs : ' . User::count());
        $this->line('   💬 Conversations : ' . Conversation::count());
        $this->line('   📝 Messages : ' . Message::count());
        $this->newLine();

        // Utilisateurs en ligne
        $onlineUsers = User::where('last_seen_at', '>', now()->subMinutes(5))->get();
        $this->info('🟢 UTILISATEURS EN LIGNE :');
        if ($onlineUsers->isEmpty()) {
            $this->line('   Aucun utilisateur en ligne actuellement');
        } else {
            foreach ($onlineUsers as $user) {
                $this->line("   👤 {$user->name} ({$user->email})");
            }
        }
        $this->newLine();

        // Conversations actives
        $activeConversations = Conversation::with(['participants', 'lastMessage.user'])
            ->where('last_message_at', '>', now()->subDays(7))
            ->orderBy('last_message_at', 'desc')
            ->get();

        $this->info('💬 CONVERSATIONS ACTIVES (7 derniers jours) :');
        foreach ($activeConversations as $conversation) {
            $participantNames = $conversation->participants->pluck('name')->join(', ');
            $lastMessage = $conversation->lastMessage;

            $conversationName = $conversation->name ?: 'Conversation privée';
            $this->line("   🗨️ {$conversationName}");
            $this->line("      Participants : {$participantNames}");

            if ($lastMessage) {
                $timeAgo = $lastMessage->created_at->diffForHumans();
                $content = strlen($lastMessage->content) > 50
                    ? substr($lastMessage->content, 0, 50) . '...'
                    : $lastMessage->content;
                $this->line("      Dernier message : \"{$content}\" par {$lastMessage->user->name} ({$timeAgo})");
            }
            $this->newLine();
        }

        // Fonctionnalités disponibles
        $this->info('✨ FONCTIONNALITÉS DISPONIBLES :');
        $this->line('   📱 Interface moderne responsive');
        $this->line('   💬 Conversations privées et de groupe');
        $this->line('   📎 Partage de fichiers (images, vidéos, documents)');
        $this->line('   😀 Sélecteur d\'émojis');
        $this->line('   💭 Réponses aux messages');
        $this->line('   ✏️ Édition de messages');
        $this->line('   👀 Statut de lecture');
        $this->line('   🟢 Indicateur de présence en ligne');
        $this->line('   🔍 Recherche d\'utilisateurs');
        $this->line('   📱 Support mobile complet');
        $this->newLine();

        // URLs d'accès
        $this->info('🌐 ACCÈS AU SYSTÈME :');
        $this->line('   Interface principale : http://yamsoo.test/messages');
        $this->line('   API conversations : http://yamsoo.test/api/conversations');
        $this->line('   Recherche utilisateurs : http://yamsoo.test/api/users/search');
        $this->newLine();

        // Instructions d'utilisation
        $this->info('📖 COMMENT UTILISER :');
        $this->line('   1. Connectez-vous à votre compte Yamsoo');
        $this->line('   2. Cliquez sur "Messages" dans la sidebar');
        $this->line('   3. Sélectionnez une conversation existante ou créez-en une nouvelle');
        $this->line('   4. Commencez à échanger avec votre famille !');
        $this->newLine();

        // Conseils
        $this->info('💡 CONSEILS :');
        $this->line('   • Utilisez @ pour mentionner quelqu\'un');
        $this->line('   • Glissez-déposez des fichiers pour les partager');
        $this->line('   • Cliquez sur un message pour y répondre');
        $this->line('   • Les émojis ajoutent de la convivialité !');
        $this->newLine();

        $this->info('🎉 Le système de messagerie Yamsoo est prêt à l\'emploi !');
        $this->line('   Profitez de cette nouvelle façon de rester connecté avec votre famille.');

        return 0;
    }
}
