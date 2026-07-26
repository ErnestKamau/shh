<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'payment_method')) {
                $table->string('payment_method', 100)->nullable()->after('payment_terms_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            if (Schema::hasColumn('crm_customers', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
        });
    }
};
