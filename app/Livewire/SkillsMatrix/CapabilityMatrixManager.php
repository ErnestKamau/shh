<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\CapabilityMatrix;
use App\Models\SkillsMatrix\CapabilityMatrixRoles;
use App\Models\SkillsMatrix\SkillsMatrix;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CapabilityMatrixManager extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public bool $showModal = false;

    public string $name = '';

    public string $skillsMatrixId = '';

    /** @var array<int, array<string, mixed>> */
    public array $staffRows = [];

    public function openCreate(): void
    {
        $this->name = '';
        $this->skillsMatrixId = '';
        $this->staffRows = [['user_id' => '', 'skill_matrix_role_id' => '', 'jobdescription_id' => '', 'code' => '']];
        $this->showModal = true;
    }

    public function addStaffRow(): void
    {
        $this->staffRows[] = ['user_id' => '', 'skill_matrix_role_id' => '', 'jobdescription_id' => '', 'code' => ''];
    }

    public function saveCapability(): void
    {
        $this->authorize('skills-matrix.components.capability.add');
        $this->validate(['name' => 'required', 'skillsMatrixId' => 'required']);

        $matrix = new CapabilityMatrix;
        $matrix->name = $this->name;
        $matrix->matrix_id = $this->skillsMatrixId;
        $matrix->created_by = Auth::id();
        $matrix->status = 1;
        $matrix->save();

        foreach ($this->staffRows as $row) {
            if (empty($row['user_id'])) {
                continue;
            }
            CapabilityMatrixRoles::create([
                'capability_id' => $matrix->id,
                'skill_matrix_role_id' => $row['skill_matrix_role_id'],
                'role_id' => $row['jobdescription_id'],
                'user_id' => $row['user_id'],
                'code' => $row['code'] ?? '',
            ]);
        }

        $this->showModal = false;
        $this->flash('Capability matrix created.');
    }

    public function render()
    {
        $skillmatrixs = SkillsMatrix::where('status', 1)->orderBy('name')->get();

        $query = CapabilityMatrix::query()
            ->with(['skillmatrix', 'creator'])
            ->withCount('grouproles')
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->orderBy('name');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhereHas('skillmatrix', fn ($sq) => $sq->where('name', 'like', $term));
            });
        }

        $capabilities = $query->paginate($this->perPage);

        return view('livewire.skills-matrix.capability-matrix-manager', compact('skillmatrixs', 'capabilities'));
    }
}
