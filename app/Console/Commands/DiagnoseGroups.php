<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DiagnoseGroups extends Command
{
    protected $signature = 'diagnose:groups {--user-id=1}';
    protected $description = 'Diagnostiquer les groupes existants et les permissions';

    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("🔍 Diagnostic des groupes pour: {$user->name}");
        $this->newLine();

        // Lister tous les groupes
        $allGroups = Conversation::where('type', 'group')->with('participants')->get();
        
        $this->info("📋 Tous les groupes dans la base de données:");
        foreach ($allGroups as $group) {
            $this->line("  • ID: {$group->id} | Nom: {$group->name} | Participants: {$group->participants->count()}");
            
            // Vérifier si l'utilisateur est participant
            $userParticipant = $group->participants->firstWhere('id', $user->id);
            if ($userParticipant) {
                $role = $userParticipant->pivot->role ?? 'member';
                $status = $userParticipant->pivot->status ?? 'active';
                $leftAt = $userParticipant->pivot->left_at;
                
                $this->line("    ✅ Vous êtes participant - Rôle: {$role} | Status: {$status}" . ($leftAt ? " | Quitté: {$leftAt}" : ""));
                
                // Tester les permissions
                try {
                    $canUpdate = $user->can('updateSettings', $group);
                    $this->line("    🔐 Permission updateSettings: " . ($canUpdate ? "✅ OUI" : "❌ NON"));
                } catch (\Exception $e) {
                    $this->line("    ❌ Erreur permission: " . $e->getMessage());
                }
            } else {
                $this->line("    ❌ Vous n'êtes pas participant de ce groupe");
            }
        }

        $this->newLine();
        
        // Lister les groupes accessibles par l'utilisateur
        $userGroups = Conversation::where('type', 'group')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('conversation_participants.user_id', $user->id)
                      ->where('conversation_participants.status', 'active')
                      ->whereNull('conversation_participants.left_at');
            })
            ->get();

        $this->info("🏠 Groupes accessibles par l'utilisateur:");
        foreach ($userGroups as $group) {
            $this->line("  • ID: {$group->id} | Nom: {$group->name}");
            $this->line("    🌐 URL Settings: http://yamsoo.test/groups/{$group->id}/settings");
        }

        if ($userGroups->isEmpty()) {
            $this->warn("⚠️ Aucun groupe accessible trouvé");
            $this->info("💡 Exécutez: php artisan test:groups-system --user-id={$userId}");
        }
    }
}
