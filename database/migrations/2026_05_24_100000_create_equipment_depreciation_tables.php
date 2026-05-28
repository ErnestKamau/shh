<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depreciation_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_rate', 8, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('equipment_depreciation_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id')->unique();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->uuid('depreciation_method_id')->nullable();
            $table->boolean('enable_depreciation')->default(false);
            $table->string('currency', 10)->default('USD');
            $table->decimal('freight_cost', 15, 2)->default(0);
            $table->decimal('capitalized_amount', 15, 2)->default(0);
            $table->date('depreciation_start_date')->nullable();
            $table->unsignedSmallInteger('useful_life_years')->nullable();
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->string('frequency')->default('monthly');
            $table->decimal('depreciation_rate', 8, 4)->nullable();
            $table->string('declining_balance_type')->nullable();
            $table->decimal('expected_total_units', 15, 4)->nullable();
            $table->string('unit_type')->nullable();
            $table->decimal('current_units_used', 15, 4)->default(0);
            $table->string('usage_source')->nullable();
            $table->decimal('initial_book_value', 15, 2)->nullable();
            $table->decimal('current_book_value', 15, 2)->nullable();
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->decimal('current_period_depreciation', 15, 2)->default(0);
            $table->string('status')->default('disabled');
            $table->uuid('active_schedule_version_id')->nullable();
            $table->date('last_processed_period_date')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('depreciation_method_id')->references('id')->on('depreciation_methods')->nullOnDelete();
            $table->index('company_id');
            $table->index('status');
        });

        Schema::create('depreciation_schedule_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_depreciation_config_id');
            $table->unsignedInteger('version_number');
            $table->string('reason')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->uuid('created_by')->nullable();
            $table->timestamps();

            $table->foreign('equipment_depreciation_config_id', 'dsv_config_fk')
                ->references('id')->on('equipment_depreciation_configs')->cascadeOnDelete();
            $table->unique(['equipment_depreciation_config_id', 'version_number'], 'dsv_config_version_unique');
        });

        Schema::create('depreciation_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('depreciation_schedule_version_id');
            $table->uuid('equipment_id');
            $table->string('period_label');
            $table->date('period_date');
            $table->unsignedSmallInteger('period_index')->default(0);
            $table->decimal('opening_book_value', 15, 2);
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('closing_book_value', 15, 2);
            $table->boolean('is_posted')->default(false);
            $table->timestamps();

            $table->foreign('depreciation_schedule_version_id', 'ds_version_fk')
                ->references('id')->on('depreciation_schedule_versions')->cascadeOnDelete();
            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->index(['equipment_id', 'period_date']);
        });

        Schema::create('depreciation_ledger_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->uuid('equipment_depreciation_config_id');
            $table->uuid('depreciation_schedule_version_id')->nullable();
            $table->uuid('depreciation_schedule_id')->nullable();
            $table->string('period_label');
            $table->date('period_date');
            $table->string('method_code')->nullable();
            $table->decimal('opening_book_value', 15, 2);
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->decimal('closing_book_value', 15, 2);
            $table->unsignedInteger('version_number')->default(1);
            $table->uuid('generated_by')->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('equipment_depreciation_config_id', 'dle_config_fk')
                ->references('id')->on('equipment_depreciation_configs')->cascadeOnDelete();
            $table->index(['equipment_id', 'period_date']);
        });

        Schema::create('equipment_appraisals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->uuid('equipment_depreciation_config_id');
            $table->date('appraisal_date');
            $table->decimal('prior_book_value', 15, 2);
            $table->decimal('new_appraised_value', 15, 2);
            $table->unsignedSmallInteger('useful_life_extension_years')->default(0);
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->uuid('created_by')->nullable();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->foreign('equipment_depreciation_config_id', 'ea_config_fk')
                ->references('id')->on('equipment_depreciation_configs')->cascadeOnDelete();
        });

        Schema::create('depreciation_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('equipment_id');
            $table->uuid('equipment_depreciation_config_id')->nullable();
            $table->string('action_type');
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->uuid('user_id')->nullable();
            $table->string('approval_status')->nullable();
            $table->timestamps();

            $table->foreign('equipment_id')->references('id')->on('equipment')->cascadeOnDelete();
            $table->index('equipment_id');
        });

        Schema::table('equipment_depreciation_configs', function (Blueprint $table) {
            $table->foreign('active_schedule_version_id', 'edc_active_version_fk')
                ->references('id')->on('depreciation_schedule_versions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_depreciation_configs', function (Blueprint $table) {
            $table->dropForeign('edc_active_version_fk');
        });

        Schema::dropIfExists('depreciation_audit_logs');
        Schema::dropIfExists('equipment_appraisals');
        Schema::dropIfExists('depreciation_ledger_entries');
        Schema::dropIfExists('depreciation_schedules');
        Schema::dropIfExists('depreciation_schedule_versions');
        Schema::dropIfExists('equipment_depreciation_configs');
        Schema::dropIfExists('depreciation_methods');
    }
};
