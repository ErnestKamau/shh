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
        if (!Schema::connection($this->connection)->hasColumn('ai.ai_knowledge_chunks', 'required_permission')) {
            Schema::connection($this->connection)->table('ai.ai_knowledge_chunks', function (Blueprint $table) {
                $table->string('required_permission', 255)->default('General.View')->after('metadata')->index();
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('ai.ai_knowledge_chunks', function (Blueprint $table) {
            $table->dropColumn('required_permission');
        });
    }
};
