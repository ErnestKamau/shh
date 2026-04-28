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
        Schema::create('pricelist_customers', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('pricelist_id')->index('idx_pricelist_customers_pricelist_id_b06fe195');
            $table->integer('customer_id');
            $table->timestamps();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricelist_customers');
    }
};
