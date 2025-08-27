<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Events\MessageSent;
use Illuminate\Support\Facades\DB;

class TestNotificationSystem extends Command
{
    protected $signature = 'test:notification-system';
    protected $description = 'Tester le système de notifications sonores';

    public function handle()
    {
        $this->info('🔊 Test du système de notifications sonores');
        $this->newLine();

        // 1. Vérifier la configuration
        $this->testConfiguration();

        // 2. Créer des utilisateurs de test
        $users = $this->createTestUsers();

        // 3. Créer une conversation de test
        $conversation = $this->createTestConversation($users);

        // 4. Tester les préférences de notification
        $this->testNotificationSettings($conversation, $users);

        // 5. Simuler l'envoi de messages
        $this->simulateMessages($conversation, $users);

        $this->newLine();
        $this->info('✅ Test du système de notifications terminé');
        $this->info('🌐 Ouvrez http://yamsoo.test/test-notifications pour tester l\'interface');
    }

    private function testConfiguration()
    {
        $this->info('📋 1. Test de la configuration');

        // Vérifier le fichier audio
        $audioPath = public_path('notifications/alert-sound.mp3');
        if (file_exists($audioPath)) {
            $this->line('✅ Fichier audio trouvé: /notifications/alert-sound.mp3');
        } else {
            $this->error('❌ Fichier audio manquant: /notifications/alert-sound.mp3');
        }

        // Vérifier la configuration de broadcasting
        $broadcastDriver = config('broadcasting.default');
        $this->line("📡 Driver de broadcasting: {$broadcastDriver}");

        $this->newLine();
    }

    private function createTestUsers()
    {
        $this->info('👥 2. Création des utilisateurs de test');

        $alice = User::firstOrCreate(
            ['email' => 'alice.test@example.com'],
            [
                'name' => 'Alice Test',
                'password' => bcrypt('password')
            ]
        );

        $bob = User::firstOrCreate(
            ['email' => 'bob.test@example.com'],
            [
                'name' => 'Bob Test',
                'password' => bcrypt('password')
            ]
        );

        $this->line("✅ Alice créée (ID: {$alice->id})");
        $this->line("✅ Bob créé (ID: {$bob->id})");

        $this->newLine();
        return compact('alice', 'bob');
    }

    private function createTestConversation($users)
    {
        $this->info('💬 3. Création de la conversation de test');

        $conversation = Conversation::firstOrCreate(
            [
                'type' => 'private',
                'created_by' => $users['alice']->id
            ],
            [
                'name' => 'Test Notifications',
                'description' => 'Conversation pour tester les notifications',
                'is_active' => true
            ]
        );

        // Ajouter les participants
        $conversation->participants()->syncWithoutDetaching([
            $users['alice']->id => [
                'role' => 'member',
                'status' => 'active',
                'notifications_enabled' => true,
                'joined_at' => now()
            ],
            $users['bob']->id => [
                'role' => 'member',
                'status' => 'active',
                'notifications_enabled' => true,
                'joined_at' => now()
            ]
        ]);

        $this->line("✅ Conversation créée (ID: {$conversation->id})");
        $this->newLine();

        return $conversation;
    }

    private function testNotificationSettings($conversation, $users)
    {
        $this->info('🔔 4. Test des préférences de notification');

        // Tester les préférences par défaut
        $aliceSettings = $conversation->participants()
            ->where('user_id', $users['alice']->id)
            ->first();

        if ($aliceSettings && $aliceSettings->pivot->notifications_enabled) {
            $this->line('✅ Notifications activées par défaut pour Alice');
        } else {
            $this->error('❌ Notifications désactivées pour Alice');
        }

        // Désactiver les notifications pour Bob
        $conversation->participants()->updateExistingPivot($users['bob']->id, [
            'notifications_enabled' => false
        ]);

        $bobSettings = $conversation->participants()
            ->where('user_id', $users['bob']->id)
            ->first();

        if ($bobSettings && !$bobSettings->pivot->notifications_enabled) {
            $this->line('✅ Notifications désactivées pour Bob');
        } else {
            $this->error('❌ Erreur lors de la désactivation pour Bob');
        }

        $this->newLine();
    }

    private function simulateMessages($conversation, $users)
    {
        $this->info('📨 5. Simulation d\'envoi de messages');

        // Message d'Alice vers Bob
        $message1 = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $users['alice']->id,
            'content' => 'Salut Bob ! Test de notification sonore.',
            'type' => 'text'
        ]);

        $this->line("✅ Message d'Alice créé (ID: {$message1->id})");

        // Broadcaster l'événement
        try {
            broadcast(new MessageSent($message1, $users['alice']));
            $this->line('✅ Événement MessageSent broadcasté pour Alice');
        } catch (\Exception $e) {
            $this->error("❌ Erreur broadcast Alice: " . $e->getMessage());
        }

        sleep(2); // Attendre 2 secondes

        // Message de Bob vers Alice
        $message2 = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $users['bob']->id,
            'content' => 'Salut Alice ! Réponse de test.',
            'type' => 'text'
        ]);

        $this->line("✅ Message de Bob créé (ID: {$message2->id})");

        // Broadcaster l'événement
        try {
            broadcast(new MessageSent($message2, $users['bob']));
            $this->line('✅ Événement MessageSent broadcasté pour Bob');
        } catch (\Exception $e) {
            $this->error("❌ Erreur broadcast Bob: " . $e->getMessage());
        }

        $this->newLine();
        $this->info('💡 Résultats attendus:');
        $this->line('   • Alice devrait entendre une notification pour le message de Bob');
        $this->line('   • Bob ne devrait PAS entendre de notification (notifications désactivées)');
        $this->line('   • Aucun des deux ne devrait entendre de notification pour ses propres messages');
    }
}
