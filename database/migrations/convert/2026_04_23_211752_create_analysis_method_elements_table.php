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
        Schema::create('analysis_method_elements', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('analysis_method_id')->index('analysis_method_id');
            $table->uuid('analyte_id')->index('analyte_id');
            $table->double('quantity');
            $table->boolean('active');
            $table->timestamps();
            $table->uuid('company_id')->nullable()->index('idx_analysis_method_elements_company_id_7be8f7db');
            $table->foreign(['analysis_method_id'], 'fk_analysis_method_elements_analysis_method_id_9a29a709')->references(['id'])->on('analysis_methods')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['analyte_id'], 'fk_analysis_method_elements_analyte_id_437636e5')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_analysis_method_elements_company_id_85f7ffa2')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analysis_method_elements');
    }
};
