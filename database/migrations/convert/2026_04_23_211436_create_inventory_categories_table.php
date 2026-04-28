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
            $table->uuid('company_id')->nullable()->index('idx_inventory_categories_company_id_121ed0a9');
            $table->uuid('inventory_location_id')->nullable()->index('idx_inventory_categories_inventory_location_id_2cca91b8');
            $table->string('category_type', 100)->default('normal');
            $table->boolean('is_lab')->default(false);
            $table->boolean('active')->default(true);

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
