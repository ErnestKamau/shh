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
        Schema::create('uncertainty_sources', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('uncertainty_budget_id')->index('idx_uncertainty_sources_uncertainty_budget_id_2a1f7c69');
            $table->string('source_name');
            $table->enum('type', ['A', 'B']);
            $table->decimal('std_uncertainty_value', 10, 6);
            $table->decimal('sensitivity_coefficient', 10, 6)->default(1);
            $table->decimal('contribution_value', 10, 6)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->foreign(['uncertainty_budget_id'], 'fk_uncertainty_sources_uncertainty_budget_id_a959796b')->references(['id'])->on('uncertainty_budgets')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uncertainty_sources');
    }
};
