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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('logo')->default('/images/no-logo.png');
            $table->string('email');
            $table->string('phone');
            $table->string('building');
            $table->string('street');
            $table->string('town');
            $table->string('address');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_suppliers_company_id_91900566');
            $table->smallInteger('active')->default(1);
            $table->uuid('inventory_location_id')->nullable()->default(0)->index('idx_suppliers_inventory_location_id_bf4ba6fc');
            $table->string('pin_number', 100)->nullable();
            $table->string('vat_number', 100)->default('');
            $table->string('supplier_code', 100)->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('default_currency', 100)->nullable();
            $table->foreign(['company_id'], 'fk_suppliers_company_id_303eda27')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['inventory_location_id'], 'fk_suppliers_inventory_location_id_08c9d055')->references(['id'])->on('inventory_locations')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
