<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_entry_worksheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('document_control_no')->nullable();
            $table->string('revision')->nullable();
            $table->date('issue_date')->nullable();
            $table->enum('row_driver', ['sample_header', 'sample_detail', 'captured_result', 'method'])->default('captured_result');
            $table->json('row_driver_filters')->nullable();
            $table->enum('mandatory_fields_placement', ['top', 'bottom'])->default('top');
            $table->boolean('allow_manual_rows')->default(true);
            $table->timestamps();
        });

        Schema::create('log_entry_worksheet_columns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_entry_worksheet_id')->index();
            $table->string('label');
            $table->string('key');
            $table->enum('column_type', ['input', 'derived', 'dataset'])->default('input');
            $table->enum('input_data_type', ['string', 'number', 'date', 'boolean', 'textarea'])->default('string');
            $table->text('expression')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->json('dataset_config')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->timestamps();
        });

        Schema::create('log_entry_worksheet_mandatory_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('log_entry_worksheet_id')->index();
            $table->string('label');
            $table->enum('field_type', ['input', 'datetime', 'date', 'dataset_related']);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('field_value_name');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sample_log_entry_worksheet_instances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_header_id')->index();
            $table->uuid('log_entry_worksheet_id')->index();
            $table->string('status')->default('draft');
            $table->timestamps();
            $table->unique(['sample_header_id', 'log_entry_worksheet_id'], 'sample_log_entry_instance_unique');
        });

        Schema::create('sample_log_entry_worksheet_rows', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id')->index();
            $table->integer('row_index')->default(0);
            $table->enum('row_source', ['auto', 'manual'])->default('auto');
            $table->string('driver_type')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->timestamps();
            $table->index(['instance_id', 'row_index']);
        });

        Schema::create('sample_log_entry_worksheet_cell_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('row_id')->index();
            $table->uuid('column_id')->index();
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['row_id', 'column_id'], 'sample_log_entry_cell_unique');
        });

        Schema::create('sample_log_entry_worksheet_mandatory_data', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('instance_id')->index();
            $table->uuid('mandatory_field_id')->index();
            $table->text('field_value')->nullable();
            $table->timestamps();
            $table->unique(['instance_id', 'mandatory_field_id'], 'sample_log_entry_mandatory_unique');
        });

        if (Schema::hasTable('analysis_elements') && ! Schema::hasColumn('analysis_elements', 'log_entry_worksheet_id')) {
            Schema::table('analysis_elements', function (Blueprint $table) {
                $table->uuid('log_entry_worksheet_id')->nullable()->after('procedure_worksheet_id');
            });
        }

        if (Schema::hasTable('captured_results')) {
            Schema::table('captured_results', function (Blueprint $table) {
                if (! Schema::hasColumn('captured_results', 'log_entry_worksheet_id')) {
                    $table->uuid('log_entry_worksheet_id')->nullable()->after('has_hybrid_worksheet');
                }
                if (! Schema::hasColumn('captured_results', 'has_log_entry_worksheet')) {
                    $table->boolean('has_log_entry_worksheet')->default(false)->after('log_entry_worksheet_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('captured_results')) {
            Schema::table('captured_results', function (Blueprint $table) {
                foreach (['has_log_entry_worksheet', 'log_entry_worksheet_id'] as $column) {
                    if (Schema::hasColumn('captured_results', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('analysis_elements') && Schema::hasColumn('analysis_elements', 'log_entry_worksheet_id')) {
            Schema::table('analysis_elements', function (Blueprint $table) {
                $table->dropColumn('log_entry_worksheet_id');
            });
        }

        Schema::dropIfExists('sample_log_entry_worksheet_mandatory_data');
        Schema::dropIfExists('sample_log_entry_worksheet_cell_values');
        Schema::dropIfExists('sample_log_entry_worksheet_rows');
        Schema::dropIfExists('sample_log_entry_worksheet_instances');
        Schema::dropIfExists('log_entry_worksheet_mandatory_fields');
        Schema::dropIfExists('log_entry_worksheet_columns');
        Schema::dropIfExists('log_entry_worksheets');
    }
};
