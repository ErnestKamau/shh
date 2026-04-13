<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->unsignedBigInteger('crm_unit_id')->nullable()->after('sample_point_id');
        });
    }

    public function down(): void
    {
        Schema::table('sample_details', function (Blueprint $table) {
            $table->dropColumn('crm_unit_id');
        });
    }
};
