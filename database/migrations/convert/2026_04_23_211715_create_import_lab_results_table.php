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
        Schema::create('import_lab_results', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('batch_id')->index('idx_import_lab_results_batch_id');
            $table->string('sample_no', 500);
            $table->string('result');
            $table->string('analyte_code');
            $table->string('mean')->nullable();
            $table->string('std_dev')->nullable();
            $table->string('cv')->nullable();
            $table->integer('count')->nullable();
            $table->string('well_id', 100)->nullable();
            $table->string('remark', 500)->nullable();
            $table->integer('remark_config')->nullable();
            $table->boolean('double_entry')->default(false);
            $table->boolean('is_proccessed')->default(false);
            $table->string('is_selected', 100)->default('1');
            $table->date('date_of_reading')->nullable();
            $table->string('test_week', 100)->nullable();
            $table->string('well', 100)->nullable();
            $table->uuid('analyte_id')->nullable()->index('idx_import_lab_results_analyte_id_1d731d5d');
            $table->string('analyte_name', 500)->nullable();
            $table->integer('analyst_id')->nullable();
            $table->foreign(['analyte_id'], 'fk_import_lab_results_analyte_id_1d31291e')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['batch_id'], 'fk_import_lab_results_batch_id')->references(['id'])->on('sample_headers')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_lab_results');
    }
};
