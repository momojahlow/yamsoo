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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['user', 'moderator', 'admin', 'super_admin'])
                  ->default('user')
                  ->after('email');
            $table->timestamp('role_assigned_at')->nullable()->after('role');
            $table->unsignedBigInteger('role_assigned_by')->nullable()->after('role_assigned_at');
            $table->boolean('is_active')->default(true)->after('role_assigned_by');
            $table->timestamp('last_login_at')->nullable()->after('last_seen_at');
            $table->string('last_login_ip')->nullable()->after('last_login_at');
            
            $table->foreign('role_assigned_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_assigned_by']);
            $table->dropIndex(['role', 'is_active']);
            $table->dropColumn([
                'role',
                'role_assigned_at',
                'role_assigned_by',
                'is_active',
                'last_login_at',
                'last_login_ip'
            ]);
        });
    }
};
