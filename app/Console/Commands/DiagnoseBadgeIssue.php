<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Message;

class DiagnoseBadgeIssue extends Command
{
    protected $signature = 'diagnose:badge-issue';
    protected $description = 'Diagnostiquer le problème d\'affichage du badge de messages';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC DU BADGE DE MESSAGES');
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

        $this->info('📊 DONNÉES ACTUELLES :');
        $this->line("   📩 Messages non lus : {$unreadMessages}");
        $this->newLine();

        $this->info('🐛 PROBLÈMES IDENTIFIÉS :');
        $this->line('   1. Badge affiché mais avec ligne rouge sous "Messages"');
        $this->line('   2. Possible conflit CSS entre le badge et le bouton');
        $this->line('   3. Positionnement du badge qui interfère avec le style');
        $this->newLine();

        $this->info('🔧 SOLUTIONS APPLIQUÉES :');
        $this->line('   ✅ Badge isolé dans un div séparé');
        $this->line('   ✅ pointerEvents: none pour éviter les interactions');
        $this->line('   ✅ Z-index élevé (z-50) pour priorité d\'affichage');
        $this->line('   ✅ Positionnement absolu optimisé');
        $this->line('   ✅ Suppression du composant NotificationBadge complexe');
        $this->newLine();

        $this->info('🎨 STRUCTURE CSS ACTUELLE :');
        $this->line('   <div className="relative inline-block">');
        $this->line('     <SidebarMenuButton>Messages</SidebarMenuButton>');
        $this->line('     <div className="absolute -top-2 -right-2 z-50">');
        $this->line('       <span className="badge-styles">{count}</span>');
        $this->line('     </div>');
        $this->line('   </div>');
        $this->newLine();

        $this->info('🧪 TESTS À EFFECTUER :');
        $this->line('   1. Vider le cache du navigateur (Ctrl+F5)');
        $this->line('   2. Vérifier l\'absence de CSS conflictuel');
        $this->line('   3. Inspecter l\'élément dans les DevTools');
        $this->line('   4. Vérifier que le badge ne chevauche pas le texte');
        $this->newLine();

        $this->info('🔍 VÉRIFICATIONS SUPPLÉMENTAIRES :');
        
        // Vérifier s'il y a des styles CSS globaux qui pourraient interférer
        $this->line('   📁 Fichiers CSS à vérifier :');
        $this->line('      • resources/css/app.css');
        $this->line('      • Styles Tailwind compilés');
        $this->line('      • Styles de la sidebar');
        $this->newLine();

        $this->warn('⚠️  SI LE PROBLÈME PERSISTE :');
        $this->line('   1. Ouvrez les DevTools (F12)');
        $this->line('   2. Inspectez l\'élément Messages');
        $this->line('   3. Vérifiez les styles appliqués');
        $this->line('   4. Cherchez des conflits CSS');
        $this->line('   5. Désactivez temporairement le badge pour isoler le problème');
        $this->newLine();

        $this->info('💡 SOLUTION ALTERNATIVE :');
        $this->line('   Si le problème persiste, nous pouvons :');
        $this->line('   • Utiliser un badge en position fixed');
        $this->line('   • Créer un composant badge complètement isolé');
        $this->line('   • Modifier la structure HTML de la sidebar');
        $this->newLine();

        $this->info('🎯 DIAGNOSTIC TERMINÉ !');
        $this->line('   Testez maintenant : http://yamsoo.test/dashboard');

        return 0;
    }
}
