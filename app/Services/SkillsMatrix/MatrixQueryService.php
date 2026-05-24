<?php

namespace App\Services\SkillsMatrix;

use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillMarixRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\Models\SkillsMatrix\SkillsMatrix;
use App\ModulePreConfigs;
use Illuminate\Support\Collection;

class MatrixQueryService
{
    public function activeMatrices(): Collection
    {
        return SkillsMatrix::query()
            ->where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function matrixWithDepartment(string $matrixId): ?SkillsMatrix
    {
        return SkillsMatrix::query()
            ->where('skillsmatrices.id', $matrixId)
            ->withDepartmentName()
            ->first();
    }

    /**
     * @return Collection<int, SkillMarixRole>
     */
    public function matrixRoles(string $matrixId): Collection
    {
        return SkillMarixRole::with('jobdescription')
            ->where('skills_matrix_id', $matrixId)
            ->orderBy('job_description_id')
            ->get();
    }

    /**
     * @return Collection<int, SkillMatrixDetails>
     */
    public function matrixDetails(string $matrixId): Collection
    {
        return SkillMatrixDetails::with([
            'competencyarea',
            'competencytype',
            'competencydescription',
            'roles.role',
            'roles.proficiency',
        ])
            ->where('skill_matrix_id', $matrixId)
            ->get();
    }

    public function proficiencies(): Collection
    {
        return ModulePreConfigs::query()
            ->where('type', 'Proficiency')
            ->where('module', 'Skills-Matrix')
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->selectRaw('id, color, code, description, level')
            ->orderBy('code')
            ->get();
    }

    public function trainingProficiencies(): Collection
    {
        return ModulePreConfigs::query()
            ->where('type', 'Training')
            ->where('module', 'Skills-Matrix')
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->selectRaw('id, color, code, description')
            ->orderBy('code')
            ->get();
    }

    public function capabilityWithRelations(string $capabilityId): ?CapabilityMatrix
    {
        return CapabilityMatrix::with([
            'skillmatrix',
            'creator',
            'roles.user',
            'roles.jobdescription',
        ])->find($capabilityId);
    }

    /**
     * @return Collection<int, SkillMatrixDetails>
     */
    public function capabilityCompetencies(CapabilityMatrix $capability): Collection
    {
        return SkillMatrixDetails::with([
            'competencyarea',
            'competencytype',
            'competencydescription',
            'capabilityusers',
        ])
            ->where('skill_matrix_id', $capability->matrix_id)
            ->get();
    }

    /**
     * @return array<string, array<int, array{user_id: mixed, proficiency_id: mixed, id: mixed}>>
     */
    public function capabilityUserProficiencyMap(Collection $competencies): array
    {
        return $competencies->mapWithKeys(function ($competency) {
            return [
                $competency->id => $competency->capabilityusers->map(fn ($user) => [
                    'user_id' => $user->user_id,
                    'proficiency_id' => $user->proficiency_id,
                    'id' => $user->id,
                ])->toArray(),
            ];
        })->toArray();
    }

    /**
     * @return Collection<int, CapabilityMatrixRoles>
     */
    public function activeCapabilityRoles(string $capabilityId): Collection
    {
        return CapabilityMatrixRoles::with(['user', 'jobdescription'])
            ->where('capability_id', $capabilityId)
            ->whereNull('deleted_at')
            ->get();
    }

    public function staffOnCapabilityMatrices(): Collection
    {
        return CapabilityMatrixRoles::query()
            ->with(['user', 'jobdescription', 'capability'])
            ->whereNull('deleted_at')
            ->whereHas('capability', fn ($q) => $q->where('status', 1)->whereNull('deleted_at'))
            ->get()
            ->unique('user_id')
            ->values();
    }

    public function userCapabilityDetails(string $userId): Collection
    {
        return CapabilityMatrixDetail::with(['competency.competencyarea', 'proficiency'])
            ->where('user_id', $userId)
            ->get();
    }
}
