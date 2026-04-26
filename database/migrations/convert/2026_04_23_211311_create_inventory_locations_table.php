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
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->integer('level')->default(1);
            $table->uuid('inventory_location_id')->default(0)->index('idx_inventory_locations_inventory_location_id_9791b25c');
            $table->timestamps();
            $table->smallInteger('active')->nullable()->default(1);
            $table->uuid('company_id')->nullable()->index('idx_inventory_locations_company_id_be199bf1');
            $table->integer('currency')->nullable();
            $table->foreign(['company_id'], 'fk_inventory_locations_company_id_81a0dcd9')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_inventory_locations_inventory_location_id_e75cbfc6')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_locations');
    }
};
