<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const STAGE_MAP = [
        '0' => 'All Complaints',
        '1' => 'Open Complaints',
        '2' => 'Complaints Approval',
        '3' => 'Complaints Resolution',
        '4' => 'Resolution Approval',
        '5' => 'Closed Complaints',
        '6' => 'Cancelled Complaints',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('chain_of_custody_complaints')) {
            return;
        }

        foreach (self::STAGE_MAP as $from => $to) {
            DB::table('chain_of_custody_complaints')->where('workflow_stage', $from)->update(['workflow_stage' => $to]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('chain_of_custody_complaints')) {
            return;
        }

        foreach (self::STAGE_MAP as $from => $to) {
            DB::table('chain_of_custody_complaints')->where('workflow_stage', $to)->update(['workflow_stage' => $from]);
        }
    }
};
