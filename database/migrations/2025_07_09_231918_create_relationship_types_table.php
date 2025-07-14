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
            $table->string('code')->unique(); // 'father', 'mother', 'son', etc.
            $table->string('name_fr'); // Nom en français
            $table->string('name_ar'); // Nom en arabe
            $table->string('name_en'); // Nom en anglais
            $table->enum('gender', ['male', 'female', 'both']); // Genre applicable
            $table->boolean('requires_mother_name')->default(false); // Nécessite le nom de la mère
            $table->timestamps();
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
