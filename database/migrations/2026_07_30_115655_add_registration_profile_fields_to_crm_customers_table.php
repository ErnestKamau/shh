<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'contact_person')) {
                $table->text('contact_person')->nullable()->after('name');
            }
            if (! Schema::hasColumn('crm_customers', 'designation')) {
                $table->text('designation')->nullable()->after('contact_person');
            }
            if (! Schema::hasColumn('crm_customers', 'billing_address')) {
                $table->text('billing_address')->nullable()->after('physical_address');
            }
            if (! Schema::hasColumn('crm_customers', 'trade_license')) {
                $table->text('trade_license')->nullable()->after('vat_no');
            }
            if (! Schema::hasColumn('crm_customers', 'vat_registration_certificate')) {
                $table->string('vat_registration_certificate')->nullable()->after('trade_license');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            $columns = [
                'contact_person',
                'designation',
                'billing_address',
                'trade_license',
                'vat_registration_certificate',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('crm_customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
