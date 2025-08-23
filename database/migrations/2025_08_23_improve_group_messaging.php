<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Améliorer la table conversations pour les groupes
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            if (!Schema::hasColumn('conversations', 'avatar')) {
                $table->string('avatar')->nullable()->after('description');
            }
            if (!Schema::hasColumn('conversations', 'max_participants')) {
                $table->integer('max_participants')->default(256)->after('avatar');
            }
        });

        // Améliorer la table conversation_participants
        Schema::table('conversation_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('conversation_participants', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true)->after('is_admin');
            }
            if (!Schema::hasColumn('conversation_participants', 'role')) {
                $table->enum('role', ['member', 'admin', 'owner'])->default('member')->after('is_admin');
            }
        });

        // Améliorer la table messages pour les groupes
        Schema::table('messages', function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'mentions')) {
                $table->json('mentions')->nullable()->after('content'); // IDs des utilisateurs mentionnés
            }
            if (!Schema::hasColumn('messages', 'is_pinned')) {
                $table->boolean('is_pinned')->default(false)->after('is_edited');
            }
            if (!Schema::hasColumn('messages', 'pinned_at')) {
                $table->timestamp('pinned_at')->nullable()->after('is_pinned');
            }
            if (!Schema::hasColumn('messages', 'pinned_by')) {
                $table->foreignId('pinned_by')->nullable()->constrained('users')->onDelete('set null')->after('pinned_at');
            }
        });

        // Table pour les réactions aux messages (émojis)
        if (!Schema::hasTable('message_reactions')) {
            Schema::create('message_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('emoji', 10); // 👍, ❤️, 😂, etc.
                $table->timestamps();

                $table->unique(['message_id', 'user_id', 'emoji']);
                $table->index(['message_id', 'emoji']);
            });
        }

        // Table pour l'historique des actions de groupe
        if (!Schema::hasTable('conversation_activities')) {
            Schema::create('conversation_activities', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->enum('action', [
                    'created', 'joined', 'left', 'added', 'removed', 
                    'promoted', 'demoted', 'name_changed', 'description_changed',
                    'avatar_changed', 'settings_changed'
                ]);
                $table->json('metadata')->nullable(); // Données supplémentaires selon l'action
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_activities');
        Schema::dropIfExists('message_reactions');
        
        Schema::table('messages', function (Blueprint $table) {
            $columns = ['pinned_by', 'pinned_at', 'is_pinned', 'mentions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('messages', $column)) {
                    if ($column === 'pinned_by') {
                        $table->dropForeign(['pinned_by']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $columns = ['role', 'notifications_enabled'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('conversation_participants', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('conversations', function (Blueprint $table) {
            $columns = ['max_participants', 'avatar', 'description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('conversations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
