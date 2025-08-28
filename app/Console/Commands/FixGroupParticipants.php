<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;

class FixGroupParticipants extends Command
{
    protected $signature = 'fix:group-participants {--group-id=1}';
    protected $description = 'S\'assurer que les participants des groupes existent et sont correctement configurés';

    public function handle()
    {
        $groupId = $this->option('group-id');
        $group = Conversation::find($groupId);
        
        if (!$group) {
            $this->error("❌ Groupe avec ID {$groupId} non trouvé");
            return;
        }

        $this->info("🔧 Vérification des participants du groupe: {$group->name} (ID: {$group->id})");
        $this->newLine();

        // Lister tous les participants actuels
        $participants = $group->participants()->get();
        
        $this->info("👥 Participants actuels:");
        foreach ($participants as $participant) {
            $role = $participant->pivot->role;
            $status = $participant->pivot->status;
            $leftAt = $participant->pivot->left_at;
            
            $statusText = $leftAt ? "❌ Quitté le {$leftAt}" : ($status === 'active' ? "✅ Actif" : "⚠️ {$status}");
            
            $this->line("  • ID: {$participant->id} | {$participant->name} | Rôle: {$role} | {$statusText}");
        }

        // Créer des utilisateurs de test s'ils n'existent pas
        $testUsers = [
            ['id' => 17, 'name' => 'Alice Dupont', 'email' => 'alice.test@example.com'],
            ['id' => 18, 'name' => 'Bob Martin', 'email' => 'bob.test@example.com'],
            ['id' => 19, 'name' => 'Claire Durand', 'email' => 'claire.test@example.com'],
        ];

        $this->newLine();
        $this->info("🛠️ Création/vérification des utilisateurs de test:");

        foreach ($testUsers as $userData) {
            $user = User::find($userData['id']);
            
            if (!$user) {
                // Créer l'utilisateur s'il n'existe pas
                $user = User::create([
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'password' => bcrypt('password'),
                    'email_verified_at' => now()
                ]);
                $this->line("  ✅ Créé: {$user->name} (ID: {$user->id})");
            } else {
                $this->line("  ✅ Existe: {$user->name} (ID: {$user->id})");
            }

            // Vérifier s'il est participant du groupe
            $isParticipant = $group->participants()->where('user_id', $user->id)->exists();
            
            if (!$isParticipant && $user->id !== 1) { // Ne pas ajouter l'utilisateur 1 s'il est déjà propriétaire
                // Ajouter comme membre
                $group->participants()->attach($user->id, [
                    'role' => 'member',
                    'status' => 'active',
                    'notifications_enabled' => true,
                    'joined_at' => now()
                ]);
                $this->line("    ➕ Ajouté au groupe comme membre");
            } elseif ($isParticipant) {
                $participant = $group->participants()->where('user_id', $user->id)->first();
                if ($participant->pivot->left_at) {
                    // Réactiver s'il avait quitté
                    $group->participants()->updateExistingPivot($user->id, [
                        'left_at' => null,
                        'status' => 'active'
                    ]);
                    $this->line("    🔄 Réactivé dans le groupe");
                } else {
                    $this->line("    ✅ Déjà membre actif");
                }
            }
        }

        $this->newLine();
        
        // Afficher les participants finaux
        $finalParticipants = $group->participants()
            ->where('conversation_participants.status', 'active')
            ->whereNull('conversation_participants.left_at')
            ->get();
            
        $this->info("🎯 Participants finaux actifs:");
        foreach ($finalParticipants as $participant) {
            $role = $participant->pivot->role;
            $this->line("  • ID: {$participant->id} | {$participant->name} | Rôle: {$role}");
        }

        $this->newLine();
        $this->info("✅ Correction terminée");
        $this->info("🌐 Testez maintenant: http://yamsoo.test/groups");
        $this->info("🎯 Vous devriez pouvoir retirer les participants ID 17, 18, 19");
    }
}
