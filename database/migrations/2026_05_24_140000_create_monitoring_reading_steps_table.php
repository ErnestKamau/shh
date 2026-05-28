<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_reading_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->index();
            $table->unsignedInteger('step_number')->default(1);
            $table->string('variable_name');
            $table->string('step_type', 32)->default('input');
            $table->text('expression')->nullable();
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('lookup_config')->nullable();
            $table->uuid('analyte_id')->nullable()->index();
            $table->string('variable_slug')->nullable();
            $table->timestamps();

            $table->foreign('template_id')->references('id')->on('monitoring_templates')->cascadeOnDelete();
            $table->unique(['template_id', 'variable_name'], 'monitoring_reading_steps_template_var_unique');
            $table->unique(['template_id', 'step_number'], 'monitoring_reading_steps_template_step_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_reading_steps');
    }
};
