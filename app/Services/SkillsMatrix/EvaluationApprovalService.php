<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\TrainingDetail;
use App\Models\SkillsMatrix\TrainingEvaluation;
use App\Models\SkillsMatrix\TrainingHeader;
use App\Models\SkillsMatrix\TrainingPlannerDetails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EvaluationApprovalService
{
    public function approve(string $evaluationId): void
    {
        $evaluation = TrainingEvaluation::findOrFail($evaluationId);

        if ($evaluation->status !== TrainingEvaluation::STATUS_SUBMITTED) {
            throw new \RuntimeException('Only submitted evaluations can be approved.');
        }

        $evaluation->update([
            'status' => TrainingEvaluation::STATUS_APPROVED,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $this->applyToCapability($evaluation);
    }

    public function reject(string $evaluationId, ?string $notes = null): void
    {
        $evaluation = TrainingEvaluation::findOrFail($evaluationId);

        $evaluation->update([
            'status' => TrainingEvaluation::STATUS_REJECTED,
            'rejected_by' => Auth::id(),
            'rejected_at' => now(),
            'score_notes' => trim(($evaluation->score_notes ?? '')."\n".($notes ?? '')),
        ]);
    }

    public function applyToCapability(TrainingEvaluation $evaluation): void
    {
        if (! $evaluation->competency_id || ! $evaluation->proposed_proficiency_id) {
            return;
        }

        $plannerDetail = TrainingPlannerDetails::find($evaluation->training_planner_detail_id);
        if (! $plannerDetail) {
            return;
        }

        $capabilityId = $this->resolveCapabilityId($plannerDetail);
        if (! $capabilityId) {
            return;
        }

        $existing = CapabilityMatrixDetail::query()
            ->where('capability_id', $capabilityId)
            ->where('competency_id', $evaluation->competency_id)
            ->where('user_id', $evaluation->user_id)
            ->first();

        if ($existing) {
            $existing->update(['proficiency_id' => $evaluation->proposed_proficiency_id]);

            return;
        }

        CapabilityMatrixDetail::create([
            'id' => (string) Str::uuid(),
            'capability_id' => $capabilityId,
            'competency_id' => $evaluation->competency_id,
            'user_id' => $evaluation->user_id,
            'proficiency_id' => $evaluation->proposed_proficiency_id,
        ]);
    }

    protected function resolveCapabilityId(TrainingPlannerDetails $plannerDetail): ?string
    {
        if (! $plannerDetail->training_need_detail_id) {
            return null;
        }

        $trainingDetail = TrainingDetail::find($plannerDetail->training_need_detail_id);
        if (! $trainingDetail) {
            return null;
        }

        $header = TrainingHeader::find($trainingDetail->training_header_id);

        return $header?->capability_id ? (string) $header->capability_id : null;
    }
}
