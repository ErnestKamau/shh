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

        Schema::table('crm_customers', function (Blueprint $table) {
            if (! Schema::hasColumn('crm_customers', 'city_id')) {
                $table->string('city_id')->nullable()->after('country_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('crm_customers')) {
            return;
        }

        Schema::table('crm_customers', function (Blueprint $table) {
            if (Schema::hasColumn('crm_customers', 'city_id')) {
                $table->dropColumn('city_id');
            }
        });
    }
};
