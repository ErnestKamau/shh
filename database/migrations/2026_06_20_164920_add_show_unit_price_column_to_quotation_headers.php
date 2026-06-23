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
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_headers', 'show_unit_price_column')) {
                $table->boolean('show_unit_price_column')->default(true)->after('show_mu_column');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_headers', 'show_unit_price_column')) {
                $table->dropColumn('show_unit_price_column');
            }
        });
    }
};
