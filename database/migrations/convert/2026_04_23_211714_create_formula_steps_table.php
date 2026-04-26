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
        Schema::create('formula_steps', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('formula_version_id');
            $table->integer('step_number');
            $table->string('variable_name');
            $table->enum('step_type', ['input', 'derived', 'lookup', 'parameter_result'])->index('idx_formula_steps_step_type_e124661f');
            $table->text('expression')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->longText('lookup_config')->nullable();
            $table->uuid('analyte_id')->nullable()->index('formula_steps_analyte_id_foreign');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['formula_version_id', 'step_number'], 'idx_formula_steps_formula_version_id_step_number_9d41f035');
            $table->unique(['formula_version_id', 'variable_name', 'deleted_at']);
            $table->foreign(['analyte_id'], 'fk_formula_steps_analyte_id_0eee2df8')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['formula_version_id'], 'fk_formula_steps_formula_version_id_1fd80d95')->references(['id'])->on('formula_versions')->onUpdate('no action')->onDelete('cascade');
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
