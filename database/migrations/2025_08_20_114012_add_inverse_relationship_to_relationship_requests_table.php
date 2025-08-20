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
            $table->unsignedBigInteger('inverse_relationship_type_id')->nullable()->after('relationship_type_id');
            $table->string('inverse_relationship_name')->nullable()->after('inverse_relationship_type_id');

            $table->foreign('inverse_relationship_type_id')->references('id')->on('relationship_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('relationship_requests', function (Blueprint $table) {
            $table->dropForeign(['inverse_relationship_type_id']);
            $table->dropColumn(['inverse_relationship_type_id', 'inverse_relationship_name']);
        });
    }
};
