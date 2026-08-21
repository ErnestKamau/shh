<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amspec UAE package quotation model:
 *   Total price = No. of samples × Unit price (ONCE per sample package).
 *   Quantity Required is free text on the package (e.g. "Per Sample Swab"),
 *   not a commercial multiplier — tests stay scope (method / LOQ / MU).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_details', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_details', 'quantity_required')) {
                $table->string('quantity_required')->nullable()->after('quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_details', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_details', 'quantity_required')) {
                $table->dropColumn('quantity_required');
            }
        });
    }
};
