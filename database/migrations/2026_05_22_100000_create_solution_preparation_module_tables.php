<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lab_sub_category') && ! Schema::hasColumn('lab_sub_category', 'alternative_solution_id')) {
            Schema::table('lab_sub_category', function (Blueprint $table): void {
                $table->uuid('alternative_solution_id')->nullable()->after('batch_status');
                $table->foreign('alternative_solution_id', 'fk_lab_sub_category_alternative_solution')
                    ->references('id')->on('lab_sub_category')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('solution_preparations')) {
            Schema::create('solution_preparations', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('solution_id')->index();
                $table->string('preparation_number')->unique();
                $table->string('batch_number')->nullable();
                $table->boolean('is_new_batch')->default(false);
                $table->uuid('prepared_by')->nullable();
                $table->timestamp('prepared_at')->nullable();
                $table->enum('status', ['preparing', 'awaiting_approval', 'completed', 'cancelled', 'failed'])->default('preparing');
                $table->text('notes')->nullable();
                $table->decimal('quantity_prepared', 12, 4)->nullable();
                $table->uuid('uom_id')->nullable();
                $table->uuid('approved_by')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->text('approval_notes')->nullable();
                $table->json('ingredient_payload')->nullable();
                $table->boolean('alternative_aware')->default(false);
                $table->uuid('source_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('solution_id', 'fk_solution_preparations_solution_id')
                    ->references('id')->on('lab_sub_category')->cascadeOnDelete();
            });
        } else {
            Schema::table('solution_preparations', function (Blueprint $table): void {
                if (! Schema::hasColumn('solution_preparations', 'preparation_number')) {
                    $table->string('preparation_number')->nullable()->unique();
                }
                if (! Schema::hasColumn('solution_preparations', 'is_new_batch')) {
                    $table->boolean('is_new_batch')->default(false);
                }
                if (! Schema::hasColumn('solution_preparations', 'approved_by')) {
                    $table->uuid('approved_by')->nullable();
                    $table->timestamp('approved_at')->nullable();
                    $table->text('approval_notes')->nullable();
                }
                if (! Schema::hasColumn('solution_preparations', 'ingredient_payload')) {
                    $table->json('ingredient_payload')->nullable();
                }
                if (! Schema::hasColumn('solution_preparations', 'alternative_aware')) {
                    $table->boolean('alternative_aware')->default(false);
                }
                if (! Schema::hasColumn('solution_preparations', 'source_id')) {
                    $table->uuid('source_id')->nullable()->index();
                }
            });
        }

        if (! Schema::hasTable('solution_preparation_step_templates')) {
            Schema::create('solution_preparation_step_templates', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('lab_sub_category_id')->index();
                $table->integer('step_number');
                $table->string('step_name');
                $table->string('step_type');
                $table->uuid('ingredient_id')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->uuid('sample_type_id')->nullable();
                $table->uuid('analysis_type_id')->nullable();
                $table->json('selected_analytes')->nullable();
                $table->string('result_type')->nullable();
                $table->json('analyte_result_types')->nullable();
                $table->uuid('standard_id')->nullable();
                $table->timestamps();

                $table->foreign('lab_sub_category_id', 'fk_sp_step_templates_lab_sub_category')
                    ->references('id')->on('lab_sub_category')->cascadeOnDelete();
                $table->foreign('ingredient_id', 'fk_sp_step_templates_ingredient')
                    ->references('id')->on('lab_category_items')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('solution_preparation_step_template_controls')) {
            Schema::create('solution_preparation_step_template_controls', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('template_step_id')->index();
                $table->uuid('control_solution_id');
                $table->string('label')->nullable();
                $table->timestamps();

                $table->foreign('template_step_id', 'fk_sp_template_controls_step')
                    ->references('id')->on('solution_preparation_step_templates')->cascadeOnDelete();
                $table->foreign('control_solution_id', 'fk_sp_template_controls_solution')
                    ->references('id')->on('lab_sub_category')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('preparation_steps')) {
            Schema::create('preparation_steps', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_id')->index();
                $table->integer('step_number');
                $table->string('step_name');
                $table->string('step_type');
                $table->uuid('ingredient_id')->nullable();
                $table->text('description')->nullable();
                $table->text('notes')->nullable();
                $table->uuid('sample_type_id')->nullable();
                $table->uuid('analysis_type_id')->nullable();
                $table->json('selected_analytes')->nullable();
                $table->string('result_type')->nullable();
                $table->json('analyte_result_types')->nullable();
                $table->uuid('standard_id')->nullable();
                $table->decimal('quantity_used', 12, 4)->nullable();
                $table->uuid('uom_id')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->uuid('completed_by')->nullable();
                $table->timestamps();

                $table->foreign('preparation_id', 'fk_preparation_steps_preparation_id')
                    ->references('id')->on('solution_preparations')->cascadeOnDelete();
                $table->foreign('ingredient_id', 'fk_preparation_steps_ingredient')
                    ->references('id')->on('lab_category_items')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('preparation_step_controls')) {
            Schema::create('preparation_step_controls', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_step_id')->index();
                $table->uuid('control_solution_id');
                $table->string('label')->nullable();
                $table->timestamps();

                $table->foreign('preparation_step_id', 'fk_prep_step_controls_step')
                    ->references('id')->on('preparation_steps')->cascadeOnDelete();
                $table->foreign('control_solution_id', 'fk_prep_step_controls_solution')
                    ->references('id')->on('lab_sub_category')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('preparation_step_results')) {
            Schema::create('preparation_step_results', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_step_id')->index();
                $table->uuid('analyte_id')->nullable();
                $table->boolean('is_control')->default(false);
                $table->uuid('control_solution_id')->nullable();
                $table->string('result')->nullable();
                $table->uuid('method_id')->nullable();
                $table->uuid('analyst_id')->nullable();
                $table->string('standard_limit')->nullable();
                $table->string('standard_value')->nullable();
                $table->timestamps();

                $table->foreign('preparation_step_id', 'fk_prep_step_results_step')
                    ->references('id')->on('preparation_steps')->cascadeOnDelete();
                $table->unique(
                    ['preparation_step_id', 'analyte_id', 'is_control', 'control_solution_id'],
                    'prep_step_results_unique'
                );
            });
        }

        if (! Schema::hasTable('preparation_step_media')) {
            Schema::create('preparation_step_media', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_step_id')->index();
                $table->uuid('lab_category_item_id');
                $table->timestamps();

                $table->foreign('preparation_step_id', 'fk_prep_step_media_step')
                    ->references('id')->on('preparation_steps')->cascadeOnDelete();
                $table->foreign('lab_category_item_id', 'fk_prep_step_media_item')
                    ->references('id')->on('lab_category_items')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('preparation_step_diluents')) {
            Schema::create('preparation_step_diluents', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_step_id')->index();
                $table->uuid('lab_category_item_id');
                $table->decimal('amount', 12, 4)->nullable();
                $table->uuid('uom_id')->nullable();
                $table->timestamps();

                $table->foreign('preparation_step_id', 'fk_prep_step_diluents_step')
                    ->references('id')->on('preparation_steps')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('preparation_inoculated_media')) {
            Schema::create('preparation_inoculated_media', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('preparation_id')->index();
                $table->uuid('media_id')->nullable();
                $table->uuid('lab_category_item_id')->nullable();
                $table->string('result')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('preparation_id', 'fk_prep_inoculated_media_preparation')
                    ->references('id')->on('solution_preparations')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('preparation_inoculated_media');
        Schema::dropIfExists('preparation_step_diluents');
        Schema::dropIfExists('preparation_step_media');
        Schema::dropIfExists('preparation_step_results');
        Schema::dropIfExists('preparation_step_controls');
        Schema::dropIfExists('preparation_steps');
        Schema::dropIfExists('solution_preparation_step_template_controls');
        Schema::dropIfExists('solution_preparation_step_templates');

        if (Schema::hasTable('lab_sub_category') && Schema::hasColumn('lab_sub_category', 'alternative_solution_id')) {
            Schema::table('lab_sub_category', function (Blueprint $table): void {
                $table->dropForeign('fk_lab_sub_category_alternative_solution');
                $table->dropColumn('alternative_solution_id');
            });
        }

        Schema::dropIfExists('solution_preparations');
    }
};
