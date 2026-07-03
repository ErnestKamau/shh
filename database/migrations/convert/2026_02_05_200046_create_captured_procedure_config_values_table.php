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
        if (Schema::hasTable('captured_procedure_config_values')) {
            return;
        }
        Schema::create('captured_procedure_config_values', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('captured_result_id')->index('idx_captured_procedure_config_values_captured_result_i_0a846c1b');
            $table->uuid('procedure_worksheet_id')->index('idx_captured_procedure_config_values_procedure_workshe_9f6a6bc1');
            $table->uuid('procedure_config_field_id')->index('idx_captured_procedure_config_values_procedure_config_6f4a5b43');
            $table->text('value')->nullable();
            $table->timestamps();

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
