<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use Illuminate\Support\Collection;

class GapAnalysisService
{
    public function __construct(
        protected MatrixQueryService $matrixQuery,
    ) {}

    /**
     * @return array{
     *   critical: int,
     *   minor: int,
     *   exceeding: int,
     *   rows: array<int, array{competency: string, area: string, gaps: array<int, array{gap: int|null, label: string, class: string}>}>
     * }
     */
    public function analyzeCapability(CapabilityMatrix $capability, array $roleIds = []): array
    {
        $roles = $this->matrixQuery->activeCapabilityRoles($capability->id);
        if ($roleIds !== []) {
            $roles = $roles->whereIn('id', $roleIds);
        }

        $competencies = $this->matrixQuery->capabilityCompetencies($capability);
        $critical = 0;
        $minor = 0;
        $exceeding = 0;
        $rows = [];

        foreach ($competencies as $competency) {
            $gaps = [];
            foreach ($roles as $role) {
                $required = $this->requiredProficiencyCode($competency, $role->skill_matrix_role_id);
                $actual = $this->actualProficiencyCode($competency->id, $role->user_id);
                $gap = ($actual !== null && $required !== null) ? $actual - $required : null;
                $gaps[] = [
                    'gap' => $gap,
                    'label' => $this->gapLabel($gap),
                    'class' => $this->gapClass($gap),
                    'role_name' => $role->jobdescription->name ?? '',
                ];
                if ($gap !== null) {
                    if ($gap < -1) {
                        $critical++;
                    } elseif ($gap < 0) {
                        $minor++;
                    } elseif ($gap > 0) {
                        $exceeding++;
                    }
                }
            }
            $rows[] = [
                'competency' => $competency->competencydescription->description ?? '',
                'area' => $competency->competencyarea->description ?? '',
                'gaps' => $gaps,
            ];
        }

        return [
            'critical' => $critical,
            'minor' => $minor,
            'exceeding' => $exceeding,
            'rows' => $rows,
        ];
    }

    protected function requiredProficiencyCode(SkillMatrixDetails $competency, string $matrixRoleId): ?int
    {
        $roleProf = SkillMatrixDetailRole::with('proficiency')
            ->where('matrix_detail_id', $competency->id)
            ->where('matrix_role_id', $matrixRoleId)
            ->first();

        return $roleProf?->proficiency ? (int) $roleProf->proficiency->code : null;
    }

    protected function actualProficiencyCode(string $competencyId, string $userId): ?int
    {
        $detail = CapabilityMatrixDetail::with('proficiency')
            ->where('competency_id', $competencyId)
            ->where('user_id', $userId)
            ->first();

        return $detail?->proficiency ? (int) $detail->proficiency->code : null;
    }

    protected function gapLabel(?int $gap): string
    {
        if ($gap === null) {
            return '—';
        }
        if ($gap === 0) {
            return '✓';
        }
        if ($gap > 0) {
            return '+'.$gap;
        }

        return (string) $gap;
    }

    protected function gapClass(?int $gap): string
    {
        if ($gap === null) {
            return 'gap-neutral';
        }
        if ($gap < 0) {
            return 'gap-train';
        }
        if ($gap === 0) {
            return 'gap-ok';
        }

        return 'gap-exceed';
    }
}
