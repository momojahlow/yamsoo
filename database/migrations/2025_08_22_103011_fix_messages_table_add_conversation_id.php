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
        // Vérifier si la colonne conversation_id existe déjà
        if (!Schema::hasColumn('messages', 'conversation_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('conversation_id')->nullable()->constrained()->onDelete('cascade');
            });
        }

        // Ajouter les colonnes manquantes pour le système de messagerie moderne
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'type')) {
                $table->enum('type', ['text', 'image', 'file', 'audio', 'video'])->default('text')->after('content');
            }
            if (!Schema::hasColumn('messages', 'file_url')) {
                $table->string('file_url')->nullable()->after('type');
            }
            if (!Schema::hasColumn('messages', 'file_name')) {
                $table->string('file_name')->nullable()->after('file_url');
            }
            if (!Schema::hasColumn('messages', 'file_size')) {
                $table->integer('file_size')->nullable()->after('file_name');
            }
            if (!Schema::hasColumn('messages', 'is_edited')) {
                $table->boolean('is_edited')->default(false)->after('file_size');
            }
            if (!Schema::hasColumn('messages', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('is_edited');
            }
            if (!Schema::hasColumn('messages', 'reply_to_id')) {
                $table->foreignId('reply_to_id')->nullable()->constrained('messages')->onDelete('set null')->after('edited_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'reply_to_id')) {
                $table->dropForeign(['reply_to_id']);
                $table->dropColumn('reply_to_id');
            }
            if (Schema::hasColumn('messages', 'edited_at')) {
                $table->dropColumn('edited_at');
            }
            if (Schema::hasColumn('messages', 'is_edited')) {
                $table->dropColumn('is_edited');
            }
            if (Schema::hasColumn('messages', 'file_size')) {
                $table->dropColumn('file_size');
            }
            if (Schema::hasColumn('messages', 'file_name')) {
                $table->dropColumn('file_name');
            }
            if (Schema::hasColumn('messages', 'file_url')) {
                $table->dropColumn('file_url');
            }
            if (Schema::hasColumn('messages', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('messages', 'conversation_id')) {
                $table->dropForeign(['conversation_id']);
                $table->dropColumn('conversation_id');
            }
        });
    }
};
