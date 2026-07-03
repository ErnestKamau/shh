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
        if (Schema::hasTable('suppliers_categories')) {
            return;
        }
        Schema::create('suppliers_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_suppliers_categories_supplier_id_16abf4cc');
            $table->integer('category_id')->default(0);
            $table->timestamps();
            $table->boolean('status')->default(true);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers_categories');
    }
};
