<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->boolean('attracts_equipment_logbook')->default(false)->after('is_result_step');
            $table->json('logbook_equipment_ids')->nullable()->after('attracts_equipment_logbook');
        });
    }

    public function down(): void
    {
        Schema::table('procedure_worksheet_steps', function (Blueprint $table) {
            $table->dropColumn(['attracts_equipment_logbook', 'logbook_equipment_ids']);
        });
    }
};
