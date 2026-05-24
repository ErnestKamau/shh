<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Models\SkillsMatrix\SkillMatrixDetailRole;
use App\Models\SkillsMatrix\SkillMatrixDetails;
use App\ModulePreConfigs;
use App\Services\SkillsMatrix\MatrixQueryService;
use Livewire\Component;

class SkillsMatrixGrid extends Component
{
    use HasMatrixFlash;

    public string $matrixId;

    public ?string $editingDetailRoleId = null;

    public string $editProficiencyId = '';

    public function mount(string $matrixId): void
    {
        $this->matrixId = $matrixId;
    }

    public function openEditCell(string $detailRoleId, string $proficiencyId): void
    {
        $this->editingDetailRoleId = $detailRoleId;
        $this->editProficiencyId = $proficiencyId;
    }

    public function saveCellProficiency(): void
    {
        $this->authorize('skills-matrix.components.skills-matrix.edit');
        SkillMatrixDetailRole::findOrFail($this->editingDetailRoleId)
            ->update(['proficiency_id' => $this->editProficiencyId]);
        $this->editingDetailRoleId = null;
        $this->flash('Proficiency updated.');
    }

    public function levelClass(?int $code): string
    {
        if ($code === null) {
            return 'gap-neutral';
        }
        if ($code >= 3) {
            return 'level-3';
        }
        if ($code >= 2) {
            return 'level-2';
        }

        return 'level-1';
    }

    public function render(MatrixQueryService $query)
    {
        $matrix = $query->matrixWithDepartment($this->matrixId);
        $roles = $query->matrixRoles($this->matrixId);
        $details = $query->matrixDetails($this->matrixId);
        $proficiencies = $query->proficiencies();

        $grouped = $details->groupBy(fn ($d) => $d->competencyarea->description ?? 'Other');

        return view('livewire.skills-matrix.skills-matrix-grid', compact('matrix', 'roles', 'grouped', 'proficiencies'));
    }
}
