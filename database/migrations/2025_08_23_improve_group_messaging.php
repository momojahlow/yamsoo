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

            // Nouvelles colonnes pour améliorer la gestion des groupes
            if (!Schema::hasColumn('conversations', 'visibility')) {
                $table->enum('visibility', ['public', 'private', 'invite_only'])->default('private')->after('max_participants');
            }
            if (!Schema::hasColumn('conversations', 'join_approval_required')) {
                $table->boolean('join_approval_required')->default(false)->after('visibility');
            }

            // Optimisation : référence au dernier message pour éviter les JOINs lourds
            if (!Schema::hasColumn('conversations', 'last_message_id')) {
                $table->unsignedBigInteger('last_message_id')->nullable()->after('join_approval_required');
            }
            if (!Schema::hasColumn('conversations', 'last_activity_at')) {
                $table->timestamp('last_activity_at')->nullable()->after('last_message_id');
            }

            // Index pour optimiser les requêtes
            if (!Schema::hasColumn('conversations', 'type')) {
                $table->string('type', 20)->default('private')->after('id');
                $table->index('type');
            }
            $table->index(['type', 'last_activity_at']);
            $table->index('visibility');
        });

        // Améliorer la table conversation_participants
        Schema::table('conversation_participants', function (Blueprint $table) {
            if (!Schema::hasColumn('conversation_participants', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true)->after('is_admin');
            }
            if (!Schema::hasColumn('conversation_participants', 'role')) {
                $table->enum('role', ['member', 'admin', 'owner'])->default('member')->after('is_admin');
            }

            // Nouvelles colonnes pour améliorer la gestion des participants
            if (!Schema::hasColumn('conversation_participants', 'nickname')) {
                $table->string('nickname')->nullable()->after('role'); // Surnom dans le groupe
            }
            if (!Schema::hasColumn('conversation_participants', 'status')) {
                $table->enum('status', ['active', 'invited', 'pending', 'banned'])->default('active')->after('nickname');
            }
            if (!Schema::hasColumn('conversation_participants', 'invited_by')) {
                $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null')->after('status');
            }
            if (!Schema::hasColumn('conversation_participants', 'invitation_sent_at')) {
                $table->timestamp('invitation_sent_at')->nullable()->after('invited_by');
            }

            // Index pour optimiser les requêtes
            $table->index(['conversation_id', 'status']);
            $table->index(['user_id', 'status']);
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

            // Nouvelles colonnes pour le statut de livraison
            if (!Schema::hasColumn('messages', 'delivery_status')) {
                $table->enum('delivery_status', ['sent', 'delivered', 'failed'])->default('sent')->after('pinned_by');
            }
            if (!Schema::hasColumn('messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            }

            // Supprimer read_at car on va utiliser une table pivot pour la multi-lecture
            if (Schema::hasColumn('messages', 'read_at')) {
                $table->dropColumn('read_at');
            }

            // Index pour optimiser les requêtes
            $table->index(['conversation_id', 'delivery_status']);
        });

        // Table pivot pour la multi-lecture des messages (essentiel pour les groupes)
        if (!Schema::hasTable('message_reads')) {
            Schema::create('message_reads', function (Blueprint $table) {
                $table->id();
                $table->foreignId('message_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->timestamp('read_at');
                $table->timestamps();

                $table->unique(['message_id', 'user_id']);
                $table->index(['message_id', 'read_at']);
                $table->index(['user_id', 'read_at']);
            });
        }

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
        Schema::dropIfExists('message_reads');

        Schema::table('messages', function (Blueprint $table) {
            $columns = ['delivery_status', 'delivered_at', 'pinned_by', 'pinned_at', 'is_pinned', 'mentions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('messages', $column)) {
                    if ($column === 'pinned_by') {
                        $table->dropForeign(['pinned_by']);
                    }
                    $table->dropColumn($column);
                }
            }

            // Remettre read_at si elle n'existe pas
            if (!Schema::hasColumn('messages', 'read_at')) {
                $table->timestamp('read_at')->nullable();
            }
        });

        Schema::table('conversation_participants', function (Blueprint $table) {
            $columns = ['invitation_sent_at', 'invited_by', 'status', 'nickname', 'role', 'notifications_enabled'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('conversation_participants', $column)) {
                    if ($column === 'invited_by') {
                        $table->dropForeign(['invited_by']);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('conversations', function (Blueprint $table) {
            $columns = ['last_activity_at', 'last_message_id', 'join_approval_required', 'visibility', 'type', 'max_participants', 'avatar', 'description'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('conversations', $column)) {
                    if ($column === 'last_message_id') {
                        $table->dropForeign(['last_message_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
