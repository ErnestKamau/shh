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
        if (Schema::hasTable('uncertainty_budgets')) {
            return;
        }
        Schema::create('uncertainty_budgets', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('analyte_id')->index('idx_uncertainty_budgets_analyte_id_865b53d8');
            $table->text('method_ids');
            $table->decimal('coverage_factor_k', 5)->default(2);
            $table->decimal('confidence_level', 5)->default(95);
            $table->decimal('combined_standard_uncertainty', 10, 6)->nullable();
            $table->decimal('expanded_uncertainty', 10, 6)->nullable();
            $table->integer('version_number')->default(1);
            $table->uuid('created_by')->nullable()->index('idx_uncertainty_budgets_created_by_1457eabf');
            $table->uuid('company_id')->index('idx_uncertainty_budgets_company_id_bbb2c70a');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['company_id', 'active'], 'idx_uncertainty_budgets_company_id_active_0fce1f25');

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
