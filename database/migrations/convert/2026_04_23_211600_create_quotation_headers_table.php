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
        Schema::create('quotation_headers', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('quote_number')->nullable();
            $table->uuid('crm_customer_id')->nullable()->index('idx_quotation_headers_crm_customer_id_b23f50e1');
            $table->uuid('crm_customer_contact_id')->nullable()->index('idx_quotation_headers_crm_customer_contact_id_b90f1967');
            $table->date('quote_date')->nullable();
            $table->date('expiring_date')->nullable();
            $table->integer('prepared_by_id')->nullable();
            $table->date('email_to_customer')->nullable();
            $table->uuid('pricelist_id')->nullable()->index('idx_quotation_headers_pricelist_id_ff617768');
            $table->boolean('is_draft')->default(false);
            $table->double('total_amount')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->double('sub_total')->nullable();
            $table->double('tax')->nullable();
            $table->boolean('is_print')->default(false);
            $table->string('upload_url', 500)->nullable();
            $table->string('status', 500)->nullable();
            $table->text('price')->nullable();
            $table->text('service_delivery')->nullable();
            $table->text('payments')->nullable();
            $table->text('quote_specification')->nullable();
            $table->text('additional_info')->nullable();
            $table->text('payment_info')->nullable();
            $table->string('payment', 2000)->nullable();
            $table->integer('approved_by')->nullable();
            $table->string('quotation_type')->default('General');
            $table->uuid('currency_id')->nullable()->index('idx_quotation_headers_currency_id_05a0a3ab');
            $table->boolean('is_approved')->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_headers');
    }
};
