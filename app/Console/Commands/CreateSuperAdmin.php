<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'admin:create-super {--email=} {--name=} {--password=}';
    protected $description = 'Créer un super administrateur pour Yamsoo';

    public function handle()
    {
        $this->info('🔐 CRÉATION D\'UN SUPER ADMINISTRATEUR YAMSOO');
        $this->info('═══════════════════════════════════════════════════════');
        $this->newLine();

        // Récupérer ou demander les informations
        $email = $this->option('email') ?: $this->ask('Email du super administrateur');
        $name = $this->option('name') ?: $this->ask('Nom complet');
        $password = $this->option('password') ?: $this->secret('Mot de passe (min. 8 caractères)');

        // Validation
        $validator = Validator::make([
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string|min:2|max:255',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('❌ Erreurs de validation :');
            foreach ($validator->errors()->all() as $error) {
                $this->line("   • {$error}");
            }
            return 1;
        }

        // Vérifier s'il existe déjà un super admin
        $existingSuperAdmin = User::where('role', 'super_admin')->first();
        if ($existingSuperAdmin) {
            $this->warn('⚠️  Un super administrateur existe déjà :');
            $this->line("   Nom : {$existingSuperAdmin->name}");
            $this->line("   Email : {$existingSuperAdmin->email}");
            $this->newLine();
            
            if (!$this->confirm('Voulez-vous créer un autre super administrateur ?')) {
                $this->info('Opération annulée.');
                return 0;
            }
        }

        // Confirmation
        $this->info('📋 INFORMATIONS DU SUPER ADMINISTRATEUR :');
        $this->line("   Nom : {$name}");
        $this->line("   Email : {$email}");
        $this->line("   Rôle : Super Administrateur");
        $this->newLine();

        if (!$this->confirm('Confirmer la création ?')) {
            $this->info('Opération annulée.');
            return 0;
        }

        try {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'role_assigned_at' => now(),
                'email_verified_at' => now(), // Auto-vérifier l'email
            ]);

            $this->newLine();
            $this->info('✅ SUPER ADMINISTRATEUR CRÉÉ AVEC SUCCÈS !');
            $this->line("   ID : {$user->id}");
            $this->line("   Nom : {$user->name}");
            $this->line("   Email : {$user->email}");
            $this->line("   Rôle : {$user->role_name}");
            $this->newLine();

            $this->info('🔗 ACCÈS À L\'ADMINISTRATION :');
            $this->line('   URL de connexion : ' . url('/login'));
            $this->line('   Panel admin : ' . url('/admin'));
            $this->newLine();

            $this->info('🛡️  PERMISSIONS ACCORDÉES :');
            $this->line('   ✅ Accès complet au panel d\'administration');
            $this->line('   ✅ Gestion de tous les utilisateurs');
            $this->line('   ✅ Modération de tout le contenu');
            $this->line('   ✅ Configuration système');
            $this->line('   ✅ Sauvegarde et restauration');
            $this->line('   ✅ Promotion/rétrogradation d\'administrateurs');
            $this->newLine();

            $this->warn('⚠️  SÉCURITÉ :');
            $this->line('   • Gardez ces informations confidentielles');
            $this->line('   • Utilisez un mot de passe fort');
            $this->line('   • Activez l\'authentification à deux facteurs si disponible');
            $this->newLine();

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création :');
            $this->line("   {$e->getMessage()}");
            return 1;
        }
    }
}
