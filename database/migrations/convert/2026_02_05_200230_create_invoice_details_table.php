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
        if (Schema::hasTable('invoice_details')) {
            return;
        }
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('sample_header_id')->index('idx_invoice_details_sample_header_id_90e43167');
            $table->uuid('sample_detail_id')->index('idx_invoice_details_sample_detail_id_dae56b2a');
            $table->uuid('invoice_id')->index('idx_invoice_details_invoice_id');
            $table->uuid('invoicable_item_id')->nullable()->index('idx_invoice_details_invoicable_item_id_836f1c3c');
            $table->double('cost_price');
            $table->double('selling_price');
            $table->double('tax_amount')->default(0);
            $table->string('tax_rate')->default('0');
            $table->double('selling_amount');
            $table->double('total');
            $table->integer('quantity')->default(1);
            $table->string('analysis_type');
            $table->uuid('crm_customer_id')->nullable()->index('idx_invoice_details_crm_customer_id_a6a48e83');
            $table->string('analysis_type_name')->nullable();
            $table->double('selling_price_amount')->nullable()->default(0);
            $table->string('zoho_item_id', 100)->nullable();
            $table->string('zoho_item_name')->nullable();
            $table->string('discount')->nullable();
            $table->double('final_unit_price')->nullable();
            $table->string('discount_type', 100)->nullable();
            $table->text('analysis_title')->nullable();

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
