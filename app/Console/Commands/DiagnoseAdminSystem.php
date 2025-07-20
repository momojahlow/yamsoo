<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class DiagnoseAdminSystem extends Command
{
    protected $signature = 'diagnose:admin-system';
    protected $description = 'Diagnostiquer le système d\'administration';

    public function handle()
    {
        $this->info('🔍 DIAGNOSTIC DU SYSTÈME D\'ADMINISTRATION');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Vérifier la table admins
        try {
            $adminCount = Admin::count();
            $this->info("✅ Table 'admins' accessible : {$adminCount} administrateurs");
        } catch (\Exception $e) {
            $this->error("❌ Erreur table 'admins' : " . $e->getMessage());
            return 1;
        }

        // Vérifier les routes admin
        $this->info('🛣️  ROUTES ADMIN :');
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->uri(), 'admin');
        });

        if ($adminRoutes->count() > 0) {
            $this->info("   ✅ {$adminRoutes->count()} routes admin trouvées");
            foreach ($adminRoutes->take(5) as $route) {
                $this->line("      • {$route->methods()[0]} /{$route->uri()}");
            }
            if ($adminRoutes->count() > 5) {
                $this->line("      ... et " . ($adminRoutes->count() - 5) . " autres");
            }
        } else {
            $this->error('   ❌ Aucune route admin trouvée');
        }
        $this->newLine();

        // Vérifier les middlewares
        $this->info('🛡️  MIDDLEWARES :');
        try {
            $middleware = app('router')->getMiddleware();
            if (isset($middleware['admin.auth'])) {
                $this->info('   ✅ Middleware admin.auth enregistré');
            } else {
                $this->error('   ❌ Middleware admin.auth manquant');
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Erreur middleware : ' . $e->getMessage());
        }
        $this->newLine();

        // Vérifier les guards d'authentification
        $this->info('🔐 GUARDS D\'AUTHENTIFICATION :');
        $guards = config('auth.guards');
        if (isset($guards['admin'])) {
            $this->info('   ✅ Guard admin configuré');
            $this->line("      Driver : {$guards['admin']['driver']}");
            $this->line("      Provider : {$guards['admin']['provider']}");
        } else {
            $this->error('   ❌ Guard admin manquant');
        }
        $this->newLine();

        // Vérifier les providers
        $this->info('👥 PROVIDERS D\'AUTHENTIFICATION :');
        $providers = config('auth.providers');
        if (isset($providers['admins'])) {
            $this->info('   ✅ Provider admins configuré');
            $this->line("      Driver : {$providers['admins']['driver']}");
            $this->line("      Model : {$providers['admins']['model']}");
        } else {
            $this->error('   ❌ Provider admins manquant');
        }
        $this->newLine();

        // Lister les administrateurs
        $this->info('👤 ADMINISTRATEURS :');
        $admins = Admin::all();
        if ($admins->count() > 0) {
            foreach ($admins as $admin) {
                $status = $admin->is_active ? '🟢 Actif' : '🔴 Inactif';
                $this->line("   • {$admin->name} ({$admin->email}) - {$admin->role_name} {$status}");
            }
        } else {
            $this->warn('   ⚠️  Aucun administrateur trouvé');
            $this->line('   Exécutez : php artisan test:admin-login');
        }
        $this->newLine();

        // URLs de test
        $this->info('🌐 URLS DE TEST :');
        $this->line('   Connexion admin : ' . url('/admin/login'));
        $this->line('   Dashboard admin : ' . url('/admin'));
        $this->newLine();

        // Instructions
        $this->info('📋 INSTRUCTIONS DE TEST :');
        $this->line('   1. Ouvrez : ' . url('/admin/login'));
        $this->line('   2. Connectez-vous avec :');
        $this->line('      • Email : admin@exemple.com');
        $this->line('      • Mot de passe : password');
        $this->line('   3. Vérifiez l\'accès au dashboard');
        $this->newLine();

        // Résumé
        $errors = 0;
        if (Admin::count() === 0) $errors++;
        if (!isset($middleware['admin.auth'])) $errors++;
        if (!isset($guards['admin'])) $errors++;
        if (!isset($providers['admins'])) $errors++;

        if ($errors === 0) {
            $this->info('🎯 DIAGNOSTIC RÉUSSI !');
            $this->line('   Le système d\'administration est opérationnel.');
        } else {
            $this->error("❌ {$errors} problème(s) détecté(s)");
            $this->line('   Vérifiez la configuration ci-dessus.');
        }

        return $errors === 0 ? 0 : 1;
    }
}
