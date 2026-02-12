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
        Schema::create('captured_procedure_values', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('captured_result_id'); // Removed unsigned to match captured_results.id
            $table->unsignedBigInteger('procedure_worksheet_step_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('captured_result_id')->references('id')->on('captured_results')->onDelete('cascade');
            $table->foreign('procedure_worksheet_step_id')->references('id')->on('procedure_worksheet_steps')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('captured_procedure_values');
    }
};
