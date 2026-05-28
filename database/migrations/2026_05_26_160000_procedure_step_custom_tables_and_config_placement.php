<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('procedure_worksheets') && ! Schema::hasColumn('procedure_worksheets', 'config_fields_placement')) {
            Schema::table('procedure_worksheets', function (Blueprint $table) {
                $table->enum('config_fields_placement', ['top', 'bottom'])->default('top')->after('issue_date');
            });
        }

        if (Schema::hasTable('procedure_worksheet_steps')) {
            Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
                if (! Schema::hasColumn('procedure_worksheet_steps', 'table_mode')) {
                    $table->string('table_mode', 20)->nullable()->after('value_type');
                }
                if (! Schema::hasColumn('procedure_worksheet_steps', 'row_driver')) {
                    $table->string('row_driver', 40)->nullable()->after('table_mode');
                }
                if (! Schema::hasColumn('procedure_worksheet_steps', 'row_driver_filters')) {
                    $table->json('row_driver_filters')->nullable()->after('row_driver');
                }
                if (! Schema::hasColumn('procedure_worksheet_steps', 'allow_manual_rows')) {
                    $table->boolean('allow_manual_rows')->default(false)->after('row_driver_filters');
                }
            });
        }

        if (! Schema::hasTable('procedure_step_table_columns')) {
            Schema::create('procedure_step_table_columns', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('procedure_worksheet_step_id')->index();
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
        }

        if (! Schema::hasTable('procedure_step_table_static_rows')) {
            Schema::create('procedure_step_table_static_rows', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('procedure_worksheet_step_id')->index();
                $table->integer('order')->default(0);
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('procedure_step_table_static_cells')) {
            Schema::create('procedure_step_table_static_cells', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('static_row_id')->index();
                $table->uuid('column_id')->index();
                $table->text('default_value')->nullable();
                $table->timestamps();
                $table->unique(['static_row_id', 'column_id'], 'procedure_step_static_cell_unique');
            });
        }

        if (! Schema::hasTable('sample_procedure_step_table_instances')) {
            Schema::create('sample_procedure_step_table_instances', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('sample_header_id')->index();
                $table->uuid('procedure_worksheet_step_id')->index();
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->unique(
                    ['sample_header_id', 'procedure_worksheet_step_id'],
                    'sample_procedure_step_table_instance_unique'
                );
            });
        }

        if (! Schema::hasTable('sample_procedure_step_table_rows')) {
            Schema::create('sample_procedure_step_table_rows', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('instance_id')->index();
                $table->integer('row_index')->default(0);
                $table->enum('row_source', ['auto', 'manual', 'static'])->default('auto');
                $table->string('driver_type')->nullable();
                $table->uuid('driver_id')->nullable();
                $table->timestamps();
                $table->index(['instance_id', 'row_index']);
            });
        }

        if (! Schema::hasTable('sample_procedure_step_table_cell_values')) {
            Schema::create('sample_procedure_step_table_cell_values', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('row_id')->index();
                $table->uuid('column_id')->index();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['row_id', 'column_id'], 'sample_procedure_step_table_cell_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_procedure_step_table_cell_values');
        Schema::dropIfExists('sample_procedure_step_table_rows');
        Schema::dropIfExists('sample_procedure_step_table_instances');
        Schema::dropIfExists('procedure_step_table_static_cells');
        Schema::dropIfExists('procedure_step_table_static_rows');
        Schema::dropIfExists('procedure_step_table_columns');

        if (Schema::hasTable('procedure_worksheet_steps')) {
            Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
                foreach (['allow_manual_rows', 'row_driver_filters', 'row_driver', 'table_mode'] as $column) {
                    if (Schema::hasColumn('procedure_worksheet_steps', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('procedure_worksheets') && Schema::hasColumn('procedure_worksheets', 'config_fields_placement')) {
            Schema::table('procedure_worksheets', function (Blueprint $table) {
                $table->dropColumn('config_fields_placement');
            });
        }
    }
};
