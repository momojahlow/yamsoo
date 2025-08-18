<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà
            if (!Schema::hasColumn('profiles', 'language')) {
                // Préférences de langue et localisation
                $table->string('language', 5)->default('fr')->after('bio');
            }
            
            if (!Schema::hasColumn('profiles', 'timezone')) {
                $table->string('timezone', 50)->default('UTC')->after('language');
            }
            
            // Préférences de notifications
            if (!Schema::hasColumn('profiles', 'notifications_email')) {
                $table->boolean('notifications_email')->default(true)->after('timezone');
            }
            
            if (!Schema::hasColumn('profiles', 'notifications_push')) {
                $table->boolean('notifications_push')->default(true)->after('notifications_email');
            }
            
            if (!Schema::hasColumn('profiles', 'notifications_sms')) {
                $table->boolean('notifications_sms')->default(false)->after('notifications_push');
            }
            
            // Préférences de confidentialité
            if (!Schema::hasColumn('profiles', 'privacy_profile')) {
                $table->enum('privacy_profile', ['public', 'friends', 'family', 'private'])->default('friends')->after('notifications_sms');
            }
            
            if (!Schema::hasColumn('profiles', 'privacy_family')) {
                $table->enum('privacy_family', ['public', 'family', 'private'])->default('public')->after('privacy_profile');
            }
            
            // Préférences d'apparence
            if (!Schema::hasColumn('profiles', 'theme')) {
                $table->enum('theme', ['light', 'dark', 'system'])->default('light')->after('privacy_family');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $columnsToCheck = [
                'language',
                'timezone',
                'notifications_email',
                'notifications_push',
                'notifications_sms',
                'privacy_profile',
                'privacy_family',
                'theme'
            ];

            foreach ($columnsToCheck as $column) {
                if (Schema::hasColumn('profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
