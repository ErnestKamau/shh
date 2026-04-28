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
            $table->string('code')->index('idx_analytes_unik_code_8aa08f10');
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
            $table->uuid('company_id')->index('idx_analytes_unik_company_id_61cb5ab5');
            $table->boolean('show_on_report');
            $table->timestamps();
            $table->uuid('equipment_id')->nullable()->index('idx_analytes_unik_equipment_id_e22bb090');
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
