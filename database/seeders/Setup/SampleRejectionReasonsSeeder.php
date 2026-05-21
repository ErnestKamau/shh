<?php

namespace Database\Seeders\Setup;

use App\Models\System\SystemConfiguration;
use Illuminate\Database\Seeder;

class SampleRejectionReasonsSeeder extends Seeder
{
    public function run(): void
    {
        $reasons = [
            ['key' => 'improper_container', 'label' => 'Sample was collected in improper container'],
            ['key' => 'not_properly_sealed', 'label' => 'Sample not properly sealed was leaking'],
            ['key' => 'inappropriate_storage', 'label' => 'Sample was stored in appropriate storage conditions'],
            ['key' => 'inappropriate_treatment', 'label' => 'Sample was inappropriate treated after sampling prior analysis'],
            ['key' => 'improperly_labeled', 'label' => 'Sample was improperly labeled and date of collection was not clear'],
            ['key' => 'inappropriate_material', 'label' => 'Sample material was inappropriate for the test(s) requested'],
            ['key' => 'inappropriate_volume', 'label' => 'Sample volume /weight was inappropriate for the test(s) requested'],
            ['key' => 'no_request_form', 'label' => 'Sample was not accompanied by a request form/sample could not be related to a request form'],
            ['key' => 'label_mismatch', 'label' => 'Sample name/date of collection on request form did not match the same details on the sample label'],
            ['key' => 'other', 'label' => 'Other'],
        ];

        SystemConfiguration::query()->updateOrCreate(
            ['key' => 'sample_rejection_reasons'],
            [
                'value' => json_encode($reasons, JSON_UNESCAPED_UNICODE),
                'status' => true,
                'configuration_type_id' => null,
            ]
        );
    }
}
