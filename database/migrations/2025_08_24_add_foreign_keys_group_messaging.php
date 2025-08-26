<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ajouter la clé étrangère pour last_message_id après que la table messages soit modifiée
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'last_message_id') && !$this->foreignKeyExists('conversations', 'conversations_last_message_id_foreign')) {
                $table->foreign('last_message_id')->references('id')->on('messages')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if ($this->foreignKeyExists('conversations', 'conversations_last_message_id_foreign')) {
                $table->dropForeign(['last_message_id']);
            }
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $schema = Schema::getConnection()->getDoctrineSchemaManager();
        $foreignKeys = $schema->listTableForeignKeys($table);
        
        foreach ($foreignKeys as $key) {
            if ($key->getName() === $foreignKey) {
                return true;
            }
        }
        
        return false;
    }
};
