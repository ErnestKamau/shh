<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Replace the empty stub table (id + timestamps only) with the real preference schema.
     */
    public function up(): void
    {
        if (Schema::hasTable('customer_analysis_type_standards')
            && Schema::hasColumn('customer_analysis_type_standards', 'standard_id')
            && Schema::hasColumn('customer_analysis_type_standards', 'crm_customer_id')
            && Schema::hasColumn('customer_analysis_type_standards', 'analysis_type_id')) {
            return;
        }

        Schema::dropIfExists('customer_analysis_type_standards');

        Schema::create('customer_analysis_type_standards', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('crm_customer_id');
            $table->uuid('analysis_type_id');
            $table->uuid('standard_id');
            $table->timestamps();

            $table->unique(['crm_customer_id', 'analysis_type_id'], 'cats_customer_analysis_type_unique');
            $table->index('standard_id');

            $table->foreign('crm_customer_id')
                ->references('id')
                ->on('crm_customers')
                ->cascadeOnDelete();

            $table->foreign('analysis_type_id')
                ->references('id')
                ->on('analysis_types')
                ->cascadeOnDelete();

            $table->foreign('standard_id')
                ->references('id')
                ->on('standards')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Intentionally irreversible: rolling back would recreate the broken stub table.
    }
};
