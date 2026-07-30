<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Lab report comment templates use Conforming / Non-Conforming terminology.
     * Keep legacy key names as a no-op fallback via application lookups when present.
     */
    public function up(): void
    {
        $this->renameKey('lab_report_comment_pass', 'lab_report_comment_conforming');
        $this->renameKey('lab_report_comment_fail', 'lab_report_comment_non_conforming');
    }

    public function down(): void
    {
        $this->renameKey('lab_report_comment_conforming', 'lab_report_comment_pass');
        $this->renameKey('lab_report_comment_non_conforming', 'lab_report_comment_fail');
    }

    private function renameKey(string $from, string $to): void
    {
        if (DB::table('system_configurations')->where('key', $to)->exists()) {
            DB::table('system_configurations')->where('key', $from)->delete();

            return;
        }

        DB::table('system_configurations')
            ->where('key', $from)
            ->update([
                'key' => $to,
                'updated_at' => now(),
            ]);
    }
};
