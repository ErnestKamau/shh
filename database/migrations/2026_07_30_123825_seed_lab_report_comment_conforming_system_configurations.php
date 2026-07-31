<?php

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ensure Conforming / Non-Conforming lab report comment configs exist.
     * The earlier rename migration is a no-op when legacy pass/fail keys were never present.
     */
    public function up(): void
    {
        $this->renameKeyIfPresent('lab_report_comment_pass', 'lab_report_comment_conforming');
        $this->renameKeyIfPresent('lab_report_comment_fail', 'lab_report_comment_non_conforming');

        $type = SystemConfigurationsType::query()
            ->where('configuration_type', 'General Configuration')
            ->first();

        if ($type === null) {
            $type = SystemConfigurationsType::query()->updateOrCreate(
                ['configuration_type' => 'General Configuration'],
                [
                    'description' => 'General system configurations',
                    'status' => true,
                ]
            );
        }

        $defaults = [
            'lab_report_comment_conforming' => 'The above test result is conforming to the applied standard(s).',
            'lab_report_comment_non_conforming' => 'The above test result is non-conforming to the applied standard(s).',
        ];

        foreach ($defaults as $key => $value) {
            SystemConfiguration::query()->updateOrCreate(
                ['key' => $key],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $value,
                    'status' => true,
                ]
            );
        }
    }

    public function down(): void
    {
        // Keep configs; only reverse rename if legacy keys are preferred on rollback.
        $this->renameKeyIfPresent('lab_report_comment_conforming', 'lab_report_comment_pass');
        $this->renameKeyIfPresent('lab_report_comment_non_conforming', 'lab_report_comment_fail');
    }

    private function renameKeyIfPresent(string $from, string $to): void
    {
        if (! DB::table('system_configurations')->where('key', $from)->exists()) {
            return;
        }

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
