<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQcReportHeaderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qc_report_header', function (Blueprint $table) {
            $table->id();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->integer('analyte_id')->nullable();
            $table->integer('sample_type_id')->nullable();
            $table->integer('analysis_type_id')->nullable();
            $table->integer('qc_type_id')->nullable();
            $table->integer('qc_scheme_id')->nullable();
            $table->double('rob_average')->nullable();
            $table->double('rob_std_deviation')->nullable();
            $table->double('rob_cv')->nullable();
            $table->double('cv_star')->nullable();
            $table->double('std_star')->nullable();
            $table->double('statistical_population')->nullable();
            $table->text('qc_results_ids')->nullable();

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
        Schema::dropIfExists('qc_report_header');
    }
}
