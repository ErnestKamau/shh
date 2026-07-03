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
        if (Schema::hasTable('procedure_test_kit_columns')) {
            return;
        }
        Schema::create('procedure_test_kit_columns', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('idx_procedure_test_kit_columns_procedure_worksheet_id_2ccce4f3');
            $table->string('label');
            $table->string('key');
            $table->enum('type', ['string', 'number', 'date', 'boolean'])->default('string');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->timestamps();
            $table->primary(['id']);
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
