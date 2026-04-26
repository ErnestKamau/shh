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
        Schema::create('risk_review_frequencies', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_level');
            $table->integer('frequency_days');
            $table->string('frequency_label');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_risk_review_frequencies_company_id_cc43fb4d');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_review_frequencies_company_id_is_active_8811f8ab');
            $table->unique(['risk_level', 'company_id']);
            $table->foreign(['company_id'], 'fk_risk_review_frequencies_company_id_ae1de1c3')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
