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
        if (Schema::hasTable('standards_analytes')) {
            return;
        }
        Schema::create('standards_analytes', function (Blueprint $table) {
            $table->uuid('id');
            $table->timestamps();
            $table->uuid('analyte_id')->index('idx_standards_analytes_analyte_id_c4a4d738');
            $table->uuid('standard_id')->index('idx_standards_analytes_standard_id_7746fccb');
            $table->uuid('standard_value_id')->nullable()->index('idx_standards_analytes_standard_value_id_94a086dc');
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
