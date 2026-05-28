<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipment') && ! Schema::hasColumn('equipment', 'has_logbook_tracking')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->boolean('has_logbook_tracking')->default(false)->after('requires_daily_log');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('equipment') && Schema::hasColumn('equipment', 'has_logbook_tracking')) {
            Schema::table('equipment', function (Blueprint $table) {
                $table->dropColumn('has_logbook_tracking');
            });
        }
    }
};
