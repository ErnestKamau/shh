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
        Schema::table('pricelist_items', function (Blueprint $table): void {
            if (!Schema::hasColumn('pricelist_items', 'analysis_element_id')) {
                $table->uuid('analysis_element_id')->nullable()->after('analysis_id');
                $table->index('analysis_element_id', 'idx_pricelist_items_analysis_element_id');
                $table->foreign('analysis_element_id', 'fk_pricelist_items_analysis_element_id')
                    ->references('id')
                    ->on('analysis_elements')
                    ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricelist_items', function (Blueprint $table): void {
            if (Schema::hasColumn('pricelist_items', 'analysis_element_id')) {
                $table->dropForeign('fk_pricelist_items_analysis_element_id');
                $table->dropIndex('idx_pricelist_items_analysis_element_id');
                $table->dropColumn('analysis_element_id');
            }
        });
    }
};
