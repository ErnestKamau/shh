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
        Schema::create('report_formats', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('report_name');
            $table->string('report_code')->index('idx_report_formats_report_code_a6f15e0a');
            $table->string('results_display_type')->default('grid');
            $table->boolean('is_active')->default(true)->index('idx_report_formats_is_active_b50f1bd0');
            $table->uuid('company_id')->index('idx_report_formats_company_id_b3054bb4');
            $table->timestamps();

            $table->unique(['report_code', 'company_id'], 'report_formats_code_company_unique');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_formats');
    }
};
