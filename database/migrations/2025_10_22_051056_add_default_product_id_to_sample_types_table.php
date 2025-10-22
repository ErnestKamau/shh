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
        Schema::table('sample_types', function (Blueprint $table) {
            $table->foreignId('default_product_id')->nullable()->after('report_format_id')->constrained('company_products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_types', function (Blueprint $table) {
            $table->dropForeign(['default_product_id']);
            $table->dropColumn('default_product_id');
        });
    }
};
