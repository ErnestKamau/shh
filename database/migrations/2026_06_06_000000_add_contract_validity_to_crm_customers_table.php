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
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'contract_valid_from')) {
                $table->date('contract_valid_from')->nullable()->after('currency_id');
            }
            if (! Schema::hasColumn('crm_customers', 'contract_valid_to')) {
                $table->date('contract_valid_to')->nullable()->after('contract_valid_from');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $table->dropColumn(['contract_valid_from', 'contract_valid_to']);
        });
    }
};
