<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table): void {
            if (! Schema::hasColumn('crm_customers', 'quotation_acceptance_tat_minutes')) {
                $table->unsignedInteger('quotation_acceptance_tat_minutes')
                    ->nullable()
                    ->after('credit_days');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table): void {
            if (Schema::hasColumn('crm_customers', 'quotation_acceptance_tat_minutes')) {
                $table->dropColumn('quotation_acceptance_tat_minutes');
            }
        });
    }
};
