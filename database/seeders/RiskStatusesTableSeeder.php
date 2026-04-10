<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RiskManagement\RiskStatus;

class RiskStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'name' => 'Identified',
                'code' => 'IDENTIFIED',
                'description' => 'Risk has been identified and recorded',
                'color_code' => '#ffc107',
                'order_index' => 1,
                'workflow_step' => 1,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Under Assessment',
                'code' => 'ASSESSING',
                'description' => 'Risk is currently being assessed',
                'color_code' => '#17a2b8',
                'order_index' => 2,
                'workflow_step' => 2,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Under Evaluation',
                'code' => 'EVALUATING',
                'description' => 'Risk is being evaluated for treatment requirements',
                'color_code' => '#6f42c1',
                'order_index' => 3,
                'workflow_step' => 3,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Treatment Planning',
                'code' => 'TREATMENT_PLANNING',
                'description' => 'Treatment plans are being developed',
                'color_code' => '#fd7e14',
                'order_index' => 4,
                'workflow_step' => 4,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Treatment Implementation',
                'code' => 'TREATMENT_IMPLEMENTATION',
                'description' => 'Treatment plans are being implemented',
                'color_code' => '#20c997',
                'order_index' => 5,
                'workflow_step' => 5,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Under Monitoring',
                'code' => 'MONITORING',
                'description' => 'Risk is being monitored and reviewed',
                'color_code' => '#0dcaf0',
                'order_index' => 6,
                'workflow_step' => 6,
                'is_active' => true,
                'company_id' => 0,
            ],
            [
                'name' => 'Closed',
                'code' => 'CLOSED',
                'description' => 'Risk has been closed',
                'color_code' => '#28a745',
                'order_index' => 7,
                'workflow_step' => 7,
                'is_active' => true,
                'company_id' => 0,
            ],
        ];

        foreach ($statuses as $status) {
            $existingStatus = RiskStatus::withTrashed()
                ->where('code', $status['code'])
                ->first();

            if ($existingStatus) {
                if ($existingStatus->trashed()) {
                    $existingStatus->restore();
                }
                $existingStatus->update($status);
            } else {
                RiskStatus::create($status);
            }
        }
    }
}

