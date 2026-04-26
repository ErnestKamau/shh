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
        Schema::create('sample_analysis_stages', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->boolean('active');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_sample_analysis_stages_company_id_6e142bb0');
            $table->string('sample_workflow', 100)->nullable();
            $table->integer('level')->nullable()->default(0);
            $table->integer('section_head_id')->nullable();
            $table->uuid('lab_id')->nullable()->index('idx_sample_analysis_stages_lab_id_49cd48ff');
            $table->string('code', 100)->nullable();
            $table->string('title', 100)->nullable();
            $table->boolean('is_system')->nullable()->default(false);
            $table->boolean('is_sample_stage')->nullable()->default(false);
            $table->foreign(['company_id'], 'fk_sample_analysis_stages_company_id_cca3edf2')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['lab_id'], 'fk_sample_analysis_stages_lab_id_dd6032a1')->references(['id'])->on('labs')->onUpdate('no action')->onDelete('set null');


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
