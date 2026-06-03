<?php

use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::firstOrCreate(
            ['configuration_type' => 'Nature of Sample'],
            [
                'description' => 'Options for the Nature of Sample field on laboratory service request forms.',
                'status'      => true,
            ]
        );

        $options = [
            ['key' => 'Criminal',     'value' => 'criminal'],
            ['key' => 'Non-Criminal', 'value' => 'non_criminal'],
        ];

        foreach ($options as $option) {
            $existing = $type->configurations()->where('key', $option['key'])->first();
            if (! $existing) {
                $type->configurations()->create([
                    'key'    => $option['key'],
                    'value'  => $option['value'],
                    'status' => true,
                ]);
            }
        }
    }

    public function down(): void
    {
        $type = SystemConfigurationsType::where('configuration_type', 'Nature of Sample')->first();

        if ($type !== null) {
            $type->configurations()->delete();
            $type->delete();
        }
    }
};
