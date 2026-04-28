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
        Schema::create('analysis_methods', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name')->index('idx_analysis_methods_name_d1473255');
            $table->string('code');
            $table->uuid('company_id')->index('idx_analysis_methods_company_id_907b5a1b');
            $table->string('description')->nullable();
            $table->boolean('active');
            $table->timestamps();
            $table->boolean('is_sampling_method')->nullable()->default(false)->index('idx_analysis_methods_is_sampling_method_1dfb77e7');
            $table->boolean('is_ltm')->nullable()->default(false);
            $table->integer('reference_type_id')->nullable();
            $table->integer('method_type_id')->nullable();
            $table->string('validation_status')->default('pending');
            $table->uuid('sample_header_id')->nullable()->index('idx_analysis_methods_sample_header_id_8852b6b0');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_methods');
    }
};
