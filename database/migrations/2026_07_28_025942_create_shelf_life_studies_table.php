<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('shelf_life_studies')) {
            Schema::create('shelf_life_studies', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('code')->unique();
                $table->string('title');
                $table->uuid('company_id')->nullable()->index();
                $table->uuid('sample_header_id')->nullable()->index();
                $table->uuid('sample_type_id')->nullable()->index();
                $table->uuid('company_product_id')->nullable()->index();
                $table->uuid('sample_submission_request_id')->nullable()->index();
                $table->uuid('crm_customer_id')->nullable()->index();
                $table->string('batch_lot_no')->nullable();
                $table->date('mfg_date')->nullable();
                $table->string('study_type')->default('real_time'); // real_time | accelerated
                $table->decimal('storage_temp_c', 6, 2)->nullable();
                $table->decimal('storage_rh_percent', 5, 2)->nullable();
                $table->string('storage_condition_label')->nullable();
                $table->unsignedInteger('target_duration_value')->nullable();
                $table->string('target_duration_unit')->default('months'); // days|weeks|months
                $table->date('start_date')->nullable();
                $table->uuid('linked_real_time_study_id')->nullable()->index();
                $table->string('status')->default('draft'); // draft|active|on_hold|completed|aborted
                $table->text('notes')->nullable();
                $table->uuid('created_by')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('shelf_life_study_parameter_specs')) {
            Schema::create('shelf_life_study_parameter_specs', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('shelf_life_study_id')->index();
                $table->uuid('analyte_id')->index();
                $table->uuid('method_id')->nullable()->index();
                $table->uuid('reporting_unit_id')->nullable()->index();
                $table->string('parameter_label');
                $table->string('spec_type')->default('range'); // range|max|min|delta_from_baseline|panel_score_max
                $table->decimal('spec_low', 18, 6)->nullable();
                $table->decimal('spec_high', 18, 6)->nullable();
                $table->decimal('safety_margin_percent', 5, 2)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();

                $table->foreign('shelf_life_study_id')
                    ->references('id')
                    ->on('shelf_life_studies')
                    ->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('shelf_life_pull_points')) {
            Schema::create('shelf_life_pull_points', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('shelf_life_study_id')->index();
                $table->string('label');
                $table->unsignedInteger('offset_value')->default(0);
                $table->string('offset_unit')->default('months'); // days|weeks|months
                $table->boolean('is_baseline')->default(false);
                $table->date('scheduled_date')->nullable();
                $table->date('actual_pull_date')->nullable();
                $table->string('status')->default('pending'); // pending|due|tested|passed|failed|skipped
                $table->unsignedInteger('sort_order')->default(0);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('shelf_life_study_id')
                    ->references('id')
                    ->on('shelf_life_studies')
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('sample_headers') && ! Schema::hasColumn('sample_headers', 'is_shelf_life')) {
            Schema::table('sample_headers', function (Blueprint $table) {
                $table->boolean('is_shelf_life')->default(false)->index();
            });
        }

        if (Schema::hasTable('analysis_acceptance_forms') && ! Schema::hasColumn('analysis_acceptance_forms', 'is_shelf_life')) {
            Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
                $table->boolean('is_shelf_life')->default(false);
            });
        }

        if (Schema::hasTable('captured_results') && ! Schema::hasColumn('captured_results', 'shelf_life_pull_point_id')) {
            Schema::table('captured_results', function (Blueprint $table) {
                $table->uuid('shelf_life_pull_point_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('captured_results') && Schema::hasColumn('captured_results', 'shelf_life_pull_point_id')) {
            Schema::table('captured_results', function (Blueprint $table) {
                $table->dropColumn('shelf_life_pull_point_id');
            });
        }

        if (Schema::hasTable('analysis_acceptance_forms') && Schema::hasColumn('analysis_acceptance_forms', 'is_shelf_life')) {
            Schema::table('analysis_acceptance_forms', function (Blueprint $table) {
                $table->dropColumn('is_shelf_life');
            });
        }

        if (Schema::hasTable('sample_headers') && Schema::hasColumn('sample_headers', 'is_shelf_life')) {
            Schema::table('sample_headers', function (Blueprint $table) {
                $table->dropColumn('is_shelf_life');
            });
        }

        Schema::dropIfExists('shelf_life_pull_points');
        Schema::dropIfExists('shelf_life_study_parameter_specs');
        Schema::dropIfExists('shelf_life_studies');
    }
};
