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
        Schema::table('relationship_requests', function (Blueprint $table) {
            $table->dropColumn('inverse_relationship_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relationship_requests', function (Blueprint $table) {
            $table->string('inverse_relationship_name')->nullable()->after('inverse_relationship_type_id');
        });
    }
};
