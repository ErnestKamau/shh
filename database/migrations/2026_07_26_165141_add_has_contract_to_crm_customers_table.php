<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->boolean('has_contract')->default(false)->after('contract_valid_to');
        });

        DB::table('crm_customers')
            ->where(function ($query) {
                $query->where('engagement_type', 'contract')
                    ->orWhereNotNull('contract_valid_from')
                    ->orWhereNotNull('contract_valid_to');
            })
            ->update(['has_contract' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->dropColumn('has_contract');
        });
    }
};
