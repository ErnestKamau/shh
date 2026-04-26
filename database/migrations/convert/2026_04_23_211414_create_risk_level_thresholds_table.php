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
        Schema::create('risk_level_thresholds', function (Blueprint $table) {
            $table->uuid('id');
            $table->string('risk_level');
            $table->integer('min_rpn')->nullable();
            $table->integer('max_rpn')->nullable();
            $table->string('color_code')->default('#6c757d');
            $table->text('description')->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->uuid('company_id')->default(0)->index('idx_risk_level_thresholds_company_id_851329d4');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'is_active'], 'idx_risk_level_thresholds_company_id_is_active_61edf4ce');
            $table->unique(['risk_level', 'company_id']);
            $table->foreign(['company_id'], 'fk_risk_level_thresholds_company_id_7ff6d18a')->references(['id'])->on('companies')->onUpdate('no action')->onDelete('cascade');

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
