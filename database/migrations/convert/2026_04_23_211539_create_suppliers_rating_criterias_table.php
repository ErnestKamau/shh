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
        Schema::create('suppliers_rating_criterias', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_suppliers_rating_criterias_supplier_id_a6726f2f');
            $table->integer('criteria_id');
            $table->double('score');
            $table->integer('rating_by');
            $table->boolean('is_current')->default(false);
            $table->timestamps();
            $table->foreign(['supplier_id'], 'fk_suppliers_rating_criterias_supplier_id_750c29e8')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers_rating_criterias');
    }
};
