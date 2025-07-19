<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateUserLastSeen extends Command
{
    protected $signature = 'users:update-last-seen';
    protected $description = 'Mettre à jour le last_seen_at des utilisateurs existants';

    public function handle()
    {
        $this->info('🔄 MISE À JOUR DU STATUT EN LIGNE DES UTILISATEURS');
        $this->info('═══════════════════════════════════════════════');
        $this->newLine();

        $users = User::whereNull('last_seen_at')->get();
        
        if ($users->isEmpty()) {
            $this->info('✅ Tous les utilisateurs ont déjà un last_seen_at défini.');
            return 0;
        }

        $this->info("📊 {$users->count()} utilisateurs à mettre à jour...");
        $this->newLine();

        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        foreach ($users as $user) {
            // Définir un last_seen_at aléatoire dans les dernières 24h
            $randomMinutes = rand(5, 1440); // Entre 5 minutes et 24h
            $lastSeen = now()->subMinutes($randomMinutes);
            
            $user->update(['last_seen_at' => $lastSeen]);
            
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('✅ Mise à jour terminée !');
        $this->line("   {$users->count()} utilisateurs mis à jour");
        
        // Afficher quelques statistiques
        $onlineUsers = User::where('last_seen_at', '>', now()->subMinutes(5))->count();
        $recentUsers = User::where('last_seen_at', '>', now()->subHour())->count();
        
        $this->newLine();
        $this->info('📈 STATISTIQUES :');
        $this->line("   👥 Utilisateurs en ligne (< 5 min) : {$onlineUsers}");
        $this->line("   🕐 Utilisateurs récents (< 1h) : {$recentUsers}");
        $this->line("   📊 Total utilisateurs : " . User::count());

        return 0;
    }
}
