<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'pgsql_ai';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = $this->getConnection();
        
        // Ensure ai schema exists (redundant but safe)
        DB::connection($connection)->statement('CREATE SCHEMA IF NOT EXISTS ai');

        if (!Schema::connection($connection)->hasTable('ai.ai_settings')) {
            DB::connection($connection)->statement("
                CREATE TABLE ai.ai_settings (
                    key TEXT PRIMARY KEY,
                    value JSONB,
                    description TEXT,
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                )
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->dropIfExists('ai.ai_settings');
    }
};
