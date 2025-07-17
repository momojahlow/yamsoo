<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('relationship_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requester_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('relationship_type_id')->constrained('relationship_types')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('mother_name', 100)->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['requester_id', 'target_user_id']);
            $table->index(['target_user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('relationship_requests');
    }
};