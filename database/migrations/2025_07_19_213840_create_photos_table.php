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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Propriétaire de la photo
            $table->foreignId('photo_album_id')->constrained()->onDelete('cascade'); // Album contenant la photo
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('file_path'); // Chemin vers le fichier photo
            $table->string('file_name'); // Nom original du fichier
            $table->string('mime_type'); // Type MIME (image/jpeg, image/png, etc.)
            $table->integer('file_size'); // Taille du fichier en bytes
            $table->integer('width')->nullable(); // Largeur de l'image
            $table->integer('height')->nullable(); // Hauteur de l'image
            $table->string('thumbnail_path')->nullable(); // Chemin vers la miniature
            $table->json('metadata')->nullable(); // Métadonnées EXIF, etc.
            $table->integer('order')->default(0); // Ordre dans l'album
            $table->timestamp('taken_at')->nullable(); // Date de prise de la photo
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['photo_album_id', 'order']);
            $table->index(['user_id', 'created_at']);
            $table->index('taken_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
