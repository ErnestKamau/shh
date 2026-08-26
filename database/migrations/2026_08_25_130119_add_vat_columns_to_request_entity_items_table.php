<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('request_entity_items')) {
            return;
        }

        Schema::table('request_entity_items', function (Blueprint $table) {
            if (! Schema::hasColumn('request_entity_items', 'vat_perc')) {
                $table->decimal('vat_perc', 8, 2)->nullable()->default(0)->after('unit_cost');
            }
            if (! Schema::hasColumn('request_entity_items', 'vat_inc')) {
                $table->boolean('vat_inc')->nullable()->default(false)->after('vat_perc');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('request_entity_items')) {
            return;
        }

        Schema::table('request_entity_items', function (Blueprint $table) {
            if (Schema::hasColumn('request_entity_items', 'vat_inc')) {
                $table->dropColumn('vat_inc');
            }
            if (Schema::hasColumn('request_entity_items', 'vat_perc')) {
                $table->dropColumn('vat_perc');
            }
        });
    }
};
