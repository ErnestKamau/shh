<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (! Schema::hasColumn('quotation_headers', 'crm_company_unit_id')) {
                $table->uuid('crm_company_unit_id')->nullable()->index()->after('sample_point_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotation_headers', function (Blueprint $table) {
            if (Schema::hasColumn('quotation_headers', 'crm_company_unit_id')) {
                $table->dropColumn('crm_company_unit_id');
            }
        });
    }
};
