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
        // Supprimer complètement l'ancienne table et recréer avec la nouvelle structure
        Schema::dropIfExists('relationship_types');
        
        Schema::create('relationship_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nouveau nom pour l'ancien 'code'
            $table->string('display_name_fr'); // Nouveau nom pour l'ancien 'name_fr'
            $table->string('display_name_ar'); // Nouveau nom pour l'ancien 'name_ar'
            $table->string('display_name_en'); // Nouveau nom pour l'ancien 'name_en'
            $table->text('description')->nullable();
            $table->string('reverse_relationship')->nullable();
            $table->string('category')->default('direct');
            $table->integer('generation_level')->default(0);
            $table->integer('sort_order')->default(1);
            $table->timestamps();
            
            // Index pour améliorer les performances
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
        // Recréer l'ancienne structure si nécessaire
        Schema::dropIfExists('relationship_types');
        
        Schema::create('relationship_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name_fr');
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('gender', ['male', 'female', 'both']);
            $table->boolean('requires_mother_name')->default(false);
            $table->timestamps();
        });
    }
};
