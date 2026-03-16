<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('captured_procedure_values', function (Blueprint $table) {
            $table->json('equipment_ids')->nullable()->after('value');
            $table->json('measurand_ids')->nullable()->after('equipment_ids');
            $table->json('analyst_ids')->nullable()->after('measurand_ids');
        });
    }

    public function down(): void
    {
        Schema::table('captured_procedure_values', function (Blueprint $table) {
            $table->dropColumn(['equipment_ids', 'measurand_ids', 'analyst_ids']);
        });
    }
};
