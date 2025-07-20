<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Créer un administrateur pour le système d\'administration Yamsoo';

    public function handle()
    {
        $this->info('🔐 CRÉATION D\'UN ADMINISTRATEUR YAMSOO');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Créer l'admin par défaut
        try {
            // Vérifier s'il existe déjà
            $existingAdmin = Admin::where('email', 'admin@exemple.com')->first();
            
            if ($existingAdmin) {
                $this->info('✅ Administrateur déjà existant :');
                $this->line("   Email : {$existingAdmin->email}");
                $this->line("   Nom : {$existingAdmin->name}");
                $this->line("   Rôle : {$existingAdmin->role_name}");
                $this->newLine();
            } else {
                $admin = Admin::create([
                    'name' => 'Administrateur Principal',
                    'email' => 'admin@exemple.com',
                    'password' => Hash::make('password'),
                    'role' => 'super_admin',
                    'is_active' => true,
                    'permissions' => [
                        'users.view',
                        'users.create',
                        'users.edit',
                        'users.delete',
                        'users.ban',
                        'content.moderate',
                        'content.delete',
                        'messages.view',
                        'messages.delete',
                        'photos.view',
                        'photos.delete',
                        'families.manage',
                        'system.settings',
                        'system.backup',
                        'admins.manage',
                        'logs.view',
                        'analytics.view',
                    ],
                ]);

                $this->info('✅ Administrateur créé avec succès !');
                $this->line("   ID : {$admin->id}");
                $this->line("   Nom : {$admin->name}");
                $this->line("   Email : {$admin->email}");
                $this->line("   Rôle : {$admin->role_name}");
                $this->newLine();
            }

            // Créer un modérateur de test
            $moderator = Admin::firstOrCreate(
                ['email' => 'moderator@exemple.com'],
                [
                    'name' => 'Modérateur Test',
                    'password' => Hash::make('password'),
                    'role' => 'moderator',
                    'is_active' => true,
                    'permissions' => [
                        'users.view',
                        'content.moderate',
                        'messages.view',
                        'photos.view',
                    ],
                ]
            );

            if ($moderator->wasRecentlyCreated) {
                $this->info('✅ Modérateur de test créé !');
                $this->line("   Email : {$moderator->email}");
                $this->line("   Rôle : {$moderator->role_name}");
                $this->newLine();
            }

            // Statistiques
            $this->info('📊 STATISTIQUES DES ADMINISTRATEURS :');
            $stats = [
                'total' => Admin::count(),
                'super_admins' => Admin::where('role', 'super_admin')->count(),
                'admins' => Admin::where('role', 'admin')->count(),
                'moderators' => Admin::where('role', 'moderator')->count(),
                'active' => Admin::where('is_active', true)->count(),
            ];

            foreach ($stats as $key => $value) {
                $label = match($key) {
                    'total' => 'Total administrateurs',
                    'super_admins' => 'Super administrateurs',
                    'admins' => 'Administrateurs',
                    'moderators' => 'Modérateurs',
                    'active' => 'Administrateurs actifs',
                };
                $this->line("   {$label} : {$value}");
            }
            $this->newLine();

            $this->info('🔗 ACCÈS À L\'ADMINISTRATION :');
            $this->line('   URL de connexion : ' . url('/admin/login'));
            $this->line('   Dashboard : ' . url('/admin'));
            $this->newLine();

            $this->info('🔑 COMPTES DE TEST :');
            $this->line('   Super Admin :');
            $this->line('      Email : admin@exemple.com');
            $this->line('      Mot de passe : password');
            $this->newLine();
            $this->line('   Modérateur :');
            $this->line('      Email : moderator@exemple.com');
            $this->line('      Mot de passe : password');
            $this->newLine();

            $this->info('🛡️  PERMISSIONS DISPONIBLES :');
            $permissions = Admin::getAvailablePermissions();
            foreach ($permissions as $key => $description) {
                $this->line("   • {$key} : {$description}");
            }
            $this->newLine();

            $this->info('🎯 SYSTÈME D\'ADMINISTRATION PRÊT !');
            $this->line('   Les administrateurs peuvent maintenant se connecter.');

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création :');
            $this->line("   {$e->getMessage()}");
            return 1;
        }
    }
}
