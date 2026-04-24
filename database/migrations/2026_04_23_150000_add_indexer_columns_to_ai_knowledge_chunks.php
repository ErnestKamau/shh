<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $conn = 'pgsql_ai';
        $tableName = 'ai.ai_knowledge_chunks';

        if (!Schema::connection($conn)->hasTable($tableName)) {
            return;
        }

        Schema::connection($conn)->table($tableName, function (Blueprint $table) use ($conn, $tableName) {
            if (!Schema::connection($conn)->hasColumn($tableName, 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('metadata');
            }

            if (!Schema::connection($conn)->hasColumn($tableName, 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('required_permission');
            }

            if (!Schema::connection($conn)->hasColumn($tableName, 'source_lineage_url')) {
                $table->text('source_lineage_url')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        $conn = 'pgsql_ai';
        $tableName = 'ai.ai_knowledge_chunks';

        if (!Schema::connection($conn)->hasTable($tableName)) {
            return;
        }

        Schema::connection($conn)->table($tableName, function (Blueprint $table) use ($conn, $tableName) {
            $drops = [];

            if (Schema::connection($conn)->hasColumn($tableName, 'company_id')) {
                $drops[] = 'company_id';
            }

            if (Schema::connection($conn)->hasColumn($tableName, 'expires_at')) {
                $drops[] = 'expires_at';
            }

            if (Schema::connection($conn)->hasColumn($tableName, 'source_lineage_url')) {
                $drops[] = 'source_lineage_url';
            }

            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
