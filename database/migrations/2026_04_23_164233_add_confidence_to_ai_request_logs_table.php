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
        Schema::connection('pgsql_ai')->table('ai.ai_request_logs', function (Blueprint $table) {
            if (!Schema::connection('pgsql_ai')->hasColumn('ai.ai_request_logs', 'confidence')) {
                $table->double('confidence')->default(0.0)->after('latency_ms');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql_ai')->table('ai.ai_request_logs', function (Blueprint $table) {
            if (Schema::connection('pgsql_ai')->hasColumn('ai.ai_request_logs', 'confidence')) {
                $table->dropColumn('confidence');
            }
        });
    }
};
