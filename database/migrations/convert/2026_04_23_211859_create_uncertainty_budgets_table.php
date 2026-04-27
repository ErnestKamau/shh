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
        Schema::create('uncertainty_budgets', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('analyte_id')->index('uncertainty_budgets_analyte_id_method_id_index');
            $table->text('method_ids');
            $table->decimal('coverage_factor_k', 5)->default(2);
            $table->decimal('confidence_level', 5)->default(95);
            $table->decimal('combined_standard_uncertainty', 10, 6)->nullable();
            $table->decimal('expanded_uncertainty', 10, 6)->nullable();
            $table->integer('version_number')->default(1);
            $table->uuid('created_by')->nullable()->index('idx_uncertainty_budgets_created_by');
            $table->uuid('company_id')->index('idx_uncertainty_budgets_company_id_b4edf8f4');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'active'], 'idx_uncertainty_budgets_company_id_active_9c20bea0');
            $table->foreign(['analyte_id'], 'fk_uncertainty_budgets_analyte_id_d1244d3a')->references(['id'])->on('analytes')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['company_id'], 'fk_uncertainty_budgets_company_id_8cf47a1e')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['created_by'], 'fk_uncertainty_budgets_created_by')->references(['id'])->on('users')->onUpdate('no action')->onDelete('set null');


            $table->primary(['id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uncertainty_budgets');
    }
};
