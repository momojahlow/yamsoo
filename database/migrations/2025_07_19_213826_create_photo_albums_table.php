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
        Schema::create('photo_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('cover_photo')->nullable(); // URL de la photo de couverture
            $table->enum('privacy', ['public', 'family', 'private'])->default('family');
            $table->boolean('is_default')->default(false); // Album par défaut de l'utilisateur
            $table->integer('photos_count')->default(0); // Compteur de photos
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['user_id', 'privacy']);
            $table->index(['user_id', 'is_default']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_albums');
    }
};
