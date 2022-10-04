<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLabCategoryItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lab_category_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('category_id');
            $table->integer('reagent_id');
            $table->string('manufacture')->nullable();
            $table->integer('unit_measure_id');
            $table->string('amount_used')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lab_category_items');
    }
}
