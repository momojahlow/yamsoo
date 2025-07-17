<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Console\Command;

class UpdateUserGenders extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'users:update-genders {--dry-run : Afficher les changements sans les appliquer}';

    /**
     * The console command description.
     */
    protected $description = 'Met à jour les genres des utilisateurs basé sur leurs prénoms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔍 Analyse des profils utilisateurs...');
        
        // Récupérer tous les utilisateurs sans genre défini ou avec genre 'other'
        $usersWithoutGender = User::with('profile')
            ->whereHas('profile', function($query) {
                $query->whereNull('gender')
                      ->orWhere('gender', 'other')
                      ->orWhere('gender', '');
            })
            ->get();
            
        if ($usersWithoutGender->isEmpty()) {
            $this->info('✅ Tous les utilisateurs ont déjà un genre défini !');
            return;
        }
        
        $this->info("📊 {$usersWithoutGender->count()} utilisateur(s) sans genre défini trouvé(s)");
        
        // Dictionnaire de prénoms avec genres
        $maleNames = [
            'ahmed', 'mohammed', 'youssef', 'hassan', 'karim', 'omar', 'adil', 'rachid',
            'mohamed', 'ali', 'said', 'khalid', 'abdelaziz', 'mustapha', 'hamid',
            'abdellah', 'abderrahim', 'abdelkader', 'brahim', 'driss'
        ];
        
        $femaleNames = [
            'fatima', 'amina', 'leila', 'nadia', 'sara', 'zineb', 'hanae', 'khadija',
            'aicha', 'malika', 'latifa', 'zohra', 'hafida', 'rajae', 'samira',
            'naima', 'zahra', 'meryem', 'salma', 'imane'
        ];
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($usersWithoutGender as $user) {
            $profile = $user->profile;
            $firstName = strtolower($profile->first_name ?? '');
            
            $detectedGender = null;
            
            // Détecter le genre basé sur le prénom
            if (in_array($firstName, $maleNames)) {
                $detectedGender = 'male';
            } elseif (in_array($firstName, $femaleNames)) {
                $detectedGender = 'female';
            }
            
            if ($detectedGender) {
                if ($isDryRun) {
                    $this->line("🔄 {$user->name} ({$firstName}) → {$detectedGender}");
                } else {
                    $profile->update(['gender' => $detectedGender]);
                    $this->line("✅ {$user->name} → {$detectedGender}");
                }
                $updated++;
            } else {
                $this->line("⚠️  {$user->name} ({$firstName}) → Genre non détecté automatiquement");
                $skipped++;
            }
        }
        
        if ($isDryRun) {
            $this->info("\n📋 Mode simulation - Aucun changement appliqué");
            $this->info("🔄 {$updated} utilisateur(s) seraient mis à jour");
            $this->info("⚠️  {$skipped} utilisateur(s) nécessitent une intervention manuelle");
            $this->info("\n💡 Exécutez sans --dry-run pour appliquer les changements");
        } else {
            $this->info("\n✅ Mise à jour terminée !");
            $this->info("🔄 {$updated} utilisateur(s) mis à jour");
            $this->info("⚠️  {$skipped} utilisateur(s) nécessitent une intervention manuelle");
            
            if ($skipped > 0) {
                $this->warn("\n⚠️  Certains utilisateurs n'ont pas pu être mis à jour automatiquement.");
                $this->warn("Vous devrez définir leur genre manuellement via l'interface d'administration.");
            }
        }
        
        // Afficher un résumé des genres après mise à jour
        $this->showGenderSummary();
    }
    
    private function showGenderSummary()
    {
        $this->info("\n📊 Résumé des genres dans la base de données :");
        
        $maleCount = Profile::where('gender', 'male')->count();
        $femaleCount = Profile::where('gender', 'female')->count();
        $otherCount = Profile::where('gender', 'other')->count();
        $nullCount = Profile::whereNull('gender')->orWhere('gender', '')->count();
        
        $this->table(
            ['Genre', 'Nombre'],
            [
                ['Masculin', $maleCount],
                ['Féminin', $femaleCount],
                ['Autre', $otherCount],
                ['Non défini', $nullCount],
            ]
        );
        
        $total = $maleCount + $femaleCount + $otherCount + $nullCount;
        $defined = $maleCount + $femaleCount;
        $percentage = $total > 0 ? round(($defined / $total) * 100, 1) : 0;
        
        $this->info("🎯 {$percentage}% des utilisateurs ont un genre clairement défini (masculin/féminin)");
        
        if ($nullCount > 0) {
            $this->warn("⚠️  {$nullCount} utilisateur(s) n'ont toujours pas de genre défini");
            $this->info("💡 Conseil : Encouragez ces utilisateurs à compléter leur profil");
        }
    }
}
