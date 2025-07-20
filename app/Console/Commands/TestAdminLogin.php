<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestAdminLogin extends Command
{
    protected $signature = 'test:admin-login';
    protected $description = 'Créer un admin de test et afficher les informations de connexion';

    public function handle()
    {
        $this->info('🔐 CRÉATION D\'UN ADMIN DE TEST');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        try {
            // Créer ou récupérer l'admin
            $admin = Admin::firstOrCreate(
                ['email' => 'admin@exemple.com'],
                [
                    'name' => 'Admin Principal',
                    'password' => Hash::make('password'),
                    'role' => 'super_admin',
                    'is_active' => true,
                    'permissions' => [
                        'users.view',
                        'users.create', 
                        'users.edit',
                        'users.delete',
                        'content.moderate',
                        'messages.view',
                        'photos.view',
                        'system.settings',
                        'admins.manage'
                    ],
                ]
            );

            if ($admin->wasRecentlyCreated) {
                $this->info('✅ Admin créé avec succès !');
            } else {
                $this->info('✅ Admin existant trouvé !');
            }

            $this->newLine();
            $this->info('📋 INFORMATIONS DE CONNEXION :');
            $this->line("   Email : {$admin->email}");
            $this->line("   Mot de passe : password");
            $this->line("   Rôle : {$admin->role_name}");
            $this->line("   Statut : " . ($admin->is_active ? 'Actif' : 'Inactif'));
            $this->newLine();

            $this->info('🌐 URLS D\'ACCÈS :');
            $this->line('   Page de connexion : ' . url('/admin/login'));
            $this->line('   Dashboard admin : ' . url('/admin'));
            $this->newLine();

            $this->info('🎯 INSTRUCTIONS :');
            $this->line('   1. Ouvrez : ' . url('/admin/login'));
            $this->line('   2. Connectez-vous avec :');
            $this->line('      • Email : admin@exemple.com');
            $this->line('      • Mot de passe : password');
            $this->line('   3. Vous serez redirigé vers le dashboard admin');
            $this->newLine();

            $this->info('✨ SYSTÈME D\'ADMINISTRATION PRÊT !');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur : ' . $e->getMessage());
            return 1;
        }
    }
}
