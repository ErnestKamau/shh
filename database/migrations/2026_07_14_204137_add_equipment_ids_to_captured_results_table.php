<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captured_results', function (Blueprint $table) {
            $table->json('equipment_ids')->nullable()->after('equipment_id');
        });
    }

    public function down(): void
    {
        Schema::table('captured_results', function (Blueprint $table) {
            $table->dropColumn('equipment_ids');
        });
    }
};
