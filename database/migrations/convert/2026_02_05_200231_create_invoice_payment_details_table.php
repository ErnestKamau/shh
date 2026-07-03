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
        Schema::create('invoice_payment_details', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->integer('received_by');
            $table->uuid('invoice_id')->nullable()->index('idx_invoice_payment_details_invoice_id');
            $table->string('ref_no')->nullable();
            $table->string('transaction_no')->nullable();
            $table->string('credit_days')->nullable();
            $table->boolean('is_delete')->default(false);
            $table->string('payment_method', 500)->nullable();
            $table->string('amount', 100)->nullable();
            $table->string('vat', 100)->nullable();
            $table->string('balance', 100)->nullable();
            $table->string('contact_person_name', 100)->nullable();
            $table->string('batch_id', 100)->nullable();
            $table->primary(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_payment_details');
    }
};
