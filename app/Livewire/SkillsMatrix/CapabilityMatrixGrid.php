<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Models\SkillsMatrix\CapabilityMatrixDetail;
use App\Services\SkillsMatrix\MatrixQueryService;
use Illuminate\Support\Str;
use Livewire\Component;

class CapabilityMatrixGrid extends Component
{
    use HasMatrixFlash;

    public string $capabilityId;

    /** @var array<int, string> */
    public array $selectedRoleIds = [];

    public ?string $editingDetailId = null;

    public string $editProficiencyId = '';

    public string $editCompetencyId = '';

    public string $editUserId = '';

    public string $editSkillMatrixRoleId = '';

    public function mount(string $capabilityId): void
    {
        $this->capabilityId = $capabilityId;
    }

    public function updatedSelectedRoleIds(): void
    {
        // filter refresh
    }

    public function openEditCell(
        string $detailId,
        string $proficiencyId,
        string $competencyId,
        string $userId,
        string $skillMatrixRoleId,
    ): void {
        $this->editingDetailId = $detailId !== '' ? $detailId : null;
        $this->editProficiencyId = $proficiencyId;
        $this->editCompetencyId = $competencyId;
        $this->editUserId = $userId;
        $this->editSkillMatrixRoleId = $skillMatrixRoleId;
    }

    public function saveCellProficiency(): void
    {
        $this->authorize('skills-matrix.components.capability.edit');

        $detail = $this->editingDetailId
            ? CapabilityMatrixDetail::findOrFail($this->editingDetailId)
            : new CapabilityMatrixDetail;

        if (! $detail->exists) {
            $detail->id = (string) Str::uuid();
            $detail->capability_id = $this->capabilityId;
            $detail->competency_id = $this->editCompetencyId;
            $detail->user_id = $this->editUserId;
            $detail->skill_matrix_role_id = $this->editSkillMatrixRoleId;
        }

        $detail->proficiency_id = $this->editProficiencyId;
        $detail->save();

        $this->editingDetailId = null;
        $this->flash('Capability proficiency saved.');
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
        $capability = $query->capabilityWithRelations($this->capabilityId);
        $roles = $query->activeCapabilityRoles($this->capabilityId);
        if ($this->selectedRoleIds !== []) {
            $roles = $roles->whereIn('id', $this->selectedRoleIds);
        }
        $competencies = $query->capabilityCompetencies($capability);
        $proficiencies = $query->proficiencies();
        $userMap = $query->capabilityUserProficiencyMap($competencies);
        $grouped = $competencies->groupBy(fn ($d) => $d->competencyarea->description ?? 'Other');

        return view('livewire.skills-matrix.capability-matrix-grid', compact(
            'capability', 'roles', 'grouped', 'proficiencies', 'userMap'
        ));
    }
}
