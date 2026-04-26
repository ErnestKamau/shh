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
            $table->uuid('id');
            $table->uuid('procedure_worksheet_id')->index('procedure_test_kit_columns_procedure_worksheet_id_foreign');
            $table->string('label');
            $table->string('key');
            $table->enum('type', ['string', 'number', 'date', 'boolean'])->default('string');
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->timestamps();
            $table->foreign(['procedure_worksheet_id'], 'fk_procedure_test_kit_columns_procedure_worksheet_id_ff38cbf1')->references(['id'])->on('procedure_worksheets')->onUpdate('no action')->onDelete('cascade');
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
