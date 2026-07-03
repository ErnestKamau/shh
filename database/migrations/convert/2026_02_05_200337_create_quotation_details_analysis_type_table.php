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
        Schema::create('quotation_details_analysis_type', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('quotation_detail_id')->index('idx_quotation_details_analysis_type_quotation_detail_i_4d774d52');
            $table->uuid('analysis_type_id')->index('idx_quotation_details_analysis_type_analysis_type_id_293698f5');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_details_analysis_type');
    }
};
