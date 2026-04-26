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
        Schema::create('lab_inventory_category', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->uuid('company_id')->nullable()->index('idx_lab_inventory_category_company_id_b2264b9b');
            $table->uuid('inventory_location_id')->nullable()->index('idx_lab_inventory_category_inventory_location_id_2701b756');
            $table->uuid('inventory_category_id')->nullable()->index('idx_lab_inventory_category_inventory_category_id_93348138');
            $table->boolean('active')->default(true);
            $table->foreign(['company_id'], 'fk_lab_inventory_category_company_id_977b77fc')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_category_id'], 'fk_lab_inventory_category_inventory_category_id_cec2b38e')->references(['id'])->on('inventory_categories')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_lab_inventory_category_inventory_location_id_fc5e27a5')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_inventory_category');
    }
};
