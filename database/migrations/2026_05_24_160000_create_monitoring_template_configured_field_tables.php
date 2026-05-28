<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_template_configured_field_sets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id')->index();
            $table->string('name');
            $table->string('placement', 16);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('template_id')
                ->references('id')
                ->on('monitoring_templates')
                ->cascadeOnDelete();
        });

        Schema::create('monitoring_template_configured_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('field_set_id')->index();
            $table->string('label');
            $table->string('field_type', 32);
            $table->unsignedInteger('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(false);
            $table->string('field_value_name');
            $table->timestamps();

            $table->foreign('field_set_id')
                ->references('id')
                ->on('monitoring_template_configured_field_sets')
                ->cascadeOnDelete();

            $table->unique(
                ['field_set_id', 'field_value_name'],
                'monitoring_template_cfg_fields_set_value_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_template_configured_fields');
        Schema::dropIfExists('monitoring_template_configured_field_sets');
    }
};
