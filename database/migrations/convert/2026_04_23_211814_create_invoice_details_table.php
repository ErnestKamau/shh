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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_header_id')->index('idx_invoice_details_sample_header_id_1866edae');
            $table->uuid('sample_detail_id')->index('idx_invoice_details_sample_detail_id_49169523');
            $table->integer('invoice_id');
            $table->uuid('invoicable_item_id')->nullable()->index('idx_invoice_details_invoicable_item_id_16eecf02');
            $table->double('cost_price');
            $table->double('selling_price');
            $table->double('tax_amount')->default(0);
            $table->string('tax_rate')->default('0');
            $table->double('selling_amount');
            $table->double('total');
            $table->integer('quantity')->default(1);
            $table->string('analysis_type');
            $table->uuid('crm_customer_id')->default(0)->index('idx_invoice_details_crm_customer_id_d8b87e90');
            $table->string('analysis_type_name')->nullable();
            $table->double('selling_price_amount')->nullable()->default(0);
            $table->string('zoho_item_id', 100)->nullable();
            $table->string('zoho_item_name')->nullable();
            $table->string('discount')->nullable();
            $table->double('final_unit_price')->nullable();
            $table->string('discount_type', 100)->nullable();
            $table->text('analysis_title')->nullable();
            $table->foreign(['crm_customer_id'], 'fk_invoice_details_crm_customer_id_61e3be94')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['invoicable_item_id'], 'fk_invoice_details_invoicable_item_id_f01e159c')->references(['id'])->on('invoicable_items')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_detail_id'], 'fk_invoice_details_sample_detail_id_74fc178b')->references(['id'])->on('sample_details')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['sample_header_id'], 'fk_invoice_details_sample_header_id_ab2c8ca5')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');




            $table->primary(['id']);




        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
