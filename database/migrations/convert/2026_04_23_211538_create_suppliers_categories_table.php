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
        Schema::create('suppliers_categories', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_suppliers_categories_supplier_id_2036867d');
            $table->integer('category_id')->default(0);
            $table->timestamps();
            $table->boolean('status')->default(true);
            $table->foreign(['supplier_id'], 'fk_suppliers_categories_supplier_id_607c2167')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

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
