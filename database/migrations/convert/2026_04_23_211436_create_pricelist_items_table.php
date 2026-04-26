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
        Schema::create('pricelist_items', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('pricelist_id')->index('idx_pricelist_items_pricelist_id_9278d0e4');
            $table->integer('analysis_id');
            $table->uuid('sample_type_id')->index('idx_pricelist_items_sample_type_id_3ea83c02');
            $table->float('cost_price');
            $table->float('selling_price');
            $table->float('changed_price');
            $table->boolean('vat');
            $table->boolean('internal_use');
            $table->boolean('external_view');
            $table->boolean('active');
            $table->timestamps(6);
            $table->integer('level')->nullable()->default(0);
            $table->foreign(['pricelist_id'], 'fk_pricelist_items_pricelist_id_62f2ba68')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_type_id'], 'fk_pricelist_items_sample_type_id_7c867377')->references(['id'])->on('sample_types')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricelist_items');
    }
};
