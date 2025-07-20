<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;

class AddNadiaToConversations extends Command
{
    protected $signature = 'add:nadia-conversations';
    protected $description = 'Ajouter Nadia aux conversations existantes pour tester le système de messagerie';

    public function handle()
    {
        $this->info('👥 AJOUT DE NADIA AUX CONVERSATIONS');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Trouver Nadia
        $nadia = User::where('name', 'like', '%Nadia%')->first();
        
        if (!$nadia) {
            $this->error('❌ Nadia non trouvée');
            return 1;
        }

        $this->info("👤 UTILISATRICE : {$nadia->name} (ID: {$nadia->id})");
        $this->newLine();

        // Obtenir toutes les conversations
        $conversations = Conversation::with('participants')->get();
        
        $this->info("💬 CONVERSATIONS DISPONIBLES : {$conversations->count()}");
        $this->newLine();

        $addedCount = 0;
        $alreadyInCount = 0;

        foreach ($conversations as $conversation) {
            // Vérifier si Nadia est déjà dans cette conversation
            $isAlreadyParticipant = $conversation->participants()
                ->where('user_id', $nadia->id)
                ->exists();

            if ($isAlreadyParticipant) {
                $this->line("   ✅ Déjà dans : {$conversation->name}");
                $alreadyInCount++;
            } else {
                // Ajouter Nadia à la conversation
                $conversation->participants()->attach($nadia->id);
                $this->line("   ➕ Ajoutée à : {$conversation->name}");
                $addedCount++;

                // Créer un message de bienvenue
                Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $nadia->id,
                    'content' => '👋 Salut tout le monde ! Je viens de rejoindre la conversation.',
                    'type' => 'text'
                ]);
            }
        }

        $this->newLine();
        $this->info('📊 RÉSUMÉ :');
        $this->line("   ➕ Ajoutée à {$addedCount} conversation(s)");
        $this->line("   ✅ Déjà dans {$alreadyInCount} conversation(s)");
        $this->newLine();

        // Créer une conversation familiale spéciale pour Nadia
        $this->info('🏠 CRÉATION D\'UNE CONVERSATION FAMILIALE POUR NADIA :');
        
        // Trouver les membres de la famille de Nadia
        $familyMembers = collect();
        
        // Ajouter Mohammed (mari)
        $mohammed = User::where('name', 'like', '%Mohammed%')->where('name', 'like', '%Alami%')->first();
        if ($mohammed) {
            $familyMembers->push($mohammed);
        }

        // Ajouter Karim (fils)
        $karim = User::where('name', 'like', '%Karim%')->first();
        if ($karim) {
            $familyMembers->push($karim);
        }

        // Ajouter Ahmed (beau-frère)
        $ahmed = User::where('name', 'like', '%Ahmed%')->where('name', 'like', '%Benali%')->first();
        if ($ahmed) {
            $familyMembers->push($ahmed);
        }

        if ($familyMembers->isNotEmpty()) {
            // Créer la conversation familiale
            $familyConversation = Conversation::create([
                'name' => 'Famille Nadia Berrada',
                'type' => 'group',
                'created_by' => $nadia->id
            ]);

            // Ajouter Nadia
            $familyConversation->participants()->attach($nadia->id);
            
            // Ajouter les membres de la famille
            foreach ($familyMembers as $member) {
                $familyConversation->participants()->attach($member->id);
                $this->line("   👥 {$member->name} ajouté à la conversation familiale");
            }

            // Message de bienvenue
            Message::create([
                'conversation_id' => $familyConversation->id,
                'user_id' => $nadia->id,
                'content' => '🏠 Bienvenue dans notre conversation familiale ! Ici nous pouvons tous rester en contact.',
                'type' => 'text'
            ]);

            $this->line("   ✅ Conversation familiale créée : {$familyConversation->name}");
        }

        $this->newLine();
        $this->info('🎯 TERMINÉ !');
        $this->line('   Nadia peut maintenant utiliser le système de messagerie.');
        $this->line('   Visitez : http://yamsoo.test/messages');

        return 0;
    }
}
