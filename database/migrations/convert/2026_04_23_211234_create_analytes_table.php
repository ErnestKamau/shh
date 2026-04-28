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
        Schema::create('analytes', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('code')->index('idx_analytes_code_fc995921');
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
            $table->uuid('company_id')->nullable()->index('idx_analytes_company_id_b63ba3fe');
            $table->boolean('show_on_report');
            $table->timestamps();
            $table->softDeletes();
            $table->uuid('equipment_id')->nullable()->index('idx_analytes_equipment_id_66bbf2fb');
            $table->boolean('is_italic')->nullable()->default(false);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytes');
    }
};
