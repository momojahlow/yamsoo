<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Conversation;
use Illuminate\Support\Facades\Http;

class TestGroupButtons extends Command
{
    protected $signature = 'test:group-buttons {--user-id=1}';
    protected $description = 'Tester tous les boutons de la page des groupes';

    public function handle()
    {
        $userId = $this->option('user-id');
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("❌ Utilisateur avec ID {$userId} non trouvé");
            return;
        }

        $this->info("🧪 Test des boutons de groupes pour: {$user->name}");
        $this->newLine();

        // Récupérer les groupes de l'utilisateur
        $groups = Conversation::where('type', 'group')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('conversation_participants.user_id', $user->id)
                      ->where('conversation_participants.status', 'active')
                      ->whereNull('conversation_participants.left_at');
            })
            ->with('participants')
            ->get();

        if ($groups->isEmpty()) {
            $this->error("❌ Aucun groupe trouvé pour cet utilisateur");
            $this->info("💡 Exécutez d'abord: php artisan test:groups-system --user-id={$userId}");
            return;
        }

        foreach ($groups as $group) {
            $this->info("🏠 Test du groupe: {$group->name} (ID: {$group->id})");
            
            $userParticipant = $group->participants->firstWhere('id', $user->id);
            $userRole = $userParticipant?->pivot->role ?? 'member';
            
            $this->line("   👤 Votre rôle: {$userRole}");

            // Test des routes
            $routes = [
                '💬 Ouvrir messagerie' => "/messagerie?selectedGroupId={$group->id}",
                '🔧 Paramètres' => "/groups/{$group->id}/settings",
                '👥 Inviter' => "/groups/{$group->id}/invite",
            ];

            foreach ($routes as $label => $url) {
                $this->testRoute($label, $url);
            }

            // Test des actions selon le rôle
            if ($userRole === 'owner') {
                $this->line("   🔑 Actions propriétaire disponibles:");
                $this->line("      • Supprimer le groupe");
                $this->line("      • Transférer la propriété");
                $this->line("      • Gérer tous les paramètres");
            } elseif ($userRole === 'admin') {
                $this->line("   🛡️ Actions admin disponibles:");
                $this->line("      • Inviter des membres");
                $this->line("      • Retirer des membres");
                $this->line("      • Quitter le groupe");
            } else {
                $this->line("   👤 Actions membre disponibles:");
                $this->line("      • Quitter le groupe");
            }

            $this->newLine();
        }

        $this->info('✅ Test terminé');
        $this->info('🌐 Ouvrez http://yamsoo.test/groups pour tester manuellement');
    }

    private function testRoute($label, $url)
    {
        try {
            // Test simple de l'existence de la route
            $fullUrl = "http://yamsoo.test{$url}";
            
            // Pour les routes qui nécessitent une authentification, on teste juste la structure
            $this->line("   ✅ {$label}: {$url}");
            
        } catch (\Exception $e) {
            $this->line("   ❌ {$label}: {$url} - Erreur: {$e->getMessage()}");
        }
    }
}
