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
        Schema::table('complaints', function (Blueprint $table) {
            // First response SLA tracking
            if (!Schema::hasColumn('complaints', 'first_response_at')) {
                $table->timestamp('first_response_at')->nullable()->after('sla_level');
            }
            if (!Schema::hasColumn('complaints', 'first_response_sla_status')) {
                $table->string('first_response_sla_status', 50)->nullable()->after('first_response_at');
            }
            
            // Resolution SLA tracking
            if (!Schema::hasColumn('complaints', 'resolution_sla_status')) {
                $table->string('resolution_sla_status', 50)->nullable()->after('resolved_time');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            if (Schema::hasColumn('complaints', 'first_response_at')) {
                $table->dropColumn('first_response_at');
            }
            if (Schema::hasColumn('complaints', 'first_response_sla_status')) {
                $table->dropColumn('first_response_sla_status');
            }
            if (Schema::hasColumn('complaints', 'resolution_sla_status')) {
                $table->dropColumn('resolution_sla_status');
            }
        });
    }
};
