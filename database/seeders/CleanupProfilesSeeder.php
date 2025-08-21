<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CleanupProfilesSeeder extends Seeder
{
    /**
     * Nettoyer les profils en double et s'assurer que gender est obligatoire
     */
    public function run(): void
    {
        $this->command->info('🧹 Nettoyage des profils en double...');

        // 1. Supprimer les profils sans gender (car gender est obligatoire)
        $profilesWithoutGender = Profile::whereNull('gender')->orWhere('gender', '')->get();

        if ($profilesWithoutGender->count() > 0) {
            $this->command->info("Suppression de {$profilesWithoutGender->count()} profils sans gender...");

            foreach ($profilesWithoutGender as $profile) {
                $userName = $profile->user ? $profile->user->name : 'N/A';
                $this->command->info("- Suppression profil ID {$profile->id} (user: {$userName})");
                $profile->delete();
            }
        }

        // 2. Identifier et supprimer les profils en double par user_id
        $duplicateProfiles = DB::table('profiles')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateProfiles->count() > 0) {
            $this->command->info("Traitement de {$duplicateProfiles->count()} utilisateurs avec profils multiples...");

            foreach ($duplicateProfiles as $duplicate) {
                $profiles = Profile::where('user_id', $duplicate->user_id)
                    ->orderBy('updated_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                // Garder le plus récent (premier dans la liste)
                $keepProfile = $profiles->first();
                $profilesToDelete = $profiles->skip(1);

                $this->command->info("User ID {$duplicate->user_id}: Garder profil ID {$keepProfile->id}, supprimer " . $profilesToDelete->count() . " doublons");

                foreach ($profilesToDelete as $profileToDelete) {
                    $profileToDelete->delete();
                }
            }
        }

        // 3. S'assurer que tous les utilisateurs ont un profil avec gender
        $usersWithoutProfile = User::doesntHave('profile')->get();

        if ($usersWithoutProfile->count() > 0) {
            $this->command->info("Création de profils manquants pour {$usersWithoutProfile->count()} utilisateurs...");

            foreach ($usersWithoutProfile as $user) {
                // Créer un profil basique avec gender obligatoire
                $profile = Profile::create([
                    'user_id' => $user->id,
                    'first_name' => $this->extractFirstName($user->name),
                    'last_name' => $this->extractLastName($user->name),
                    'gender' => $this->guessGender($user->name), // Gender obligatoire
                    'language' => 'fr',
                    'timezone' => 'UTC',
                    'notifications_email' => true,
                    'notifications_push' => true,
                    'notifications_sms' => false,
                    'privacy_profile' => 'friends',
                    'privacy_family' => 'public',
                    'theme' => 'light',
                ]);

                $this->command->info("- Profil créé pour {$user->name} (gender: {$profile->gender})");
            }
        }

        // 4. Vérifier les profils avec gender vide et les corriger
        $profilesWithEmptyGender = Profile::where('gender', '')->get();

        if ($profilesWithEmptyGender->count() > 0) {
            $this->command->info("Correction de {$profilesWithEmptyGender->count()} profils avec gender vide...");

            foreach ($profilesWithEmptyGender as $profile) {
                $guessedGender = $this->guessGender($profile->first_name ?? $profile->user->name);
                $profile->update(['gender' => $guessedGender]);

                $this->command->info("- Profil ID {$profile->id}: gender défini à '{$guessedGender}'");
            }
        }

        $this->command->info('✅ Nettoyage des profils terminé !');
        $this->showProfileStats();
    }

    /**
     * Extraire le prénom du nom complet
     */
    private function extractFirstName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return $parts[0] ?? 'Prénom';
    }

    /**
     * Extraire le nom de famille du nom complet
     */
    private function extractLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : 'Nom';
    }

    /**
     * Deviner le gender basé sur le prénom (simple heuristique)
     */
    private function guessGender(string $name): string
    {
        $firstName = $this->extractFirstName($name);
        $firstName = strtolower($firstName);

        // Prénoms masculins courants
        $maleNames = [
            'ahmed', 'mohamed', 'ali', 'omar', 'hassan', 'ibrahim', 'youssef', 'khalid',
            'pierre', 'jean', 'michel', 'philippe', 'alain', 'bernard', 'christian', 'daniel',
            'david', 'eric', 'francois', 'gerard', 'henri', 'jacques', 'laurent', 'marc',
            'nicolas', 'olivier', 'pascal', 'patrick', 'paul', 'robert', 'stephane', 'thierry'
        ];

        // Prénoms féminins courants
        $femaleNames = [
            'fatima', 'aicha', 'khadija', 'amina', 'zeinab', 'maryam', 'sara', 'nour',
            'marie', 'nathalie', 'isabelle', 'sylvie', 'catherine', 'francoise', 'monique',
            'christine', 'brigitte', 'martine', 'nicole', 'veronique', 'chantal', 'dominique',
            'michele', 'annie', 'sandrine', 'valerie', 'corinne', 'karine', 'stephanie'
        ];

        if (in_array($firstName, $maleNames)) {
            return 'male';
        }

        if (in_array($firstName, $femaleNames)) {
            return 'female';
        }

        // Heuristiques simples pour les terminaisons
        if (str_ends_with($firstName, 'a') || str_ends_with($firstName, 'e')) {
            return 'female';
        }

        // Par défaut, assigner aléatoirement mais de façon cohérente
        return (crc32($firstName) % 2 === 0) ? 'male' : 'female';
    }

    /**
     * Afficher les statistiques des profils
     */
    private function showProfileStats(): void
    {
        $totalProfiles = Profile::count();
        $maleProfiles = Profile::where('gender', 'male')->count();
        $femaleProfiles = Profile::where('gender', 'female')->count();
        $usersWithoutProfile = User::doesntHave('profile')->count();

        $this->command->info('📊 Statistiques des profils :');
        $this->command->info("   • Total profils: {$totalProfiles}");
        $this->command->info("   • Hommes: {$maleProfiles}");
        $this->command->info("   • Femmes: {$femaleProfiles}");
        $this->command->info("   • Utilisateurs sans profil: {$usersWithoutProfile}");
    }
}
