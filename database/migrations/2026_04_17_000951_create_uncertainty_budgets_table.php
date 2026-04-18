<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUncertaintyBudgetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uncertainty_budgets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('analyte_id');
            $table->unsignedBigInteger('method_id');
            $table->decimal('coverage_factor_k', 5, 2)->default(2.00);
            $table->decimal('confidence_level', 5, 2)->default(95.00);
            $table->decimal('combined_standard_uncertainty', 10, 6)->nullable();
            $table->decimal('expanded_uncertainty', 10, 6)->nullable();
            $table->integer('version_number')->default(1);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('company_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Note: Foreign key constraints removed for MariaDB compatibility

            // Indexes
            $table->index(['analyte_id', 'method_id']);
            $table->index(['company_id', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uncertainty_budgets');
    }
}