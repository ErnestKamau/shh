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
        Schema::table('crm_customers', function (Blueprint $table) {
            if (!Schema::hasColumn('crm_customers', 'customer_type')) {
                $table->string('customer_type', 20)
                    ->default('external')
                    ->after('lab_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm_customers', function (Blueprint $table) {
            if (Schema::hasColumn('crm_customers', 'customer_type')) {
                $table->dropColumn('customer_type');
            }
        });
    }
};

