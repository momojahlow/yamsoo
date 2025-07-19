<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Ajouter les nouvelles colonnes
            $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('type', ['text', 'image', 'file', 'audio', 'video'])->default('text');
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null');

            // Ajouter des index
            $table->index(['conversation_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['conversation_id']);
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn([
                'conversation_id',
                'type',
                'file_path',
                'file_name',
                'file_size',
                'is_edited',
                'edited_at',
                'reply_to_id'
            ]);
        });
    }
};
