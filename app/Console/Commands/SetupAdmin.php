<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetupAdmin extends Command
{
    protected $signature = 'setup:admin';
    protected $description = 'Configuration initiale du système d\'administration Yamsoo';

    public function handle()
    {
        $this->info('🚀 CONFIGURATION DU SYSTÈME D\'ADMINISTRATION YAMSOO');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Vérifier s'il existe déjà un super admin
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        
        if ($existingSuperAdmin) {
            $this->info('✅ Super administrateur déjà configuré :');
            $this->line("   Nom : {$existingSuperAdmin->name}");
            $this->line("   Email : {$existingSuperAdmin->email}");
            $this->newLine();
        } else {
            $this->info('🔧 Création du super administrateur par défaut...');
            
            try {
                $superAdmin = User::create([
                    'name' => 'Super Admin',
                    'email' => 'admin@yamsoo.com',
                    'password' => Hash::make('AdminYamsoo2024!'),
                    'role' => 'super_admin',
                    'is_active' => true,
                    'role_assigned_at' => now(),
                    'email_verified_at' => now(),
                ]);

                $this->info('✅ Super administrateur créé avec succès !');
                $this->line("   Email : admin@yamsoo.com");
                $this->line("   Mot de passe : AdminYamsoo2024!");
                $this->newLine();
            } catch (\Exception $e) {
                $this->error('❌ Erreur lors de la création : ' . $e->getMessage());
                return 1;
            }
        }

        // Promouvoir Nadia en administrateur pour les tests
        $nadia = User::where('name', 'like', '%Nadia%')->first();
        if ($nadia && $nadia->role === 'user') {
            $nadia->assignRole('admin');
            $this->info('✅ Nadia Berrada promue administratrice pour les tests');
        }

        // Statistiques du système
        $this->info('📊 STATISTIQUES DU SYSTÈME :');
        $stats = [
            'total_users' => User::count(),
            'super_admins' => User::where('role', 'super_admin')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'moderators' => User::where('role', 'moderator')->count(),
            'users' => User::where('role', 'user')->count(),
            'active_users' => User::where('is_active', true)->count(),
        ];

        foreach ($stats as $key => $value) {
            $label = match($key) {
                'total_users' => 'Total utilisateurs',
                'super_admins' => 'Super administrateurs',
                'admins' => 'Administrateurs',
                'moderators' => 'Modérateurs',
                'users' => 'Utilisateurs',
                'active_users' => 'Utilisateurs actifs',
            };
            $this->line("   {$label} : {$value}");
        }
        $this->newLine();

        $this->info('🔗 ACCÈS À L\'ADMINISTRATION :');
        $this->line('   URL de connexion : ' . url('/login'));
        $this->line('   Panel admin : ' . url('/admin'));
        $this->newLine();

        $this->info('🛡️  FONCTIONNALITÉS DISPONIBLES :');
        $this->line('   ✅ Tableau de bord administrateur');
        $this->line('   ✅ Gestion des utilisateurs');
        $this->line('   ✅ Modération de contenu');
        $this->line('   ✅ Statistiques système');
        $this->line('   ✅ Gestion des rôles et permissions');
        $this->newLine();

        $this->info('🎯 CONFIGURATION TERMINÉE !');
        $this->line('   Le système d\'administration Yamsoo est prêt à être utilisé.');

        return 0;
    }
}
