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
        Schema::create('inventory_stores', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->uuid('company_id')->index('idx_inventory_stores_company_id_66f457dd');
            $table->uuid('inventory_location_id')->index('idx_inventory_stores_inventory_location_id_083c7493');
            $table->timestamps();
            $table->string('type_of_store', 100)->default('inventory_store');
            $table->boolean('is_frozen')->default(false);
            $table->foreign(['company_id'], 'fk_inventory_stores_company_id_7d77fd31')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['inventory_location_id'], 'fk_inventory_stores_inventory_location_id_73bf547f')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('cascade');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_stores');
    }
};
