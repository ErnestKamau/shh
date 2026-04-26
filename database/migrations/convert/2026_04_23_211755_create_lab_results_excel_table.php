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
        Schema::create('lab_results_excel', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->string('excel_url');
            $table->integer('strip_id');
            $table->date('date_of_reading');
            $table->string('test_week');
            $table->uuid('sample_header_id')->nullable()->index('idx_lab_results_excel_sample_header_id_0baaa715');
            $table->integer('import_user_id')->nullable();
            $table->uuid('analyte_id')->nullable()->index('idx_lab_results_excel_analyte_id_60d14c57');
            $table->foreign(['analyte_id'], 'fk_lab_results_excel_analyte_id_0e9eb583')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['sample_header_id'], 'fk_lab_results_excel_sample_header_id_ebf277af')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_results_excel');
    }
};
