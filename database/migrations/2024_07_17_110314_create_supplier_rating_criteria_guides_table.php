<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierRatingCriteriaGuidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_rating_criteria_guides', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->integer('criteria_id');
            $table->double('lower_value', 2);
            $table->double('upper_value', 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('supplier_rating_criteria_guides');
    }
}
