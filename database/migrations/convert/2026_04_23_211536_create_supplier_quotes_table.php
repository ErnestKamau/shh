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
        Schema::create('supplier_quotes', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('supplier_id')->index('idx_supplier_quotes_supplier_id_05ce29d3');
            $table->integer('request_id');
            $table->integer('request_item_id');
            $table->double('quote_amount');
            $table->timestamps();
            $table->dateTime('awarded_at')->nullable();
            $table->boolean('is_awarded')->default(false);
            $table->integer('registered_by')->nullable()->default(0);
            $table->foreign(['supplier_id'], 'fk_supplier_quotes_supplier_id_2b1e0c9a')->references(['id'])->on('suppliers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_quotes');
    }
};
