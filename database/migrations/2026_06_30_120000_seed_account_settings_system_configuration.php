<?php

use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $type = SystemConfigurationsType::firstOrCreate(
            ['configuration_type' => 'Account Settings'],
            [
                'description' => 'Customer account billing / credit status options.',
                'status'      => true,
            ]
        );

        $options = [
            ['key' => 'Account Holder(OK)', 'value' => 'Account Holder(OK)'],
            ['key' => 'Account Holder(Overdue)', 'value' => 'Account Holder(Overdue)'],
            ['key' => 'Pay Upfront', 'value' => 'Pay Upfront'],
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
        $type = SystemConfigurationsType::where('configuration_type', 'Account Settings')->first();

        if ($type !== null) {
            $type->configurations()->delete();
            $type->delete();
        }
    }
};
