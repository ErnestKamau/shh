<?php

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\Services\Sampleworkflow\AmendmentReportConfigurationService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => AmendmentReportConfigurationService::TYPE_NAME],
            [
                'description' => 'Wording and numbering formats for amended / revised lab reports.',
                'status' => true,
            ]
        );

        foreach (AmendmentReportConfigurationService::defaults() as $key => $value) {
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
        SystemConfiguration::query()
            ->whereIn('key', AmendmentReportConfigurationService::keys())
            ->delete();

        $type = SystemConfigurationsType::query()
            ->where('configuration_type', AmendmentReportConfigurationService::TYPE_NAME)
            ->first();

        if ($type !== null && $type->configurations()->count() === 0) {
            $type->delete();
        }
    }
};
