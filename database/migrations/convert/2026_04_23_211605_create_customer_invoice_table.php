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
        Schema::create('customer_invoice', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->double('total')->default(0);
            $table->integer('sent_by')->default(0);
            $table->string('invoice_number')->default('INV-0000')->unique();
            $table->uuid('pricelist_id')->index('idx_customer_invoice_pricelist_id_08386bfb');
            $table->string('reference_number')->nullable();
            $table->string('upload_url')->nullable();
            $table->uuid('currency_id')->default(0)->index('idx_customer_invoice_currency_id_93d4ff84');
            $table->date('due_date')->nullable();
            $table->integer('customer_id')->default(0);
            $table->double('total_tax')->nullable()->default(0);
            $table->string('tax_invoice', 100)->nullable();
            $table->string('sales_order_id', 100)->nullable();
            $table->dateTime('zoho_so_confirmed')->nullable();
            $table->uuid('zoho_customer_id')->nullable()->index('idx_customer_invoice_zoho_customer_id_35c397f2');
            $table->text('zoho_response')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->text('delete_reason')->nullable();
            $table->foreign(['currency_id'], 'fk_customer_invoice_currency_id_0e6e5a88')->references(['id'])->on('currencies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['pricelist_id'], 'fk_customer_invoice_pricelist_id_73d1b366')->references(['id'])->on('pricelists')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['zoho_customer_id'], 'fk_customer_invoice_zoho_customer_id_d1589ead')->references(['id'])->on('zoho_customers')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invoice');
    }
};
