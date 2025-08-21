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
        Schema::create('relationship_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nom technique (ex: 'father', 'mother')
            $table->string('display_name_fr'); // Nom d'affichage en français
            $table->string('display_name_ar'); // Nom d'affichage en arabe
            $table->string('display_name_en'); // Nom d'affichage en anglais
            $table->text('description')->nullable(); // Description de la relation
            $table->enum('category', ['direct', 'extended', 'marriage', 'adoption']); // Catégorie de relation
            $table->integer('generation_level')->default(0); // Niveau générationnel (-2, -1, 0, 1, 2)
            $table->integer('sort_order')->default(0); // Ordre d'affichage
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index('category');
            $table->index('generation_level');
            $table->index('sort_order');
            $table->index(['category', 'generation_level']); // Index composite
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationship_types');
    }
};
