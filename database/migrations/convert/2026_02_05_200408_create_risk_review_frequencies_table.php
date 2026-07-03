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
        if (Schema::hasTable('risk_review_frequencies')) {
            return;
        }
        Schema::create('risk_review_frequencies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_level');
            $table->integer('frequency_days');
            $table->string('frequency_label');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_risk_review_frequencies_company_id_dcb963da');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_review_frequencies_company_id_is_active_2602a7bc');
            $table->unique(['risk_level', 'company_id']);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_review_frequencies');
    }
};
