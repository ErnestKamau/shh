<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        if (Schema::hasColumn('quotation_details', 'pricing_granularity')) {
            Schema::table('quotation_details', function (Blueprint $table) {
                $table->dropColumn('pricing_granularity');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('quotation_details')) {
            return;
        }

        if (! Schema::hasColumn('quotation_details', 'pricing_granularity')) {
            Schema::table('quotation_details', function (Blueprint $table) {
                $table->string('pricing_granularity', 32)->nullable()->after('unit_price');
            });
        }
    }
};
