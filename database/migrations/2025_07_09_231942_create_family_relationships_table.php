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
        Schema::create('family_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Utilisateur principal
            $table->foreignId('related_user_id')->constrained('users')->onDelete('cascade'); // Utilisateur lié
            $table->foreignId('relationship_type_id')->constrained('relationship_types');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->string('mother_name')->nullable(); // Nom de la mère (requis pour père/fils)
            $table->text('message')->nullable(); // Message de la demande
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // Éviter les relations en double
            $table->unique(['user_id', 'related_user_id', 'relationship_type_id'], 'unique_relationship');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_relationships');
    }
};
