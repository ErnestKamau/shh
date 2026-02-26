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
        Schema::create('procedure_test_kit_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_worksheet_id')
                ->constrained('procedure_worksheets')
                ->onDelete('cascade');
            $table->string('label');
            $table->string('key');
            $table->enum('type', ['string', 'number', 'date', 'boolean'])->default('string');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('procedure_test_kit_columns');
    }
};
