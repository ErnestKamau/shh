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
        if (Schema::hasTable('sample_analysis_stages')) {
            return;
        }
        Schema::create('sample_analysis_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->boolean('active');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_sample_analysis_stages_company_id_a04755a6');
            $table->string('sample_workflow', 100)->nullable();
            $table->integer('level')->nullable()->default(0);
            $table->integer('section_head_id')->nullable();
            $table->uuid('lab_id')->nullable()->index('idx_sample_analysis_stages_lab_id_14006729');
            $table->string('code', 100)->nullable();
            $table->string('title', 100)->nullable();
            $table->boolean('is_system')->nullable()->default(false);
            $table->boolean('is_sample_stage')->nullable()->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sample_analysis_stages');
    }
};
