<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier que la table profiles existe
        if (!Schema::hasTable('profiles')) {
            // La table n'existe pas encore, on ne fait rien
            return;
        }

        // Nettoyer d'abord les données existantes
        $this->cleanupExistingData();

        // Modifier la colonne gender pour la rendre obligatoire
        Schema::table('profiles', function (Blueprint $table) {
            // Modifier la colonne gender pour qu'elle ne soit plus nullable
            $table->enum('gender', ['male', 'female'])->nullable(false)->change();

            // Ajouter un index sur gender pour de meilleures performances
            if (!Schema::hasIndex('profiles', 'profiles_gender_index')) {
                $table->index('gender');
            }

            // Ajouter un index sur user_id si pas déjà présent
            if (!Schema::hasIndex('profiles', 'profiles_user_id_index')) {
                $table->index('user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('profiles')) {
            return;
        }

        Schema::table('profiles', function (Blueprint $table) {
            // Remettre gender comme nullable
            $table->enum('gender', ['male', 'female'])->nullable()->change();

            // Supprimer les index ajoutés si ils existent
            if (Schema::hasIndex('profiles', 'profiles_gender_index')) {
                $table->dropIndex(['gender']);
            }
            if (Schema::hasIndex('profiles', 'profiles_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
        });
    }

    /**
     * Nettoyer les données existantes avant de rendre gender obligatoire
     */
    private function cleanupExistingData(): void
    {
        // Supprimer les profils sans gender ou avec gender vide
        DB::table('profiles')
            ->whereNull('gender')
            ->orWhere('gender', '')
            ->delete();

        // Supprimer les profils en double (garder le plus récent par user_id)
        $duplicates = DB::table('profiles')
            ->select('user_id', DB::raw('COUNT(*) as count'))
            ->groupBy('user_id')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $duplicate) {
            // Récupérer tous les profils de cet utilisateur
            $profiles = DB::table('profiles')
                ->where('user_id', $duplicate->user_id)
                ->orderBy('updated_at', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            // Garder le premier (plus récent) et supprimer les autres
            $profilesToDelete = $profiles->skip(1)->pluck('id');

            if ($profilesToDelete->isNotEmpty()) {
                DB::table('profiles')
                    ->whereIn('id', $profilesToDelete)
                    ->delete();
            }
        }

        // Assigner un gender par défaut aux profils qui n'en ont pas
        $profilesWithoutGender = DB::table('profiles')
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->whereNull('profiles.gender')
            ->orWhere('profiles.gender', '')
            ->select('profiles.id', 'profiles.first_name', 'users.name')
            ->get();

        foreach ($profilesWithoutGender as $profile) {
            $gender = $this->guessGender($profile->first_name ?? $profile->name);

            DB::table('profiles')
                ->where('id', $profile->id)
                ->update(['gender' => $gender]);
        }
    }

    /**
     * Deviner le gender basé sur le prénom
     */
    private function guessGender(string $name): string
    {
        $firstName = strtolower(trim(explode(' ', $name)[0]));

        // Prénoms masculins courants
        $maleNames = [
            'ahmed', 'mohamed', 'ali', 'omar', 'hassan', 'ibrahim', 'youssef', 'khalid',
            'pierre', 'jean', 'michel', 'philippe', 'alain', 'bernard', 'christian', 'daniel',
            'david', 'eric', 'francois', 'gerard', 'henri', 'jacques', 'laurent', 'marc'
        ];

        // Prénoms féminins courants
        $femaleNames = [
            'fatima', 'aicha', 'khadija', 'amina', 'zeinab', 'maryam', 'sara', 'nour',
            'marie', 'nathalie', 'isabelle', 'sylvie', 'catherine', 'francoise', 'monique',
            'christine', 'brigitte', 'martine', 'nicole', 'veronique', 'chantal', 'dominique'
        ];

        if (in_array($firstName, $maleNames)) {
            return 'male';
        }

        if (in_array($firstName, $femaleNames)) {
            return 'female';
        }

        // Heuristique simple : terminaisons
        if (str_ends_with($firstName, 'a') || str_ends_with($firstName, 'e')) {
            return 'female';
        }

        // Par défaut, basé sur le hash du nom pour cohérence
        return (crc32($firstName) % 2 === 0) ? 'male' : 'female';
    }
};
