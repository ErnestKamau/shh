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
        Schema::create('sample_worksheet_formular_mandatory_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worksheet_formular_id');
            $table->unsignedBigInteger('formula_mandatory_field_id');
            $table->text('field_value')->nullable();
            $table->timestamps();
            
            $table->foreign('worksheet_formular_id', 'fk_wkst_mand_formula')
                  ->references('id')
                  ->on('sample_captured_worksheet_formulas')
                  ->onDelete('cascade');
            
            $table->index('worksheet_formular_id', 'idx_wkst_mand_formula');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_worksheet_formular_mandatory_data');
    }
};
