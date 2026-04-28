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
        Schema::create('analytes_unik', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->index('code');
            $table->string('name');
            $table->integer('decimal_places');
            $table->double('equivalent_weight')->nullable();
            $table->string('reporting_symbol')->nullable();
            $table->string('reporting_unit')->nullable();
            $table->string('method');
            $table->string('common_name')->nullable()->default('-');
            $table->boolean('non_detectable');
            $table->boolean('non_accredited');
            $table->boolean('active');
            $table->uuid('company_id')->index('idx_analytes_unik_company_id_821bfbda');
            $table->boolean('show_on_report');
            $table->timestamps();
            $table->uuid('equipment_id')->nullable()->default('0')->index('equipment_id');
            $table->unique(['code'], 'code_unik');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytes_unik');
    }
};
