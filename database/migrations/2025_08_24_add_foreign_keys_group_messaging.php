<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la clé étrangère pour last_message_id après que la table messages soit modifiée
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'last_message_id')) {
                try {
                    $table->foreign('last_message_id')->references('id')->on('messages')->onDelete('set null');
                } catch (\Exception $e) {
                    // Clé étrangère déjà existante ou autre erreur, on ignore
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            try {
                $table->dropForeign(['last_message_id']);
            } catch (\Exception $e) {
                // Clé étrangère n'existe pas, on ignore
            }
        });
    }
};
