<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('formula_steps')) {
            return;
        }
        Schema::create('formula_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('formula_version_id');
            $table->integer('step_number');
            $table->string('variable_name');
            $table->enum('step_type', ['input', 'derived', 'lookup', 'parameter_result'])->index('idx_formula_steps_step_type_3d215c4d');
            $table->text('expression')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->longText('lookup_config')->nullable();
            $table->uuid('analyte_id')->nullable()->index('idx_formula_steps_analyte_id_11902ee7');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['formula_version_id', 'step_number'], 'idx_formula_steps_formula_version_id_step_number_a0a94b63');
            $table->unique(['formula_version_id', 'variable_name', 'deleted_at']);
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_steps');
    }
};
