<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Corriger les dates nulles dans les conversations
        DB::table('conversations')
            ->whereNull('last_message_at')
            ->update(['last_message_at' => now()]);
            
        // Corriger les dates nulles dans les messages
        DB::table('messages')
            ->whereNull('created_at')
            ->update(['created_at' => now()]);
            
        DB::table('messages')
            ->whereNull('updated_at')
            ->update(['updated_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pas de rollback nécessaire
    }
};
