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
        Schema::create('procedure_test_kit_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_worksheet_id')
                ->constrained('procedure_worksheets')
                ->onDelete('cascade');
            $table->integer('row_index')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_test_kit_rows');
    }
};
