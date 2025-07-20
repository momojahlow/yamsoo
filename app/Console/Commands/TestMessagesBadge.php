<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;

class TestMessagesBadge extends Command
{
    protected $signature = 'test:messages-badge';
    protected $description = 'Tester l\'apparence du badge de messages non lus dans la sidebar';

    public function handle()
    {
        $this->info('🔴 TEST DU BADGE DE MESSAGES NON LUS');
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

        // Vérifier les messages non lus
        $userConversations = $nadia->conversations()->pluck('conversations.id');
        $unreadMessages = Message::whereIn('conversation_id', $userConversations)
            ->where('user_id', '!=', $nadia->id)
            ->whereNull('read_at')
            ->count();

        $this->info('📊 ÉTAT ACTUEL DES MESSAGES :');
        $this->line("   📩 Messages non lus : {$unreadMessages}");
        $this->newLine();

        if ($unreadMessages > 0) {
            $this->info('✅ BADGE DEVRAIT ÊTRE VISIBLE :');
            $this->line("   🔴 Badge rouge avec le nombre : {$unreadMessages}");
            $this->line('   📍 Position : En haut à droite de l\'icône Messages');
            $this->line('   🎨 Style : Cercle rouge avec bordure blanche');
        } else {
            $this->warn('⚠️  AUCUN BADGE VISIBLE :');
            $this->line('   Aucun message non lu, le badge ne s\'affiche pas');
            
            // Créer un message non lu pour le test
            $this->info('🧪 CRÉATION D\'UN MESSAGE DE TEST :');
            $conversation = $nadia->conversations()->first();
            if ($conversation) {
                $otherUser = $conversation->participants()
                    ->where('user_id', '!=', $nadia->id)
                    ->first();
                
                if ($otherUser) {
                    Message::create([
                        'conversation_id' => $conversation->id,
                        'user_id' => $otherUser->id,
                        'content' => '🔴 Message de test pour le badge (créé automatiquement)',
                        'type' => 'text'
                    ]);
                    
                    $this->line('   ✨ Message de test créé !');
                    $this->line("      De : {$otherUser->name}");
                    $this->line('      Le badge devrait maintenant s\'afficher');
                }
            }
        }

        $this->newLine();
        $this->info('🎨 AMÉLIORATIONS CSS APPORTÉES :');
        $this->line('   ✅ Badge positionné dans un conteneur relatif');
        $this->line('   ✅ Positionnement absolu optimisé (-top-2 -right-2)');
        $this->line('   ✅ Taille adaptative selon l\'état de la sidebar');
        $this->line('   ✅ Bordure blanche pour meilleur contraste');
        $this->line('   ✅ Ombre portée pour effet de profondeur');
        $this->line('   ✅ Z-index élevé pour éviter les superpositions');
        $this->newLine();

        $this->info('📱 RESPONSIVE DESIGN :');
        $this->line('   🔸 Sidebar étendue : Badge 20px × 20px');
        $this->line('   🔸 Sidebar réduite : Badge 18px × 18px');
        $this->line('   🔸 Texte adaptatif selon la taille');
        $this->newLine();

        $this->info('🧪 INSTRUCTIONS DE TEST :');
        $this->line('   1. Visitez : http://yamsoo.test/dashboard');
        $this->line('   2. Regardez la sidebar à gauche');
        $this->line('   3. Vérifiez l\'icône Messages (💬)');
        $this->line('   4. Le badge rouge devrait être visible en haut à droite');
        $this->line('   5. Testez en réduisant/étendant la sidebar');
        $this->newLine();

        $this->info('🎯 RÉSULTATS ATTENDUS :');
        $this->line('   ✅ Badge rouge bien positionné');
        $this->line('   ✅ Nombre de messages lisible');
        $this->line('   ✅ Pas de chevauchement avec d\'autres éléments');
        $this->line('   ✅ Responsive selon l\'état de la sidebar');
        $this->newLine();

        $this->info('🎯 TEST TERMINÉ !');
        $this->line('   Le badge de messages devrait maintenant avoir un meilleur style.');

        return 0;
    }
}
