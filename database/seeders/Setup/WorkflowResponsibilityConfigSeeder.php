<?php

namespace Database\Seeders\Setup;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Database\Seeder;

class WorkflowResponsibilityConfigSeeder extends Seeder
{
    public function run(): void
    {
        $type = SystemConfigurationsType::query()->updateOrCreate(
            ['configuration_type' => 'Job Designation Responsibilities'],
            [
                'description' => 'Workflow stages assignable to personnel designations.',
                'status' => true,
            ]
        );

        $workflowStages = [
            'Samples En-Route' => 'Users responsible for sample receiving while samples are en-route.',
            'Samples Reception' => 'Users responsible for receiving and logging submitted samples.',
            'Samples Request Review' => 'Users responsible for reviewing submitted sample requests and forms.',
            'Samples In Lab' => 'Users responsible for custody of samples currently in the lab.',
            'Sample Verification' => 'Users responsible for verifying analyzed samples and recorded results.',
            'Sample Approval' => 'Users responsible for approving verified sample results.',
            'Reports In Payment' => 'Users responsible for payment-stage report processing.',
            'Reports for Collection' => 'Users responsible for releasing finalized reports for collection.',
        ];

        foreach ($workflowStages as $key => $description) {
            SystemConfiguration::query()->updateOrCreate(
                [
                    'key' => $key,
                ],
                [
                    'configuration_type_id' => $type->id,
                    'value' => $description,
                    'status' => true,
                ]
            );
        }
    }
}