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
            $table->uuid('crm_customer_id')->nullable()->index('idx_quotation_headers_crm_customer_id_8c2b02b4');
            $table->uuid('crm_customer_contact_id')->nullable()->index('idx_quotation_headers_crm_customer_contact_id_e39885d9');
            $table->date('quote_date')->nullable();
            $table->date('expiring_date')->nullable();
            $table->integer('prepared_by_id')->nullable();
            $table->date('email_to_customer')->nullable();
            $table->uuid('pricelist_id')->nullable()->index('idx_quotation_headers_pricelist_id_13de40bb');
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
            $table->uuid('currency_id')->default(14)->index('idx_quotation_headers_currency_id_aca9e287');
            $table->boolean('is_approved')->default(false);
            $table->foreign(['crm_customer_contact_id'], 'fk_quotation_headers_crm_customer_contact_id_ec04541f')->references(['id'])->on('crm_customer_contacts')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['crm_customer_id'], 'fk_quotation_headers_crm_customer_id_7a859b41')->references(['id'])->on('crm_customers')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['currency_id'], 'fk_quotation_headers_currency_id_623c064b')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pricelist_id'], 'fk_quotation_headers_pricelist_id_88831307')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('set null');




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
