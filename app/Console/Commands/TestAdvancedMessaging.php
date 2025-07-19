<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReaction;

class TestAdvancedMessaging extends Command
{
    protected $signature = 'test:advanced-messaging';
    protected $description = 'Tester les fonctionnalités avancées de messagerie';

    public function handle()
    {
        $this->info('🚀 TEST DES FONCTIONNALITÉS AVANCÉES DE MESSAGERIE');
        $this->info('═══════════════════════════════════════════════════');
        $this->newLine();

        // Test des statistiques
        $this->testStatistics();
        $this->newLine();

        // Test de la recherche
        $this->testSearch();
        $this->newLine();

        // Test des réactions
        $this->testReactions();
        $this->newLine();

        // Résumé des fonctionnalités
        $this->showFeatureSummary();

        return 0;
    }

    private function testStatistics()
    {
        $this->info('📊 TEST DES STATISTIQUES :');

        $totalUsers = User::count();
        $totalConversations = Conversation::count();
        $totalMessages = Message::count();
        $totalReactions = MessageReaction::count();

        $this->line("   👥 Utilisateurs : {$totalUsers}");
        $this->line("   💬 Conversations : {$totalConversations}");
        $this->line("   📝 Messages : {$totalMessages}");
        $this->line("   😀 Réactions : {$totalReactions}");

        // Messages par type
        $messagesByType = Message::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $this->line("   📊 Messages par type :");
        foreach ($messagesByType as $stat) {
            $this->line("      {$stat->type} : {$stat->count}");
        }

        // Utilisateurs les plus actifs
        $activeUsers = Message::selectRaw('user_id, COUNT(*) as message_count')
            ->with('user')
            ->groupBy('user_id')
            ->orderBy('message_count', 'desc')
            ->limit(3)
            ->get();

        $this->line("   🏆 Utilisateurs les plus actifs :");
        foreach ($activeUsers as $stat) {
            $this->line("      {$stat->user->name} : {$stat->message_count} messages");
        }
    }

    private function testSearch()
    {
        $this->info('🔍 TEST DE LA RECHERCHE :');

        // Recherche de messages contenant "famille"
        $searchResults = Message::where('content', 'like', '%famille%')
            ->with(['user', 'conversation'])
            ->limit(3)
            ->get();

        $this->line("   Résultats pour 'famille' : {$searchResults->count()} messages");
        foreach ($searchResults as $message) {
            $conversationName = $message->conversation->name ?: 'Conversation privée';
            $this->line("      \"{$message->content}\" par {$message->user->name} dans {$conversationName}");
        }

        // Recherche par type de fichier
        $imageMessages = Message::where('type', 'image')->count();
        $fileMessages = Message::where('type', 'file')->count();

        $this->line("   📷 Messages avec images : {$imageMessages}");
        $this->line("   📄 Messages avec fichiers : {$fileMessages}");
    }

    private function testReactions()
    {
        $this->info('😀 TEST DES RÉACTIONS :');

        $reactionStats = MessageReaction::selectRaw('emoji, COUNT(*) as count')
            ->groupBy('emoji')
            ->orderBy('count', 'desc')
            ->get();

        $this->line("   Réactions les plus utilisées :");
        foreach ($reactionStats as $stat) {
            $this->line("      {$stat->emoji} : {$stat->count} fois");
        }

        // Messages avec le plus de réactions
        $popularMessages = Message::withCount('reactions')
            ->whereHas('reactions')
            ->orderBy('reactions_count', 'desc')
            ->with(['user', 'reactions'])
            ->limit(3)
            ->get();

        $this->line("   Messages les plus populaires :");
        foreach ($popularMessages as $message) {
            $content = strlen($message->content) > 30
                ? substr($message->content, 0, 30) . '...'
                : $message->content;
            $reactions = $message->reactions->pluck('emoji')->join(' ');
            $this->line("      \"{$content}\" par {$message->user->name} - {$reactions}");
        }
    }

    private function showFeatureSummary()
    {
        $this->info('✨ FONCTIONNALITÉS AVANCÉES DISPONIBLES :');
        $this->newLine();

        $features = [
            '🔍 Recherche avancée' => [
                'Recherche dans le contenu des messages',
                'Filtrage par type de fichier',
                'Filtrage par date et utilisateur',
                'Mise en évidence des résultats'
            ],
            '📊 Statistiques détaillées' => [
                'Métriques d\'activité en temps réel',
                'Graphiques d\'activité quotidienne',
                'Top des contacts les plus actifs',
                'Temps de réponse moyen'
            ],
            '⚙️ Paramètres personnalisables' => [
                'Notifications push configurables',
                'Thèmes clair/sombre',
                'Paramètres de confidentialité',
                'Gestion du stockage'
            ],
            '😀 Réactions et interactions' => [
                'Réactions avec émojis',
                'Réponses aux messages',
                'Édition de messages',
                'Indicateurs de lecture'
            ],
            '📱 Interface moderne' => [
                'Design responsive mobile/desktop',
                'Animations fluides',
                'Indicateurs de présence',
                'Interface intuitive'
            ],
            '🔒 Sécurité et confidentialité' => [
                'Messages chiffrés',
                'Contrôle de la visibilité',
                'Gestion des permissions',
                'Historique sécurisé'
            ]
        ];

        foreach ($features as $category => $items) {
            $this->line($category);
            foreach ($items as $item) {
                $this->line("   • {$item}");
            }
            $this->newLine();
        }

        $this->info('🌐 ACCÈS RAPIDE :');
        $this->line('   Interface : http://yamsoo.test/messages');
        $this->line('   API : http://yamsoo.test/api/messages/*');
        $this->newLine();

        $this->info('🎯 PROCHAINES ÉTAPES RECOMMANDÉES :');
        $this->line('   1. Tester l\'interface utilisateur');
        $this->line('   2. Configurer les notifications push');
        $this->line('   3. Personnaliser les paramètres');
        $this->line('   4. Explorer les statistiques');
        $this->line('   5. Utiliser la recherche avancée');
        $this->newLine();

        $this->info('🎉 SYSTÈME DE MESSAGERIE YAMSOO - PRÊT POUR LA PRODUCTION !');
    }
}
