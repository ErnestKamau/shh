<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('formula_steps')) {
            return;
        }

        $this->expandFormulaStepTypeColumn();

        Schema::table('formula_steps', function (Blueprint $table): void {
            if (! Schema::hasColumn('formula_steps', 'step_config')) {
                $table->json('step_config')->nullable()->after('lookup_config');
            }
            if (! Schema::hasColumn('formula_steps', 'table_mode')) {
                $table->string('table_mode', 20)->nullable()->after('step_config');
            }
            if (! Schema::hasColumn('formula_steps', 'row_driver')) {
                $table->string('row_driver', 40)->nullable()->after('table_mode');
            }
            if (! Schema::hasColumn('formula_steps', 'row_driver_filters')) {
                $table->json('row_driver_filters')->nullable()->after('row_driver');
            }
            if (! Schema::hasColumn('formula_steps', 'allow_manual_rows')) {
                $table->boolean('allow_manual_rows')->default(false)->after('row_driver_filters');
            }
        });

        if (! Schema::hasTable('formula_step_table_columns')) {
            Schema::create('formula_step_table_columns', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('formula_step_id')->index();
                $table->string('label');
                $table->string('key');
                $table->string('column_type', 32)->default('input');
                $table->string('input_data_type', 32)->default('string');
                $table->text('expression')->nullable();
                $table->string('model_tied_to')->nullable();
                $table->json('dataset_config')->nullable();
                $table->integer('order')->default(0);
                $table->boolean('is_required')->default(false);
                $table->text('help_text')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('formula_step_table_static_rows')) {
            Schema::create('formula_step_table_static_rows', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('formula_step_id')->index();
                $table->integer('order')->default(0);
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('formula_step_table_static_cells')) {
            Schema::create('formula_step_table_static_cells', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('static_row_id')->index();
                $table->uuid('column_id')->index();
                $table->text('default_value')->nullable();
                $table->timestamps();
                $table->unique(['static_row_id', 'column_id'], 'formula_step_static_cell_unique');
            });
        }

        if (! Schema::hasTable('sample_formula_step_table_instances')) {
            Schema::create('sample_formula_step_table_instances', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('worksheet_formular_id')->index();
                $table->uuid('formula_step_id')->index();
                $table->string('status')->default('draft');
                $table->timestamps();
                $table->unique(
                    ['worksheet_formular_id', 'formula_step_id'],
                    'sample_formula_step_table_instance_unique'
                );
            });
        }

        if (! Schema::hasTable('sample_formula_step_table_rows')) {
            Schema::create('sample_formula_step_table_rows', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('instance_id')->index();
                $table->integer('row_index')->default(0);
                $table->string('row_source', 20)->default('auto');
                $table->string('driver_type')->nullable();
                $table->uuid('driver_id')->nullable();
                $table->timestamps();
                $table->index(['instance_id', 'row_index']);
            });
        }

        if (! Schema::hasTable('sample_formula_step_table_cell_values')) {
            Schema::create('sample_formula_step_table_cell_values', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('row_id')->index();
                $table->uuid('column_id')->index();
                $table->text('value')->nullable();
                $table->timestamps();
                $table->unique(['row_id', 'column_id'], 'sample_formula_step_table_cell_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_formula_step_table_cell_values');
        Schema::dropIfExists('sample_formula_step_table_rows');
        Schema::dropIfExists('sample_formula_step_table_instances');
        Schema::dropIfExists('formula_step_table_static_cells');
        Schema::dropIfExists('formula_step_table_static_rows');
        Schema::dropIfExists('formula_step_table_columns');

        if (Schema::hasTable('formula_steps')) {
            Schema::table('formula_steps', function (Blueprint $table): void {
                foreach (['allow_manual_rows', 'row_driver_filters', 'row_driver', 'table_mode', 'step_config'] as $column) {
                    if (Schema::hasColumn('formula_steps', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    protected function expandFormulaStepTypeColumn(): void
    {
        if (! Schema::hasColumn('formula_steps', 'step_type')) {
            return;
        }

        if (Schema::hasColumn('formula_steps', 'step_type_new')) {
            $this->completeStepTypeColumnReplacement();

            return;
        }

        Schema::table('formula_steps', function (Blueprint $table): void {
            $table->string('step_type_new', 32)->nullable()->after('variable_name');
        });

        foreach (DB::table('formula_steps')->orderBy('created_at')->cursor() as $step) {
            DB::table('formula_steps')
                ->where('id', $step->id)
                ->update(['step_type_new' => $step->step_type]);
        }

        Schema::table('formula_steps', function (Blueprint $table): void {
            $table->dropColumn('step_type');
        });

        $this->completeStepTypeColumnReplacement();
    }

    protected function completeStepTypeColumnReplacement(): void
    {
        if (! Schema::hasColumn('formula_steps', 'step_type')) {
            Schema::table('formula_steps', function (Blueprint $table): void {
                $table->string('step_type', 32)->default('input')->after('variable_name');
            });
        }

        if (Schema::hasColumn('formula_steps', 'step_type_new')) {
            foreach (DB::table('formula_steps')->whereNotNull('step_type_new')->orderBy('created_at')->cursor() as $step) {
                DB::table('formula_steps')
                    ->where('id', $step->id)
                    ->update(['step_type' => $step->step_type_new]);
            }

            Schema::table('formula_steps', function (Blueprint $table): void {
                $table->dropColumn('step_type_new');
            });
        }

        DB::table('formula_steps')
            ->whereNull('step_type')
            ->update(['step_type' => 'input']);
    }
};
