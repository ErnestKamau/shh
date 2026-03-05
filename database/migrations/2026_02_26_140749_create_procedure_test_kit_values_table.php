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
        Schema::create('procedure_test_kit_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('procedure_test_kit_row_id');
            $table->unsignedBigInteger('procedure_test_kit_column_id');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('procedure_test_kit_row_id')
                ->references('id')
                ->on('procedure_test_kit_rows')
                ->onDelete('cascade');

            $table->foreign('procedure_test_kit_column_id')
                ->references('id')
                ->on('procedure_test_kit_columns')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_test_kit_values');
    }
};
