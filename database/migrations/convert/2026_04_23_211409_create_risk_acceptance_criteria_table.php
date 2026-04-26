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
        Schema::create('risk_acceptance_criteria', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('threshold_rpn');
            $table->text('criteria_description')->nullable();
            $table->boolean('requires_treatment_plan')->default(true);
            $table->boolean('requires_monitoring')->default(true);
            $table->boolean('can_skip_treatment')->default(false);
            $table->text('applicable_categories')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_risk_acceptance_criteria_company_id_795b6851');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_acceptance_criteria_company_id_is_active_b65fbbf0');
            $table->foreign(['company_id'], 'fk_risk_acceptance_criteria_company_id_b0deb05f')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_acceptance_criteria');
    }
};
