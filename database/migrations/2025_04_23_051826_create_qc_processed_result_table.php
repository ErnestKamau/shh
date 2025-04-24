<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQcProcessedResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qc_processed_result', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('sample_type_id');
            $table->integer('analysis_type_id');
            $table->integer('analyte_id');
            $table->integer('method_id');
            $table->integer('standard_id');
            $table->integer('standard_value_id');
            $table->double('robust_standard_deviation')->nullable();
            $table->double('robust_mean')->nullable();
            $table->double('robust_median')->nullable();
            $table->double('robust_cv')->nullable();
            $table->double('robust_cv_percentage')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qc_processed_result');
    }
}
