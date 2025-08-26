<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestAuthPersistence extends Command
{
    protected $signature = 'test:auth-persistence';
    protected $description = 'Tester la persistance de l\'authentification';

    public function handle()
    {
        $this->info('🧪 Test de la persistance d\'authentification');
        $this->newLine();
        
        // Créer/récupérer un utilisateur de test
        $user = User::firstOrCreate(
            ['email' => 'test@yamsoo.test'],
            [
                'name' => 'Utilisateur Test',
                'password' => Hash::make('password123'),
                'email_verified_at' => now()
            ]
        );
        
        $this->info("✅ Utilisateur de test: {$user->name} ({$user->email})");
        $this->info("🔑 Mot de passe: password123");
        $this->newLine();
        
        // Vérifier la configuration des sessions
        $this->info('📋 Configuration des sessions:');
        $this->line("- Driver: " . config('session.driver'));
        $this->line("- Lifetime: " . config('session.lifetime') . " minutes");
        $this->line("- Secure: " . (config('session.secure') ? 'true' : 'false'));
        $this->line("- Same Site: " . config('session.same_site'));
        $this->line("- Domain: " . (config('session.domain') ?: 'null'));
        $this->newLine();
        
        // Vérifier la configuration de l'application
        $this->info('🌐 Configuration de l\'application:');
        $this->line("- APP_URL: " . config('app.url'));
        $this->line("- APP_ENV: " . config('app.env'));
        $this->line("- APP_DEBUG: " . (config('app.debug') ? 'true' : 'false'));
        $this->newLine();
        
        // Instructions de test
        $this->info('🎯 Instructions de test:');
        $this->line('1. Ouvrez http://yamsoo.test dans votre navigateur');
        $this->line('2. Connectez-vous avec test@yamsoo.test / password123');
        $this->line('3. Ouvrez un nouvel onglet sur http://yamsoo.test');
        $this->line('4. Vérifiez que vous êtes toujours connecté');
        $this->newLine();
        
        // Vérifier les routes d'authentification
        $this->info('🔗 Routes d\'authentification disponibles:');
        if (\Route::has('login')) {
            $this->line('✅ Route login disponible');
        } else {
            $this->error('❌ Route login manquante');
        }
        
        if (\Route::has('register')) {
            $this->line('✅ Route register disponible');
        } else {
            $this->error('❌ Route register manquante');
        }
        
        if (\Route::has('dashboard')) {
            $this->line('✅ Route dashboard disponible');
        } else {
            $this->error('❌ Route dashboard manquante');
        }
        
        $this->newLine();
        $this->info('✅ Test terminé !');
        
        return 0;
    }
}
