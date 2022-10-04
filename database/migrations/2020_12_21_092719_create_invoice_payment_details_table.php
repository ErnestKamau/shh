<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('received_by');
            $table->integer('invoice_id');
            $table->integer('batch_id');
            $table->string('ref_no')->nullable();
            $table->string('transaction_no')->nullable();
            $table->string('credit_days')->nullable();
            $table->boolean('is_delete')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_payment_details');
    }
}
