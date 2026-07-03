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
        if (Schema::hasTable('customer_invoice')) {
            return;
        }
        Schema::create('customer_invoice', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->double('total')->default(0);
            $table->integer('sent_by')->default(0);
            $table->string('invoice_number')->default('INV-0000')->unique();
            $table->uuid('pricelist_id')->index('idx_customer_invoice_pricelist_id_f21ac2cd');
            $table->string('reference_number')->nullable();
            $table->string('upload_url')->nullable();
            $table->uuid('currency_id')->nullable()->index('idx_customer_invoice_currency_id_ee270a8b');
            $table->date('due_date')->nullable();
            $table->uuid('customer_id')->nullable()->index('idx_customer_invoice_customer_id');
            $table->double('total_tax')->nullable()->default(0);
            $table->string('tax_invoice', 100)->nullable();
            $table->string('sales_order_id', 100)->nullable();
            $table->dateTime('zoho_so_confirmed')->nullable();
            $table->uuid('zoho_customer_id')->nullable()->index('idx_customer_invoice_zoho_customer_id_63d1930e');
            $table->text('zoho_response')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->text('delete_reason')->nullable();

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
