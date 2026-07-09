<?php

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\Services\System\ThemeService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::where('configuration_type', 'Global System Theme Settings')->first();

        if ($type) {
            foreach (ThemeService::AMSPEC_THEME as $key => $value) {
                SystemConfiguration::updateOrCreate(
                    ['key' => $key],
                    [
                        'configuration_type_id' => $type->id,
                        'value' => $value,
                        'status' => true,
                    ]
                );
            }
        }

        $quotationType = SystemConfigurationsType::where('configuration_type', 'Quotation Report')->first();

        if ($quotationType) {
            SystemConfiguration::updateOrCreate(
                ['key' => 'sys_quotation_primary_color'],
                [
                    'configuration_type_id' => $quotationType->id,
                    'value' => ThemeService::QUOTATION_PRIMARY,
                    'status' => true,
                ]
            );
        }

        ThemeService::forgetCache();
    }

    public function down(): void
    {
        ThemeService::forgetCache();
    }
};
