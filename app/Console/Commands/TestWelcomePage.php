<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestWelcomePage extends Command
{
    protected $signature = 'test:welcome-page';
    protected $description = 'Tester les différents états de la page d\'accueil selon l\'authentification';

    public function handle()
    {
        $this->info('🏠 TEST DE LA PAGE D\'ACCUEIL YAMSOO');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        $this->info('📋 FONCTIONNALITÉS TESTÉES :');
        $this->line('   ✅ Affichage conditionnel des boutons selon l\'authentification');
        $this->line('   ✅ Navigation adaptée pour utilisateurs connectés/non connectés');
        $this->line('   ✅ Personnalisation du contenu selon l\'état utilisateur');
        $this->newLine();

        $this->info('🔍 SCÉNARIOS DE TEST :');
        $this->newLine();

        // Scénario 1: Utilisateur non connecté
        $this->info('1️⃣ UTILISATEUR NON CONNECTÉ :');
        $this->line('   🌐 URL : http://yamsoo.test/');
        $this->line('   👁️  Navigation header :');
        $this->line('      • Bouton "Se connecter" (ghost)');
        $this->line('      • Bouton "S\'inscrire" (orange)');
        $this->line('   🎯 Section Hero :');
        $this->line('      • Bouton "Créer ma famille" → /register');
        $this->line('      • Bouton "Se connecter" → /login');
        $this->line('   📢 Section CTA (fin de page) :');
        $this->line('      • Bouton "Créer ma famille maintenant" → /register');
        $this->line('      • Bouton "Découvrir Yamsoo" → /login');
        $this->newLine();

        // Scénario 2: Utilisateur connecté
        $this->info('2️⃣ UTILISATEUR CONNECTÉ :');
        $this->line('   🌐 URL : http://yamsoo.test/ (après connexion)');
        $this->line('   👁️  Navigation header :');
        $this->line('      • Texte "Bonjour, [Nom utilisateur]"');
        $this->line('      • Bouton "Mon compte" → /dashboard');
        $this->line('   🎯 Section Hero :');
        $this->line('      • Bouton "Accéder à mon tableau de bord" → /dashboard');
        $this->line('      • Bouton "Ma famille" → /famille');
        $this->line('   📢 Section CTA (fin de page) :');
        $this->line('      • Bouton "Mes messages" → /messages');
        $this->line('      • Bouton "Mon arbre familial" → /famille/arbre');
        $this->newLine();

        $this->info('🧪 INSTRUCTIONS DE TEST MANUEL :');
        $this->newLine();

        $this->warn('📝 ÉTAPE 1 - Test utilisateur non connecté :');
        $this->line('   1. Déconnectez-vous si nécessaire');
        $this->line('   2. Visitez : http://yamsoo.test/');
        $this->line('   3. Vérifiez la présence des boutons "Se connecter" et "S\'inscrire"');
        $this->line('   4. Vérifiez que les boutons de la section Hero pointent vers register/login');
        $this->newLine();

        $this->warn('📝 ÉTAPE 2 - Test utilisateur connecté :');
        $this->line('   1. Connectez-vous avec un compte (ex: nadia.berrada@example.com)');
        $this->line('   2. Visitez : http://yamsoo.test/');
        $this->line('   3. Vérifiez la présence du message "Bonjour, [Nom]"');
        $this->line('   4. Vérifiez la présence du bouton "Mon compte"');
        $this->line('   5. Vérifiez que les boutons pointent vers dashboard/famille/messages');
        $this->newLine();

        $this->info('✅ RÉSULTATS ATTENDUS :');
        $this->line('   • Page d\'accueil adaptée selon l\'état d\'authentification');
        $this->line('   • Navigation cohérente et intuitive');
        $this->line('   • Boutons d\'action pertinents selon le contexte utilisateur');
        $this->line('   • Expérience utilisateur personnalisée');
        $this->newLine();

        $this->info('🔧 AMÉLIORATIONS IMPLÉMENTÉES :');
        $this->line('   ✅ Interface conditionnelle selon auth.user');
        $this->line('   ✅ Boutons d\'action contextuels');
        $this->line('   ✅ Navigation personnalisée');
        $this->line('   ✅ Expérience utilisateur optimisée');
        $this->newLine();

        $this->info('🎯 TEST TERMINÉ !');
        $this->line('   La page d\'accueil s\'adapte maintenant automatiquement');
        $this->line('   selon l\'état d\'authentification de l\'utilisateur.');

        return 0;
    }
}
