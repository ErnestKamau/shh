<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_reading_steps', function (Blueprint $table) {
            $table->json('derived_config')->nullable()->after('expression');
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_reading_steps', function (Blueprint $table) {
            $table->dropColumn('derived_config');
        });
    }
};
