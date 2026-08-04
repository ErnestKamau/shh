<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Billing quotations still need subcontracted_analytes for the Quotation Parameters
 * "Subcontracted" column. Do not drop the column.
 *
 * If an earlier draft of this migration already dropped it, recreate it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotation_details', 'subcontracted_analytes')) {
            return;
        }

        Schema::table('quotation_details', function (Blueprint $table) {
            $table->text('subcontracted_analytes')->nullable()->after('accredited_analytes');
        });
    }

    public function down(): void
    {
        // Intentionally empty — column is required for billing quotations.
    }
};
