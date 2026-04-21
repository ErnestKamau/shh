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
        if (Schema::connection('pgsql_ai')->hasTable('reporting.sync_runs')) {
            Schema::connection('pgsql_ai')->table('reporting.sync_runs', function (Blueprint $table) {
                if (!Schema::connection('pgsql_ai')->hasColumn('reporting.sync_runs', 'lock_owner')) {
                    $table->string('lock_owner', 255)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('pgsql_ai')->hasTable('reporting.sync_runs')) {
            Schema::connection('pgsql_ai')->table('reporting.sync_runs', function (Blueprint $table) {
                if (Schema::connection('pgsql_ai')->hasColumn('reporting.sync_runs', 'lock_owner')) {
                    $table->dropColumn('lock_owner');
                }
            });
        }
    }
};
