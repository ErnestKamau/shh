<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\TrainingDetail;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingHeaderStaff;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use App\Models\SkillsMatrix\TrainingPlannerHeader;
use App\User;
use Illuminate\Support\Collection;

class TrainingNeedsService
{
    /**
     * @param  array<int, string>|null  $capabilityRoleIds  CapabilityMatrixRoles ids; null = all active roles
     */
    public function createTrainingNeedForCapability(
        CapabilityMatrix $capability,
        string $name,
        User $creator,
        ?array $capabilityRoleIds = null,
    ): TrainingHeader {
        $roleIds = $capabilityRoleIds ?? CapabilityMatrixRoles::query()
            ->where('capability_id', $capability->id)
            ->whereNull('deleted_at')
            ->pluck('id')
            ->all();

        $header = TrainingHeader::create([
            'name' => $name,
            'capability_id' => $capability->id,
            'created_by' => $creator->id,
        ]);

        foreach ($roleIds as $roleId) {
            TrainingHeaderStaff::create([
                'training_header_id' => $header->id,
                'capability_matrix_role_id' => $roleId,
            ]);
        }

        $details = CapabilityMatrixDetail::with(['competency', 'proficiency'])
            ->where('capability_id', $capability->id)
            ->whereNull('deleted_at')
            ->get();

        foreach ($details as $detail) {
            $required = $detail->skillproficiency();
            if (! $required || ! $detail->proficiency) {
                continue;
            }
            if ((int) $detail->proficiency->code < (int) $required->proficiency->code) {
                TrainingDetail::create([
                    'training_header_id' => $header->id,
                    'capability_detail_id' => $detail->id,
                    'skill_matrix_role_proficiency_id' => $required->id,
                    'require_training' => 1,
                ]);
            }
        }

        return $header;
    }

    public function createTrainingPlanFromNeed(
        TrainingHeader $trainingNeed,
        string $planName,
        User $creator,
    ): TrainingPlannerHeader {
        $plan = TrainingPlannerHeader::create([
            'name' => $planName,
            'training_need_header_id' => $trainingNeed->id,
            'created_by' => $creator->id,
        ]);

        $details = TrainingDetail::where('training_header_id', $trainingNeed->id)->get();
        foreach ($details as $detail) {
            TrainingPlannerDetails::create([
                'training_plan_header_id' => $plan->id,
                'training_need_detail_id' => $detail->id,
            ]);
        }

        return $plan;
    }
}
