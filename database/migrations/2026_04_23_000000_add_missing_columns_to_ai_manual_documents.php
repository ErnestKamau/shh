<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql_ai')->table('ai.ai_manual_documents', function (Blueprint $table) {
            if (!Schema::connection('pgsql_ai')->hasColumn('ai.ai_manual_documents', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('required_permission');
            }
            if (!Schema::connection('pgsql_ai')->hasColumn('ai.ai_manual_documents', 'external_source_url')) {
                $table->string('external_source_url')->nullable()->after('expires_at');
            }
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql_ai')->table('ai.ai_manual_documents', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'external_source_url']);
        });
    }
};
