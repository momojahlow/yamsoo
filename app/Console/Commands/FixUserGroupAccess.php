<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;

class FixUserGroupAccess extends Command
{
    protected $signature = 'fix:user-group-access {--user-id=1}';
    protected $description = 'S\'assurer que l\'utilisateur a accès aux groupes qu\'il essaie de modifier';

    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("🔧 Vérification de l'accès aux groupes pour: {$user->name}");
        $this->newLine();

        // Lister tous les groupes
        $allGroups = Conversation::where('type', 'group')->get();
        
        $this->info("📋 Tous les groupes existants:");
        foreach ($allGroups as $group) {
            $this->line("  • ID: {$group->id} | Nom: {$group->name}");
            
            // Vérifier si l'utilisateur est participant
            $participant = $group->participants()->where('user_id', $user->id)->first();
            
            if ($participant) {
                $role = $participant->pivot->role;
                $status = $participant->pivot->status;
                $leftAt = $participant->pivot->left_at;
                
                if ($leftAt) {
                    $this->line("    ❌ Vous avez quitté ce groupe le {$leftAt}");
                    
                    // Réactiver l'utilisateur dans ce groupe
                    $group->participants()->updateExistingPivot($user->id, [
                        'left_at' => null,
                        'status' => 'active'
                    ]);
                    $this->line("    ✅ Accès restauré automatiquement");
                    
                } elseif ($status !== 'active') {
                    $this->line("    ⚠️ Status: {$status} (non actif)");
                    
                    // Activer l'utilisateur
                    $group->participants()->updateExistingPivot($user->id, [
                        'status' => 'active'
                    ]);
                    $this->line("    ✅ Status activé automatiquement");
                    
                } else {
                    $this->line("    ✅ Accès OK - Rôle: {$role} | Status: {$status}");
                }
            } else {
                $this->line("    ❌ Vous n'êtes pas participant de ce groupe");
                
                // Ajouter l'utilisateur comme propriétaire si c'est le groupe qu'il essaie de modifier
                if (in_array($group->id, [4])) { // Groupe ID 4 qui pose problème
                    $group->participants()->attach($user->id, [
                        'role' => 'owner',
                        'status' => 'active',
                        'notifications_enabled' => true,
                        'joined_at' => now()
                    ]);
                    $this->line("    ✅ Ajouté comme propriétaire automatiquement");
                }
            }
        }

        $this->newLine();
        
        // Vérifier les groupes accessibles après correction
        $accessibleGroups = Conversation::where('type', 'group')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('conversation_participants.user_id', $user->id)
                      ->where('conversation_participants.status', 'active')
                      ->whereNull('conversation_participants.left_at');
            })
            ->get(['id', 'name']);

        $this->info("🏠 Groupes accessibles après correction:");
        foreach ($accessibleGroups as $group) {
            $this->line("  ✅ ID: {$group->id} | Nom: {$group->name}");
            $this->line("     🌐 Settings: http://yamsoo.test/groups/{$group->id}/settings");
        }

        if ($accessibleGroups->isEmpty()) {
            $this->warn("⚠️ Aucun groupe accessible trouvé");
        } else {
            $this->info("✅ Correction terminée - {$accessibleGroups->count()} groupes accessibles");
        }
    }
}
