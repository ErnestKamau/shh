<?php

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::updateOrCreate(
            ['configuration_type' => 'Module Visibility'],
            [
                'description' => 'Controls which application modules are visible and enabled',
                'status' => true,
            ]
        );

        SystemConfiguration::firstOrCreate(
            [
                'configuration_type_id' => $type->id,
                'key' => 'system_module_visibility',
            ],
            [
                'value' => '{}',
                'status' => true,
            ]
        );
    }

    public function down(): void
    {
        $type = SystemConfigurationsType::where('configuration_type', 'Module Visibility')->first();

        if ($type === null) {
            return;
        }

        $type->configurations()->where('key', 'system_module_visibility')->delete();
        $type->delete();
    }
};
