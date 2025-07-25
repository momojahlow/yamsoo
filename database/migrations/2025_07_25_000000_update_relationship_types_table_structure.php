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
        Schema::table('relationship_types', function (Blueprint $table) {
            // Renommer les colonnes existantes pour correspondre à la nouvelle structure
            $table->renameColumn('code', 'name');
            $table->renameColumn('name_fr', 'display_name_fr');
            $table->renameColumn('name_ar', 'display_name_ar');
            $table->renameColumn('name_en', 'display_name_en');
        });

        Schema::table('relationship_types', function (Blueprint $table) {
            // Ajouter les nouvelles colonnes
            $table->text('description')->nullable()->after('display_name_en');
            $table->string('reverse_relationship')->nullable()->after('description');
            $table->string('category')->default('direct')->after('reverse_relationship');
            $table->integer('generation_level')->default(0)->after('category');
            $table->integer('sort_order')->default(1)->after('generation_level');
            
            // Supprimer les colonnes qui ne sont plus nécessaires
            $table->dropColumn(['gender', 'requires_mother_name']);
        });

        // Ajouter des index pour améliorer les performances
        Schema::table('relationship_types', function (Blueprint $table) {
            $table->index('category');
            $table->index('generation_level');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relationship_types', function (Blueprint $table) {
            // Supprimer les index
            $table->dropIndex(['category']);
            $table->dropIndex(['generation_level']);
            $table->dropIndex(['sort_order']);
            
            // Supprimer les nouvelles colonnes
            $table->dropColumn([
                'description',
                'reverse_relationship',
                'category',
                'generation_level',
                'sort_order'
            ]);
            
            // Ajouter les anciennes colonnes
            $table->enum('gender', ['male', 'female', 'both'])->after('display_name_en');
            $table->boolean('requires_mother_name')->default(false)->after('gender');
        });

        Schema::table('relationship_types', function (Blueprint $table) {
            // Renommer les colonnes pour revenir à l'ancienne structure
            $table->renameColumn('name', 'code');
            $table->renameColumn('display_name_fr', 'name_fr');
            $table->renameColumn('display_name_ar', 'name_ar');
            $table->renameColumn('display_name_en', 'name_en');
        });
    }
};
