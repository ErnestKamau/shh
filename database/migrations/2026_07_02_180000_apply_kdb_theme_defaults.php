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
            foreach (ThemeService::CONFIG_DEFAULTS as $key => $value) {
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
        $legacy = [
            'sys_theme_primary_color' => '#6D0A0E',
            'sys_theme_secondary_color' => '#8B1E22',
            'sys_theme_accent_color' => '#ffffff',
            'sys_sidebar_bg_color' => '#6D0A0E',
            'sys_sidebar_link_bg' => 'rgba(255, 255, 255, 0.08)',
        ];

        $type = SystemConfigurationsType::where('configuration_type', 'Global System Theme Settings')->first();

        if ($type) {
            foreach ($legacy as $key => $value) {
                SystemConfiguration::where('key', $key)->update(['value' => $value]);
            }
        }

        SystemConfiguration::where('key', 'sys_quotation_primary_color')->update(['value' => '#6D0A0E']);

        ThemeService::forgetCache();
    }
};
