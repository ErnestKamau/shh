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
        Schema::create('analysis_type_invoicable_item', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('analysis_type_id')->index('idx_analysis_type_invoicable_item_analysis_type_id_ea2bbf00');
            $table->uuid('invoicable_item_id')->index('idx_analysis_type_invoicable_item_invoicable_item_id_0cddbff4');
            $table->timestamps();

            $table->unique(['analysis_type_id', 'invoicable_item_id'], 'analysis_invoicable_unique');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_type_invoicable_item');
    }
};
