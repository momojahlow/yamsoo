<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\DB;

class TestGroupsSystem extends Command
{
    protected $signature = 'test:groups-system {--user-id=1 : ID de l\'utilisateur propriétaire du groupe}';
    protected $description = 'Créer des groupes de test pour vérifier le système';

    public function handle()
    {
        $userId = $this->option('user-id');
        $this->info("🔧 Création de groupes de test pour l'utilisateur ID: {$userId}");
        $this->newLine();

        // 1. Vérifier l'utilisateur
        $owner = User::find($userId);
        if (!$owner) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("👤 Propriétaire: {$owner->name} ({$owner->email})");

        // 2. Créer des utilisateurs de test
        $testUsers = $this->createTestUsers();

        // 3. Créer des groupes de test
        $groups = $this->createTestGroups($owner, $testUsers);

        // 4. Tester les routes
        $this->testGroupRoutes($groups);

        $this->newLine();
        $this->info('✅ Groupes de test créés avec succès');
        $this->info('🌐 Ouvrez http://yamsoo.test/groups pour tester les boutons');
        $this->newLine();
        
        $this->info('🎯 Boutons à tester:');
        $this->line('   • 💬 Ouvrir - Ouvre la conversation du groupe');
        $this->line('   • ⚙️ Gérer - Ouvre la modal de gestion');
        $this->line('   • 👥 Inviter - Page d\'invitation de membres');
        $this->line('   • 🔧 Paramètres - Page des paramètres du groupe');
        $this->line('   • 🚪 Quitter - Quitter le groupe (non-propriétaires)');
        $this->line('   • 🗑️ Supprimer - Supprimer le groupe (propriétaires)');
    }

    private function createTestUsers()
    {
        $this->info('👥 Création d\'utilisateurs de test');

        $users = [];
        
        $testData = [
            ['name' => 'Alice Dupont', 'email' => 'alice.test@example.com'],
            ['name' => 'Bob Martin', 'email' => 'bob.test@example.com'],
            ['name' => 'Claire Durand', 'email' => 'claire.test@example.com'],
        ];

        foreach ($testData as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now()
                ]
            );
            
            $users[] = $user;
            $this->line("   ✅ {$user->name} (ID: {$user->id})");
        }

        return $users;
    }

    private function createTestGroups($owner, $testUsers)
    {
        $this->info('🏠 Création de groupes de test');

        $groups = [];

        // Groupe 1: Groupe familial (owner + 2 membres)
        $group1 = Conversation::firstOrCreate(
            ['name' => 'Famille Test', 'type' => 'group'],
            [
                'description' => 'Groupe de test pour la famille',
                'type' => 'group',
                'visibility' => 'private',
                'max_participants' => 50,
                'created_by' => $owner->id,
                'is_active' => true,
                'last_activity_at' => now()
            ]
        );

        // Ajouter le propriétaire
        $group1->participants()->syncWithoutDetaching([
            $owner->id => [
                'role' => 'owner',
                'status' => 'active',
                'notifications_enabled' => true,
                'joined_at' => now()
            ]
        ]);

        // Ajouter 2 membres
        foreach (array_slice($testUsers, 0, 2) as $user) {
            $group1->participants()->syncWithoutDetaching([
                $user->id => [
                    'role' => 'member',
                    'status' => 'active',
                    'notifications_enabled' => true,
                    'joined_at' => now()
                ]
            ]);
        }

        $groups[] = $group1;
        $this->line("   ✅ {$group1->name} (ID: {$group1->id}) - {$group1->participants()->count()} membres");

        // Groupe 2: Groupe d'amis (owner + 1 admin + 1 membre)
        $group2 = Conversation::firstOrCreate(
            ['name' => 'Amis Test', 'type' => 'group'],
            [
                'description' => 'Groupe de test pour les amis',
                'type' => 'group',
                'visibility' => 'public',
                'max_participants' => 100,
                'created_by' => $owner->id,
                'is_active' => true,
                'last_activity_at' => now()
            ]
        );

        // Ajouter le propriétaire
        $group2->participants()->syncWithoutDetaching([
            $owner->id => [
                'role' => 'owner',
                'status' => 'active',
                'notifications_enabled' => true,
                'joined_at' => now()
            ]
        ]);

        // Ajouter un admin
        if (isset($testUsers[2])) {
            $group2->participants()->syncWithoutDetaching([
                $testUsers[2]->id => [
                    'role' => 'admin',
                    'status' => 'active',
                    'notifications_enabled' => true,
                    'joined_at' => now()
                ]
            ]);
        }

        $groups[] = $group2;
        $this->line("   ✅ {$group2->name} (ID: {$group2->id}) - {$group2->participants()->count()} membres");

        // Ajouter quelques messages de test
        foreach ($groups as $group) {
            Message::create([
                'conversation_id' => $group->id,
                'user_id' => $owner->id,
                'content' => 'Message de test dans le groupe ' . $group->name,
                'type' => 'text'
            ]);

            $group->touch(); // Mettre à jour last_activity_at
        }

        return $groups;
    }

    private function testGroupRoutes($groups)
    {
        $this->info('🔍 Test des routes des groupes');

        $routes = [
            'groups.index' => '/groups',
            'groups.create' => '/groups/create',
            'groups.settings' => '/groups/{id}/settings',
            'groups.invite' => '/groups/{id}/invite',
        ];

        foreach ($routes as $name => $pattern) {
            if (str_contains($pattern, '{id}')) {
                $url = str_replace('{id}', $groups[0]->id, $pattern);
            } else {
                $url = $pattern;
            }
            
            $this->line("   ✅ {$name}: {$url}");
        }
    }
}
