<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('captured_results', 'run_id')) {
            Schema::table('captured_results', function (Blueprint $table) {
                $table->uuid('run_id')->nullable()->after('stage_header_id');
                $table->foreign('run_id')->references('id')->on('stage_header_runs')->nullOnDelete();
            });
        }

        if (! Schema::hasColumn('analysis_elements', 'stage_header_id')) {
            Schema::table('analysis_elements', function (Blueprint $table) {
                $table->uuid('stage_header_id')->nullable()->after('method_sequence_id');
                $table->foreign('stage_header_id')->references('id')->on('stage_headers')->nullOnDelete();
            });
        }

        Schema::table('test_stages', function (Blueprint $table) {
            if (! Schema::hasColumn('test_stages', 'end_if_pass')) {
                $table->boolean('end_if_pass')->default(false)->after('is_result_stage');
            }
            if (! Schema::hasColumn('test_stages', 'is_end_stage')) {
                $table->boolean('is_end_stage')->default(false)->after('end_if_pass');
            }
            if (! Schema::hasColumn('test_stages', 'end_if_fail')) {
                $table->boolean('end_if_fail')->default(false)->after('is_end_stage');
            }
            if (! Schema::hasColumn('test_stages', 'diluents_required')) {
                $table->json('diluents_required')->nullable()->after('controls_required');
            }
            if (! Schema::hasColumn('test_stages', 'safe_duration')) {
                $table->integer('safe_duration')->nullable()->after('duration_hours');
            }
        });

    }

    public function down(): void
    {
        Schema::table('test_stages', function (Blueprint $table) {
            $columns = ['end_if_pass', 'is_end_stage', 'end_if_fail', 'diluents_required', 'safe_duration'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('test_stages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        if (Schema::hasColumn('analysis_elements', 'stage_header_id')) {
            Schema::table('analysis_elements', function (Blueprint $table) {
                $table->dropForeign(['stage_header_id']);
                $table->dropColumn('stage_header_id');
            });
        }

        if (Schema::hasColumn('captured_results', 'run_id')) {
            Schema::table('captured_results', function (Blueprint $table) {
                $table->dropForeign(['run_id']);
                $table->dropColumn('run_id');
            });
        }
    }
};
