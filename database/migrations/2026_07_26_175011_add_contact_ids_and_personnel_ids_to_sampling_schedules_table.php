<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sampling_schedules', function (Blueprint $table) {
            $table->json('contact_ids')->nullable()->after('contact_id');
            $table->json('personnel_ids')->nullable()->after('personnel_id');
        });

        // Backfill from legacy single FK columns.
        foreach (DB::table('sampling_schedules')->select('id', 'contact_id', 'personnel_id')->cursor() as $row) {
            $contactIds = ! empty($row->contact_id) ? [$row->contact_id] : [];
            $personnelIds = ! empty($row->personnel_id) ? [$row->personnel_id] : [];

            DB::table('sampling_schedules')
                ->where('id', $row->id)
                ->update([
                    'contact_ids' => $contactIds !== [] ? json_encode($contactIds) : null,
                    'personnel_ids' => $personnelIds !== [] ? json_encode($personnelIds) : null,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sampling_schedules', function (Blueprint $table) {
            $table->dropColumn(['contact_ids', 'personnel_ids']);
        });
    }
};
