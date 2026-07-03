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
        if (Schema::hasTable('procedure_config_fields')) {
            return;
        }
        Schema::create('procedure_config_fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_config_fields_procedure_worksheet_id_6c21b894');
            $table->string('label');
            $table->enum('field_type', ['input', 'datetime', 'date', 'number', 'checkbox', 'textarea', 'dataset', 'dataset_multiselect']);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('field_value_name');
            $table->timestamps();
            $table->softDeletes();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_config_fields');
    }
};
