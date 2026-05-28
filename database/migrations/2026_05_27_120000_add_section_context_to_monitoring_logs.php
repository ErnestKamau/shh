<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table): void {
            $table->uuid('lab_section_id')->nullable()->after('equipment_id')->index();
            $table->unsignedTinyInteger('frequency_slot')->nullable()->after('lab_section_id');
            $table->text('remark')->nullable()->after('frequency_slot');

            $table->foreign('lab_section_id')
                ->references('id')
                ->on('lab_sections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('monitoring_logs', function (Blueprint $table): void {
            $table->dropForeign(['lab_section_id']);
            $table->dropColumn(['lab_section_id', 'frequency_slot', 'remark']);
        });
    }
};
