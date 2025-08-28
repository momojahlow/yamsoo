<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;

class CreateGroupId4 extends Command
{
    protected $signature = 'create:group-id-4 {--user-id=1}';
    protected $description = 'Créer spécifiquement le groupe ID 4 pour résoudre l\'erreur 404';

    public function handle()
    {
        $userId = $this->option('user-id');
        $owner = User::find($userId);
        
        if (!$owner) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("🔧 Création du groupe ID 4 pour: {$owner->name}");

        // Vérifier si le groupe ID 4 existe déjà
        $existingGroup = Conversation::find(4);
        if ($existingGroup) {
            $this->info("✅ Le groupe ID 4 existe déjà: {$existingGroup->name}");
            return;
        }

        // Créer le groupe avec un ID spécifique
        $group = new Conversation();
        $group->id = 4; // Forcer l'ID 4
        $group->name = 'Groupe Test ID 4';
        $group->description = 'Groupe créé pour résoudre l\'erreur 404';
        $group->type = 'group';
        $group->visibility = 'private';
        $group->max_participants = 50;
        $group->created_by = $owner->id;
        $group->is_active = true;
        $group->last_activity_at = now();
        $group->save();

        // Ajouter le propriétaire
        $group->participants()->attach($owner->id, [
            'role' => 'owner',
            'status' => 'active',
            'notifications_enabled' => true,
            'joined_at' => now()
        ]);

        // Ajouter quelques membres de test
        $testUsers = User::whereIn('email', [
            'alice.test@example.com',
            'bob.test@example.com'
        ])->get();

        foreach ($testUsers as $user) {
            $group->participants()->attach($user->id, [
                'role' => 'member',
                'status' => 'active',
                'notifications_enabled' => true,
                'joined_at' => now()
            ]);
        }

        $this->info("✅ Groupe créé avec succès:");
        $this->line("   • ID: {$group->id}");
        $this->line("   • Nom: {$group->name}");
        $this->line("   • Participants: {$group->participants()->count()}");
        $this->line("   • URL Settings: http://yamsoo.test/groups/{$group->id}/settings");
        
        $this->newLine();
        $this->info("🎯 Maintenant vous pouvez tester:");
        $this->line("   • Modification des informations générales");
        $this->line("   • Gestion des participants");
        $this->line("   • Toutes les autres fonctionnalités");
    }
}
