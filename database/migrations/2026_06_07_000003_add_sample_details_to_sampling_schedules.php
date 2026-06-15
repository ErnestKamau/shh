<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sampling_schedules', function (Blueprint $table) {
            $table->json('sample_details')->nullable()->after('parameters');
        });
    }

    public function down(): void
    {
        Schema::table('sampling_schedules', function (Blueprint $table) {
            $table->dropColumn('sample_details');
        });
    }
};
