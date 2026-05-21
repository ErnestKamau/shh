<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grouped_worksheet_holders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('document_control_no')->nullable();
            $table->string('revision')->nullable();
            $table->date('issue_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hybrid_worksheets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('document_control_no')->nullable();
            $table->string('revision')->nullable();
            $table->date('issue_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hybrid_worksheet_versions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hybrid_worksheet_id')->index();
            $table->unsignedInteger('version_number');
            $table->boolean('is_active')->default(false);
            $table->uuid('created_by')->nullable()->index();
            $table->uuid('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hybrid_worksheet_id', 'is_active']);
            $table->unique(['hybrid_worksheet_id', 'version_number', 'deleted_at']);
        });

        Schema::create('grouped_worksheet_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grouped_worksheet_holder_id')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('item_type', 32);
            $table->uuid('reference_id');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['grouped_worksheet_holder_id', 'sort_order']);
        });

        Schema::create('hybrid_worksheet_blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hybrid_worksheet_version_id')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label')->nullable();
            $table->string('block_type', 32);
            $table->uuid('reference_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['hybrid_worksheet_version_id', 'sort_order']);
        });

        Schema::create('hybrid_formula_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hybrid_worksheet_block_id')->index();
            $table->unsignedInteger('step_number')->default(1);
            $table->string('variable_name');
            $table->string('step_type', 32);
            $table->text('expression')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->json('lookup_config')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hybrid_procedure_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hybrid_worksheet_block_id')->index();
            $table->string('step');
            $table->unsignedInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('value_type', 20)->default('text');
            $table->text('default_value')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hybrid_sequence_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('hybrid_worksheet_block_id')->index();
            $table->string('name');
            $table->unsignedInteger('order')->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_result_stage')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('grouped_worksheet_runs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sample_header_id')->index();
            $table->uuid('grouped_worksheet_holder_id')->index();
            $table->unsignedInteger('current_item_index')->default(0);
            $table->string('status', 32)->default('in_progress');
            $table->uuid('started_by')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['sample_header_id', 'grouped_worksheet_holder_id'], 'grouped_worksheet_runs_batch_holder_unique');
        });

        Schema::create('grouped_worksheet_run_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('grouped_worksheet_run_id')->index();
            $table->uuid('grouped_worksheet_item_id')->index();
            $table->string('status', 32)->default('pending');
            $table->uuid('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['grouped_worksheet_run_id', 'grouped_worksheet_item_id'], 'grouped_worksheet_run_items_unique');
        });

        if (Schema::hasTable('analysis_types') && ! Schema::hasColumn('analysis_types', 'grouped_worksheet_holder_id')) {
            Schema::table('analysis_types', function (Blueprint $table) {
                $table->uuid('grouped_worksheet_holder_id')->nullable()->after('procedure_worksheet_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('analysis_types') && Schema::hasColumn('analysis_types', 'grouped_worksheet_holder_id')) {
            Schema::table('analysis_types', function (Blueprint $table) {
                $table->dropColumn('grouped_worksheet_holder_id');
            });
        }

        Schema::dropIfExists('grouped_worksheet_run_items');
        Schema::dropIfExists('grouped_worksheet_runs');
        Schema::dropIfExists('hybrid_sequence_stages');
        Schema::dropIfExists('hybrid_procedure_steps');
        Schema::dropIfExists('hybrid_formula_steps');
        Schema::dropIfExists('hybrid_worksheet_blocks');
        Schema::dropIfExists('grouped_worksheet_items');
        Schema::dropIfExists('hybrid_worksheet_versions');
        Schema::dropIfExists('hybrid_worksheets');
        Schema::dropIfExists('grouped_worksheet_holders');
    }
};
