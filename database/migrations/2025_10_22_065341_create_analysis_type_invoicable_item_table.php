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
            $table->id();
            $table->integer('analysis_type_id');
            $table->unsignedBigInteger('invoicable_item_id');
            $table->timestamps();
            
            $table->unique(['analysis_type_id', 'invoicable_item_id'], 'analysis_invoicable_unique');
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
