<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class TestConversations extends Command
{
    protected $signature = 'test:conversations';
    protected $description = 'Créer des conversations de test pour simuler des discussions';

    public function handle()
    {
        $this->info('=== TEST COMPLET DU SYSTÈME DE MESSAGERIE ===');
        $this->newLine();

        try {
            // 1. Récupérer 3 utilisateurs pour les tests
            $users = User::take(3)->get();
            
            if ($users->count() < 3) {
                $this->error('❌ Pas assez d\'utilisateurs (besoin de 3 minimum)');
                return 1;
            }
            
            $user1 = $users[0]; // Utilisateur principal
            $user2 = $users[1]; // Deuxième utilisateur
            $user3 = $users[2]; // Troisième utilisateur
            
            $this->info('✅ Utilisateurs de test :');
            $this->line("   - User 1: {$user1->name} (ID: {$user1->id})");
            $this->line("   - User 2: {$user2->name} (ID: {$user2->id})");
            $this->line("   - User 3: {$user3->name} (ID: {$user3->id})");
            $this->newLine();
            
            // 2. Nettoyer les anciennes conversations de test
            $this->info('🧹 Nettoyage des anciennes conversations...');
            Conversation::whereIn('created_by', [$user1->id, $user2->id, $user3->id])->delete();
            
            // 3. Test 1: Conversation privée entre User1 et User2
            $this->info("💬 Test 1: Création d'une conversation privée entre {$user1->name} et {$user2->name}...");
            
            $conversation1 = Conversation::create([
                'name' => null,
                'type' => 'private',
                'created_by' => $user1->id,
                'last_message_at' => now()
            ]);
            
            $conversation1->addParticipant($user1, true);
            $conversation1->addParticipant($user2);
            
            // Ajouter quelques messages
            $messages1 = [
                ['user' => $user1, 'content' => "Salut {$user2->name} ! Comment ça va ?"],
                ['user' => $user2, 'content' => "Salut {$user1->name} ! Ça va bien, merci ! Et toi ?"],
                ['user' => $user1, 'content' => "Très bien aussi ! Tu fais quoi ce weekend ?"],
                ['user' => $user2, 'content' => "Je pensais aller au cinéma. Tu veux venir ?"],
                ['user' => $user1, 'content' => "Excellente idée ! Quel film ?"]
            ];
            
            foreach ($messages1 as $i => $msg) {
                Message::create([
                    'conversation_id' => $conversation1->id,
                    'user_id' => $msg['user']->id,
                    'content' => $msg['content'],
                    'type' => 'text',
                    'created_at' => now()->addMinutes($i * 2)
                ]);
            }
            
            $conversation1->update(['last_message_at' => now()->addMinutes(count($messages1) * 2)]);
            
            $this->line('   ✅ Conversation privée créée avec ' . count($messages1) . ' messages');
            $this->newLine();
            
            // 4. Test 2: Groupe de famille entre les 3 utilisateurs
            $this->info('👨‍👩‍👧 Test 2: Création d\'un groupe familial avec les 3 utilisateurs...');
            
            $conversation2 = Conversation::create([
                'name' => "Famille {$user1->name}",
                'type' => 'group',
                'created_by' => $user1->id,
                'last_message_at' => now()
            ]);
            
            $conversation2->addParticipant($user1, true);
            $conversation2->addParticipant($user2);
            $conversation2->addParticipant($user3);
            
            // Ajouter des messages de groupe
            $messages2 = [
                ['user' => $user1, 'content' => "Salut la famille ! J'ai créé ce groupe pour qu'on puisse tous discuter ensemble 👨‍👩‍👧"],
                ['user' => $user2, 'content' => "Super idée ! Merci {$user1->name} 😊"],
                ['user' => $user3, 'content' => "Génial ! Enfin on peut tous se parler en même temps !"],
                ['user' => $user1, 'content' => "On pourrait organiser un repas de famille ce dimanche ?"],
                ['user' => $user2, 'content' => "Excellente idée ! Je peux apporter le dessert 🍰"],
                ['user' => $user3, 'content' => "Et moi je m'occupe de l'apéritif ! 🥂"],
                ['user' => $user1, 'content' => "Parfait ! Rendez-vous chez moi à 12h alors !"]
            ];
            
            foreach ($messages2 as $i => $msg) {
                Message::create([
                    'conversation_id' => $conversation2->id,
                    'user_id' => $msg['user']->id,
                    'content' => $msg['content'],
                    'type' => 'text',
                    'created_at' => now()->addMinutes(($i + 10) * 3)
                ]);
            }
            
            $conversation2->update(['last_message_at' => now()->addMinutes((count($messages2) + 10) * 3)]);
            
            $this->line('   ✅ Groupe familial créé avec ' . count($messages2) . ' messages');
            $this->newLine();
            
            // 5. Test 3: Conversation privée entre User1 et User3
            $this->info("💬 Test 3: Création d'une conversation privée entre {$user1->name} et {$user3->name}...");
            
            $conversation3 = Conversation::create([
                'name' => null,
                'type' => 'private',
                'created_by' => $user1->id,
                'last_message_at' => now()
            ]);
            
            $conversation3->addParticipant($user1, true);
            $conversation3->addParticipant($user3);
            
            // Ajouter quelques messages
            $messages3 = [
                ['user' => $user1, 'content' => "Hey {$user3->name} ! Tu as vu le message dans le groupe ?"],
                ['user' => $user3, 'content' => "Oui ! J'ai hâte de ce repas de famille !"],
                ['user' => $user1, 'content' => "Moi aussi ! Au fait, tu peux venir un peu plus tôt pour m'aider à préparer ?"],
                ['user' => $user3, 'content' => "Bien sûr ! Vers 11h ça va ?"],
                ['user' => $user1, 'content' => "Parfait ! Merci beaucoup ! 😊"]
            ];
            
            foreach ($messages3 as $i => $msg) {
                Message::create([
                    'conversation_id' => $conversation3->id,
                    'user_id' => $msg['user']->id,
                    'content' => $msg['content'],
                    'type' => 'text',
                    'created_at' => now()->addMinutes(($i + 30) * 2)
                ]);
            }
            
            $conversation3->update(['last_message_at' => now()->addMinutes((count($messages3) + 30) * 2)]);
            
            $this->line('   ✅ Deuxième conversation privée créée avec ' . count($messages3) . ' messages');
            $this->newLine();
            
            // 6. Résumé des tests
            $this->info('📊 RÉSUMÉ DES TESTS :');
            $this->line('═══════════════════════════════════════════════════════');
            $this->line("✅ Conversation privée {$user1->name} ↔ {$user2->name} : " . count($messages1) . ' messages');
            $this->line("✅ Groupe familial (3 membres) : " . count($messages2) . ' messages');
            $this->line("✅ Conversation privée {$user1->name} ↔ {$user3->name} : " . count($messages3) . ' messages');
            $this->newLine();
            $this->line('🎯 TOTAL : 3 conversations avec ' . (count($messages1) + count($messages2) + count($messages3)) . ' messages');
            $this->newLine();
            
            $this->info('🌐 LIENS DE TEST :');
            $this->line('═══════════════════════════════════════════════════════');
            $this->line('📱 Messagerie principale : https://yamsoo.test/messagerie');
            $this->line("👤 Conversation avec {$user2->name} : https://yamsoo.test/messagerie?selectedContactId={$user2->id}");
            $this->line("👤 Conversation avec {$user3->name} : https://yamsoo.test/messagerie?selectedContactId={$user3->id}");
            $this->line('👨‍👩‍👧 Page famille : https://yamsoo.test/famille');
            $this->line('🌐 Page réseaux : https://yamsoo.test/networks');
            $this->newLine();
            
            $this->info('✅ TESTS TERMINÉS AVEC SUCCÈS !');
            
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors des tests : ' . $e->getMessage());
            $this->line('   Trace : ' . $e->getTraceAsString());
            return 1;
        }

        return 0;
    }
}
