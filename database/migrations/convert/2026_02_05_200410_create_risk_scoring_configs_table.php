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
        Schema::create('risk_scoring_configs', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('scoring_method')->default('multiplicative');
            $table->text('formula')->nullable();
            $table->integer('max_likelihood_score')->default(5);
            $table->integer('max_severity_score')->default(5);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_risk_scoring_configs_company_id_2b0dbe71');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_scoring_configs_company_id_is_active_2d284f18');

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_scoring_configs');
    }
};
