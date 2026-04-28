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
        Schema::create('tat_captured', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('captured_result_id')->index('idx_tat_captured_captured_result_id_790535cb');
            $table->uuid('analysis_type_id')->index('idx_tat_captured_analysis_type_id_6955c016');
            $table->uuid('analyte_id')->index('idx_tat_captured_analyte_id_1ae474f5');
            $table->uuid('sample_type_id')->index('idx_tat_captured_sample_type_id_f3871855');
            $table->uuid('sample_detail_id')->index('idx_tat_captured_sample_detail_id_0d31372a');
            $table->string('result')->nullable();
            $table->integer('analyst_id');
            $table->integer('tat_overdue_days')->default(0);
            $table->dateTime('tat_date');
            $table->dateTime('finished_date')->nullable();
            $table->boolean('is_complete')->default(false);
            $table->uuid('sample_header_id')->nullable()->index('idx_tat_captured_sample_header_id_0559a84e');
            $table->integer('tat_remark')->nullable();
            $table->dateTime('start_date_analysis')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tat_captured');
    }
};
