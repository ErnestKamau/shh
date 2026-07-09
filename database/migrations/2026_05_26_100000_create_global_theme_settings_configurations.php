<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\System\SystemConfigurationsType;
use App\Models\System\SystemConfiguration;
use App\Services\System\ThemeService;

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

        foreach (ThemeService::AMSPEC_THEME as $key => $value) {
            SystemConfiguration::firstOrCreate(
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
        $type = SystemConfigurationsType::where('configuration_type', 'Global System Theme Settings')->first();
        if ($type) {
            SystemConfiguration::where('configuration_type_id', $type->id)->delete();
            $type->delete();
        }
    }
};
