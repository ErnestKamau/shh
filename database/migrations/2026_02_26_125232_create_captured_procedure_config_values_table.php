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
            $table->id();
            // captured_results.id is a signed BIGINT, so we must match its type
            $table->bigInteger('captured_result_id');
            $table->unsignedBigInteger('procedure_worksheet_id');
            $table->unsignedBigInteger('procedure_config_field_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('captured_result_id')
                ->references('id')
                ->on('captured_results')
                ->onDelete('cascade');

            $table->foreign('procedure_worksheet_id')
                ->references('id')
                ->on('procedure_worksheets')
                ->onDelete('cascade');

            $table->foreign('procedure_config_field_id')
                ->references('id')
                ->on('procedure_config_fields')
                ->onDelete('cascade');
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
