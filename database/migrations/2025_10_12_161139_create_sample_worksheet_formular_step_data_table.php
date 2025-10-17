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
        Schema::create('sample_worksheet_formular_step_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worksheet_formular_id');
            $table->unsignedBigInteger('formula_step_id');
            $table->text('step_value')->nullable();
            $table->timestamps();
            
            $table->foreign('worksheet_formular_id', 'fk_wkst_step_formula')
                  ->references('id')
                  ->on('sample_captured_worksheet_formulas')
                  ->onDelete('cascade');
            
            $table->index('worksheet_formular_id', 'idx_wkst_step_formula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_worksheet_formular_step_data');
    }
};
