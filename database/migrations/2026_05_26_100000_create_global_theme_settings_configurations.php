<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\System\SystemConfigurationsType;
use App\Models\System\SystemConfiguration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::firstOrCreate(
            ['configuration_type' => 'Global System Theme Settings'],
            [
                'description' => 'Manage colors and aesthetics globally across all modules, including primary highlight colors and sidebar styles.',
                'status' => true,
            ]
        );

        $configs = [
            [
                'key' => 'sys_theme_primary_color',
                'value' => '#00A7DF',
                'status' => true,
            ],
            [
                'key' => 'sys_theme_secondary_color',
                'value' => '#0090C0',
                'status' => true,
            ],
            [
                'key' => 'sys_theme_accent_color',
                'value' => '#FFFFFF',
                'status' => true,
            ],
            [
                'key' => 'sys_sidebar_bg_color',
                'value' => '#000000',
                'status' => true,
            ],
            [
                'key' => 'sys_sidebar_link_bg',
                'value' => 'rgba(255, 255, 255, 0.08)',
                'status' => true,
            ],
        ];

        foreach ($configs as $config) {
            SystemConfiguration::firstOrCreate(
                ['key' => $config['key']],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $config['value'],
                    'status' => $config['status'],
                ]
            );
        }
    }

    public function down(): void
    {
        $type = SystemConfigurationsType::where('configuration_type', 'Global System Theme Settings')->first();
        if ($type) {
            SystemConfiguration::where('configuration_type_id', $type->id)->delete();
            $type->delete();
        }
    }
};
