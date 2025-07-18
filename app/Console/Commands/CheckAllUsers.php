<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CheckAllUsers extends Command
{
    protected $signature = 'check:all-users';
    protected $description = 'Vérifier tous les utilisateurs et leurs genres';

    public function handle()
    {
        $this->info('👥 VÉRIFICATION DE TOUS LES UTILISATEURS');
        $this->info('═══════════════════════════════════════');
        $this->newLine();

        $users = User::with('profile')->get();

        foreach ($users as $user) {
            $profile = $user->profile;
            $gender = $profile?->gender ?? 'NULL';
            $firstName = $profile?->first_name ?? 'N/A';
            $lastName = $profile?->last_name ?? 'N/A';
            
            $genderIcon = match($gender) {
                'male' => '👨',
                'female' => '👩',
                default => '❓'
            };

            $this->line("{$genderIcon} {$user->name} (ID: {$user->id})");
            $this->line("   Email: {$user->email}");
            $this->line("   Prénom: {$firstName}, Nom: {$lastName}");
            $this->line("   Genre: {$gender}");
            $this->newLine();
        }

        // Corriger les genres basés sur les prénoms
        $this->info('🔧 CORRECTION AUTOMATIQUE DES GENRES :');
        
        $corrections = [
            'Fatima' => 'female',
            'Amina' => 'female', 
            'Aicha' => 'female',
            'Leila' => 'female',
            'Ahmed' => 'male',
            'Mohammed' => 'male',
            'Youssef' => 'male',
        ];

        foreach ($users as $user) {
            $firstName = $user->profile?->first_name;
            if ($firstName && isset($corrections[$firstName])) {
                $correctGender = $corrections[$firstName];
                $currentGender = $user->profile?->gender;
                
                if ($currentGender !== $correctGender) {
                    $user->profile->update(['gender' => $correctGender]);
                    $this->line("   ✅ {$user->name} : {$currentGender} → {$correctGender}");
                }
            }
        }

        return 0;
    }
}
