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
            $table->uuid('pricelist_id')->index('idx_pricelist_customers_pricelist_id_b3507a9e');
            $table->integer('customer_id');
            $table->timestamps();
            $table->foreign(['pricelist_id'], 'fk_pricelist_customers_pricelist_id_ccbf775c')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');

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
