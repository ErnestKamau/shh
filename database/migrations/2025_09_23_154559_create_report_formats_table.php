<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportFormatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_formats', function (Blueprint $table) {
            $table->bigInteger('id', true);
            $table->string('report_name');
            $table->string('report_code');
            $table->boolean('is_active')->default(true);
            $table->integer('company_id');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('company_id');
            $table->index('is_active');
            $table->index('report_code');
            
            // Unique constraint for report_code per company
            $table->unique(['report_code', 'company_id'], 'report_formats_code_company_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('report_formats');
    }
}