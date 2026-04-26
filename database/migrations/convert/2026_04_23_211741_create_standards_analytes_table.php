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
        Schema::create('standards_analytes', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('analyte_id')->index('idx_standards_analytes_analyte_id_cafeddea');
            $table->uuid('standard_id')->index('idx_standards_analytes_standard_id_2b5f7354');
            $table->uuid('standard_value_id')->nullable()->index('idx_standards_analytes_standard_value_id_440c8d9a');
            $table->string('standard_value_type')->nullable();
            $table->string('low')->nullable();
            $table->string('high')->nullable();
            $table->string('standard_is_value')->nullable();
            $table->text('comments')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('expected_value')->nullable();
            $table->boolean('absolute_tolerance')->nullable()->default(false);
            $table->boolean('is_active')->nullable()->default(true);
            $table->string('mean_value', 100)->nullable();
            $table->string('rel_std_dev', 100)->nullable();
            $table->string('tolerance_1', 100)->nullable();
            $table->string('tolerance_2', 100)->nullable();
            $table->string('value_type', 100)->nullable();
            $table->string('matrix_operator')->nullable();
            $table->string('matrix_value')->nullable();
            $table->foreign(['analyte_id'], 'fk_standards_analytes_analyte_id_1ce10517')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_id'], 'fk_standards_analytes_standard_id_d4160bfa')->references(['id'])->on('standards')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['standard_value_id'], 'fk_standards_analytes_standard_value_id_85cb104e')->references(['id'])->on('standard_values')->onUpdate('no action')->onDelete('set null');



            $table->primary(['id']);



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standards_analytes');
    }
};
