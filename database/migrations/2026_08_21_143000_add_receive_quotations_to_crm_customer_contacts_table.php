<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('crm_customer_contacts')) {
            return;
        }

        Schema::table('crm_customer_contacts', function (Blueprint $table): void {
            if (! Schema::hasColumn('crm_customer_contacts', 'receive_quotations')) {
                $table->boolean('receive_quotations')->default(false)->after('receive_invoice');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_customer_contacts')) {
            return;
        }

        Schema::table('crm_customer_contacts', function (Blueprint $table): void {
            if (Schema::hasColumn('crm_customer_contacts', 'receive_quotations')) {
                $table->dropColumn('receive_quotations');
            }
        });
    }
};
