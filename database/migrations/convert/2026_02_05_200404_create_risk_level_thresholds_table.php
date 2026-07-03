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
        if (Schema::hasTable('risk_level_thresholds')) {
            return;
        }
        Schema::create('risk_level_thresholds', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_level');
            $table->integer('min_rpn')->nullable();
            $table->integer('max_rpn')->nullable();
            $table->string('color_code')->default('#6c757d');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->nullable()->index('idx_risk_level_thresholds_company_id_ec02a93f');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_level_thresholds_company_id_is_active_9114d83d');
            $table->unique(['risk_level', 'company_id']);

            $table->primary(['id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_level_thresholds');
    }
};
