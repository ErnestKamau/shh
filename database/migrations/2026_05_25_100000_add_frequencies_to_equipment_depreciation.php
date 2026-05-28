<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_depreciation_configs', function (Blueprint $table) {
            $table->json('frequencies')->nullable()->after('frequency');
        });

        Schema::table('depreciation_schedules', function (Blueprint $table) {
            $table->string('frequency', 20)->nullable()->after('equipment_id');
            $table->index(['equipment_id', 'frequency']);
        });

        Schema::table('depreciation_ledger_entries', function (Blueprint $table) {
            $table->string('frequency', 20)->nullable()->after('method_code');
        });

        foreach (DB::table('equipment_depreciation_configs')->select('id', 'frequency')->get() as $row) {
            $frequency = $row->frequency ?? 'monthly';
            DB::table('equipment_depreciation_configs')
                ->where('id', $row->id)
                ->update([
                    'frequencies' => json_encode([$frequency]),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('depreciation_ledger_entries', function (Blueprint $table) {
            $table->dropColumn('frequency');
        });

        Schema::table('depreciation_schedules', function (Blueprint $table) {
            $table->dropIndex(['equipment_id', 'frequency']);
            $table->dropColumn('frequency');
        });

        Schema::table('equipment_depreciation_configs', function (Blueprint $table) {
            $table->dropColumn('frequencies');
        });
    }
};
