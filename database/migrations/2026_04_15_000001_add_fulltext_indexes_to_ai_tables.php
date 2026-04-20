<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->indexExists('ai_conversations', 'ai_conversations_title_fulltext')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->fullText('title');
            });
        }

        if (! $this->indexExists('ai_messages', 'ai_messages_content_fulltext')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->fullText('content');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('ai_conversations', 'ai_conversations_title_fulltext')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->dropFullText('ai_conversations_title_fulltext');
            });
        }

        if ($this->indexExists('ai_messages', 'ai_messages_content_fulltext')) {
            Schema::table('ai_messages', function (Blueprint $table) {
                $table->dropFullText('ai_messages_content_fulltext');
            });
        }
    }

    /**
     * Check if a given index exists on the table for the current DB connection.
     */
    private function indexExists(string $tableName, string $indexName): bool
    {
        $database = DB::getDatabaseName();

        $row = DB::selectOne(
            'SELECT COUNT(1) AS count
             FROM information_schema.statistics
             WHERE table_schema = ?
               AND table_name = ?
               AND index_name = ?',
            [$database, $tableName, $indexName]
        );

        return (int) ($row->count ?? 0) > 0;
    }
};
