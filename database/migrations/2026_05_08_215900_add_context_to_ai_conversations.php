<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $aiScopedTableExists = DB::table('information_schema.tables')
            ->where('table_schema', 'ai')
            ->where('table_name', 'ai_conversations')
            ->exists();

        $publicTableExists = DB::table('information_schema.tables')
            ->where('table_schema', 'public')
            ->where('table_name', 'ai_conversations')
            ->exists();

        if ($aiScopedTableExists) {
            DB::statement("ALTER TABLE ai.ai_conversations ADD COLUMN IF NOT EXISTS context VARCHAR(50) NOT NULL DEFAULT 'general'");
            return;
        }

        if ($publicTableExists) {
            DB::statement("ALTER TABLE public.ai_conversations ADD COLUMN IF NOT EXISTS context VARCHAR(50) NOT NULL DEFAULT 'general'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $aiScopedTableExists = DB::table('information_schema.tables')
            ->where('table_schema', 'ai')
            ->where('table_name', 'ai_conversations')
            ->exists();

        $publicTableExists = DB::table('information_schema.tables')
            ->where('table_schema', 'public')
            ->where('table_name', 'ai_conversations')
            ->exists();

        if ($aiScopedTableExists) {
            DB::statement('ALTER TABLE ai.ai_conversations DROP COLUMN IF EXISTS context');
            return;
        }

        if ($publicTableExists) {
            DB::statement('ALTER TABLE public.ai_conversations DROP COLUMN IF EXISTS context');
        }
    }
};
