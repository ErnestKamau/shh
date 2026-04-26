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
        Schema::create('procedure_config_fields', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('procedure_config_fields_procedure_worksheet_id_foreign');
            $table->string('label');
            $table->enum('field_type', ['input', 'datetime', 'date', 'number', 'checkbox', 'textarea', 'dataset', 'dataset_multiselect']);
            $table->integer('order')->default(0);
            $table->text('help_text')->nullable();
            $table->string('model_tied_to')->nullable();
            $table->boolean('is_required')->default(true);
            $table->string('field_value_name');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_config_fields_procedure_worksheet_id_539f1c54')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
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
