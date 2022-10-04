<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationDetailTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_details', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('quotation_header_id');
            $table->integer('analyte_id');
            $table->integer('quantity');
            $table->integer('part_no')->nullable();
            $table->integer('unit_price');
            $table->integer('tax')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('quotation_detail');
    }
}
