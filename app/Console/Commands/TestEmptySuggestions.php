<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Suggestion;

class TestEmptySuggestions extends Command
{
    protected $signature = 'test:empty-suggestions {action=show}';
    protected $description = 'Tester l\'affichage de la page suggestions quand elle est vide';

    public function handle()
    {
        $action = $this->argument('action');
        
        $this->info('🧪 TEST DE LA PAGE SUGGESTIONS VIDE');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia
        $nadia = User::where('name', 'like', '%Nadia%')->where('name', 'like', '%Berrada%')->first();
        
        if (!$nadia) {
            $this->error('❌ Nadia Berrada non trouvée');
            return 1;
        }

        $this->info("👤 UTILISATRICE DE TEST : {$nadia->name} (ID: {$nadia->id})");
        $this->newLine();

        if ($action === 'clear') {
            // Supprimer temporairement les suggestions
            $this->info('1️⃣ SUPPRESSION TEMPORAIRE DES SUGGESTIONS :');
            $suggestionCount = Suggestion::where('user_id', $nadia->id)->count();
            $this->line("   📊 Suggestions actuelles : {$suggestionCount}");
            
            if ($suggestionCount > 0) {
                Suggestion::where('user_id', $nadia->id)->delete();
                $this->line("   ✅ {$suggestionCount} suggestions supprimées temporairement");
                $this->newLine();
                
                $this->info('🌐 TESTEZ MAINTENANT LA PAGE :');
                $this->line('   URL : http://yamsoo.test/suggestions');
                $this->line('   ✅ La sidebar devrait maintenant s\'afficher même sans suggestions');
                $this->newLine();
                
                $this->warn('⚠️  IMPORTANT : Exécutez "php artisan test:empty-suggestions restore" pour restaurer les suggestions');
            } else {
                $this->line("   ℹ️  Aucune suggestion à supprimer");
            }
            
        } elseif ($action === 'restore') {
            // Restaurer les suggestions
            $this->info('2️⃣ RESTAURATION DES SUGGESTIONS :');
            $suggestionService = app(\App\Services\IntelligentSuggestionService::class);
            $newSuggestions = $suggestionService->generateIntelligentSuggestions($nadia);
            $this->line("   ✨ {$newSuggestions} suggestions restaurées");
            $this->newLine();
            
            $this->info('🌐 SUGGESTIONS RESTAURÉES :');
            $this->line('   URL : http://yamsoo.test/suggestions');
            $this->line('   ✅ Les suggestions sont de nouveau disponibles');
            
        } else {
            // Afficher l'état actuel
            $this->info('📊 ÉTAT ACTUEL DES SUGGESTIONS :');
            $suggestionCount = Suggestion::where('user_id', $nadia->id)->count();
            $pendingCount = Suggestion::where('user_id', $nadia->id)->where('status', 'pending')->count();
            $acceptedCount = Suggestion::where('user_id', $nadia->id)->where('status', 'accepted')->count();
            
            $this->line("   📋 Total suggestions : {$suggestionCount}");
            $this->line("   ⏳ En attente : {$pendingCount}");
            $this->line("   ✅ Acceptées : {$acceptedCount}");
            $this->newLine();
            
            if ($suggestionCount === 0) {
                $this->info('🎯 SITUATION IDÉALE POUR LE TEST :');
                $this->line('   La page suggestions est actuellement vide.');
                $this->line('   URL : http://yamsoo.test/suggestions');
                $this->line('   ✅ Vérifiez que la sidebar s\'affiche correctement');
            } else {
                $this->info('💡 COMMANDES DISPONIBLES :');
                $this->line('   • php artisan test:empty-suggestions clear   - Vider temporairement les suggestions');
                $this->line('   • php artisan test:empty-suggestions restore - Restaurer les suggestions');
                $this->line('   • php artisan test:empty-suggestions show    - Afficher l\'état actuel (défaut)');
            }
        }

        $this->newLine();
        $this->info('🎯 TEST TERMINÉ !');

        return 0;
    }
}
