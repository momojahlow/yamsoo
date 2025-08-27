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
        // Vérifier que la table existe avant de la modifier
        if (!Schema::hasTable('relationship_types')) {
            // La table n'existe pas, on ne fait rien
            return;
        }

        Schema::table('relationship_types', function (Blueprint $table) {
            // Supprimer la colonne reverse_relationship si elle existe
            if (Schema::hasColumn('relationship_types', 'reverse_relationship')) {
                $table->dropColumn('reverse_relationship');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('relationship_types')) {
            return;
        }

        Schema::table('relationship_types', function (Blueprint $table) {
            // Remettre la colonne reverse_relationship si nécessaire
            if (!Schema::hasColumn('relationship_types', 'reverse_relationship')) {
                $table->string('reverse_relationship')->nullable()->after('description');
            }
        });
    }
};
