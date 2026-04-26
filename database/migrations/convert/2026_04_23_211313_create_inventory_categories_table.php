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
        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('description');
            $table->string('image')->default('no-logo.png');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_inventory_categories_company_id_2b033a34');
            $table->uuid('inventory_location_id')->nullable()->default(0)->index('idx_inventory_categories_inventory_location_id_a268b7f4');
            $table->string('category_type', 100)->default('normal');
            $table->boolean('is_lab')->default(false);
            $table->boolean('active')->default(true);
            $table->foreign(['company_id'], 'fk_inventory_categories_company_id_13e5b591')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_inventory_categories_inventory_location_id_ecf4006b')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_categories');
    }
};
