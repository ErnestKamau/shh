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
        Schema::create('captured_procedure_config_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('captured_result_id')->index('idx_captured_procedure_config_values_captured_result_i_1ffb1e7e');
            $table->uuid('procedure_worksheet_id')->index('idx_captured_procedure_config_values_procedure_workshe_c3cb7c72');
            $table->uuid('procedure_config_field_id')->index('idx_captured_procedure_config_values_procedure_config_bed5340a');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->foreign(['captured_result_id'], 'fk_captured_procedure_config_values_captured_result_id_157a0db9')->references(['id'])->on('captured_results')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_config_field_id'], 'fk_captured_procedure_config_values_procedure_config_f_e9d35992')->references(['id'])->on('procedure_config_fields')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['procedure_worksheet_id'], 'fk_captured_procedure_config_values_procedure_workshee_3a3e78c6')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_procedure_config_values');
    }
};
