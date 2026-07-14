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
        if (! Schema::hasTable('results') || ! Schema::hasColumn('results', 'captured_result_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("
                DELETE FROM results a
                USING results b
                WHERE a.captured_result_id = b.captured_result_id
                  AND a.captured_result_id IS NOT NULL
                  AND (
                    a.updated_at < b.updated_at
                    OR (a.updated_at = b.updated_at AND a.id::text < b.id::text)
                  )
            ");
        } else {
            $duplicateIds = DB::table('results')
                ->select('captured_result_id')
                ->whereNotNull('captured_result_id')
                ->groupBy('captured_result_id')
                ->havingRaw('COUNT(*) > 1')
                ->pluck('captured_result_id');

            foreach ($duplicateIds as $capturedResultId) {
                $keepId = DB::table('results')
                    ->where('captured_result_id', $capturedResultId)
                    ->orderByDesc('updated_at')
                    ->orderByDesc('id')
                    ->value('id');

                if ($keepId) {
                    DB::table('results')
                        ->where('captured_result_id', $capturedResultId)
                        ->where('id', '!=', $keepId)
                        ->delete();
                }
            }
        }

        try {
            Schema::table('results', function (Blueprint $table) {
                $table->unique('captured_result_id', 'results_captured_result_id_unique');
            });
        } catch (\Throwable) {
            // Unique index may already exist.
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('results')) {
            return;
        }

        try {
            Schema::table('results', function (Blueprint $table) {
                $table->dropUnique('results_captured_result_id_unique');
            });
        } catch (\Throwable) {
            // Index may not exist.
        }
    }
};
