<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_headers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('quote_number');
            $table->integer('crm_customer_id')->nullable();
            $table->integer('crm_customer_contact_id')->nullable();
            $table->date('quote_date')->nullable();
            $table->date('expiring_date')->nullable();
            $table->integer('prepared_by_id')->nullable();
            $table->date('email_to_customer')->nullable();
            $table->integer('pricelist_id')->nullable();
            $table->boolean('is_draft')->default(0);
            $table->float('total_amount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation');
    }
}
