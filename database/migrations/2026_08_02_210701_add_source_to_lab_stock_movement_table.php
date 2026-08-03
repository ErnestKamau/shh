<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_stock_movement', function (Blueprint $table) {
            $table->string('source_type')->nullable()->after('preparation_id');
            $table->string('source_id')->nullable()->after('source_type');

            $table->index(['source_type', 'source_id'], 'lsm_source_idx');
        });
    }

    public function down(): void
    {
        Schema::table('lab_stock_movement', function (Blueprint $table) {
            $table->dropIndex('lsm_source_idx');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
