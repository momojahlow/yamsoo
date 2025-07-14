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
        Schema::create('relationship_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade'); // Qui fait la demande
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade'); // À qui on fait la demande
            $table->foreignId('relationship_type_id')->constrained('relationship_types');
            $table->string('mother_name')->nullable(); // Nom de la mère si requis
            $table->text('message')->nullable(); // Message de la demande
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('relationship_requests');
    }
};
