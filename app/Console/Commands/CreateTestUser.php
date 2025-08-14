<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'test:create-user';
    protected $description = 'Créer un utilisateur de test';

    public function handle()
    {
        $this->info("🔍 Création d'un utilisateur de test");
        
        try {
            // Vérifier si l'utilisateur existe déjà
            $user = User::where('email', 'test@example.com')->first();
            
            if ($user) {
                $this->info("✅ Utilisateur de test existe déjà: {$user->name}");
                $this->info("📧 Email: {$user->email}");
                $this->info("🔑 Mot de passe: password");
                return;
            }
            
            // Créer l'utilisateur
            $user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            
            $this->info("✅ Utilisateur de test créé: {$user->name}");
            $this->info("📧 Email: {$user->email}");
            $this->info("🔑 Mot de passe: password");
            
        } catch (\Exception $e) {
            $this->error("❌ Erreur: " . $e->getMessage());
        }
    }
}
