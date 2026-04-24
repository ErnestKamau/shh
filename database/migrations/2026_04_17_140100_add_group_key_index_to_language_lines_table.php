<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('language_lines')) {
            return;
        }

        if (!$this->indexExists('language_lines', 'language_lines_group_key_index')) {
            Schema::table('language_lines', function (Blueprint $table) {
                $table->index(['group', 'key'], 'language_lines_group_key_index');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('language_lines')) {
            return;
        }

        if ($this->indexExists('language_lines', 'language_lines_group_key_index')) {
            Schema::table('language_lines', function (Blueprint $table) {
                $table->dropIndex('language_lines_group_key_index');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::raw('DATABASE()'))
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};
