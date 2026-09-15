<?php

use App\Company;
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
        if (! Schema::hasColumn('companies', 'po_box')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->text('po_box')->nullable()->after('fax');
            });
        }

        // Ensure AmSpec Dubai letterhead fields match the TRF header exactly.
        Company::query()
            ->where('code', 'uae')
            ->each(function (Company $company): void {
                $company->name = 'AmSpec Middle East Inspection and Testing LLC – Branch';
                $company->telephone = '+971 45576370';
                $company->email = 'AgriFood.UAE.Commercial@amspecgroup.com';
                $company->address = 'Warehouse Phase 2, Block D Premises No. D05, Dubai Science Park, Al Barsha South, Dubai, United Arab Emirates';
                $company->po_box = '500767';
                $company->fax = '';
                $company->website = 'www.amspecgroup.com';
                $company->save();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('companies', 'po_box')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn('po_box');
            });
        }
    }
};
