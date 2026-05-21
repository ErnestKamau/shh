<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedSmallInteger('maintenance_start_year')->nullable()->after('show_on_reports');
            $table->unsignedTinyInteger('maintenance_start_month')->nullable()->after('maintenance_start_year');
            $table->unsignedSmallInteger('maintenance_end_year')->nullable()->after('maintenance_start_month');
            $table->unsignedTinyInteger('maintenance_end_month')->nullable()->after('maintenance_end_year');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'maintenance_start_year',
                'maintenance_start_month',
                'maintenance_end_year',
                'maintenance_end_month',
            ]);
        });
    }
};
