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
        if (Schema::hasTable('analysis_guides')) {
            return;
        }
        Schema::create('analysis_guides', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('guide_name')->nullable();
            $table->uuid('analyte_id')->index('idx_analysis_guides_analyte_id_e5fcc95d');
            $table->uuid('analysis_type_id')->index('idx_analysis_guides_analysis_type_id_0f41537c');
            $table->double('value')->nullable();
            $table->string('comments', 1024)->nullable();
            $table->string('recommendations', 1024)->nullable();
            $table->timestamps();
            $table->uuid('standard_id')->nullable()->index('idx_analysis_guides_standard_id_58cd7add');
            $table->string('standard_value_type')->nullable();
            $table->string('high')->nullable();
            $table->string('low')->nullable();
            $table->uuid('standard_value_id')->nullable()->index('idx_analysis_guides_standard_value_id_f5b1dd88');
            $table->string('standard_is_value')->nullable();

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_guides');
    }
};
