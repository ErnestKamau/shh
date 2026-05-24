<?php

namespace App\Livewire\SkillsMatrix;

use App\Livewire\SkillsMatrix\Concerns\HasMatrixFlash;
use App\Livewire\SkillsMatrix\Concerns\HasMatrixListFilters;
use App\Models\SkillsMatrix\InductionChecklistItem;
use App\ModulePreConfigs;
use Livewire\Component;

class EducationRequirementsManager extends Component
{
    use HasMatrixFlash;
    use HasMatrixListFilters;

    public bool $showChecklistModal = false;

    public ?string $editingItemId = null;

    public string $checklistName = '';

    public function openCreateChecklist(): void
    {
        $this->editingItemId = null;
        $this->checklistName = '';
        $this->showChecklistModal = true;
    }

    public function openEditChecklist(string $id): void
    {
        $item = InductionChecklistItem::findOrFail($id);
        $this->editingItemId = $item->id;
        $this->checklistName = $item->name;
        $this->showChecklistModal = true;
    }

    public function saveChecklistItem(): void
    {
        $this->validate(['checklistName' => 'required|string|max:255']);

        $item = $this->editingItemId
            ? InductionChecklistItem::findOrFail($this->editingItemId)
            : new InductionChecklistItem;

        $item->name = $this->checklistName;
        $item->inventory_location_id = getCurrentUserLocation()->id;
        $item->sort_order = $item->sort_order ?? (InductionChecklistItem::max('sort_order') + 1);
        $item->active = true;
        $item->save();

        $this->showChecklistModal = false;
        $this->flash('Checklist item saved.');
    }

    public function deleteChecklistItem(string $id): void
    {
        InductionChecklistItem::where('id', $id)->update(['active' => false]);
        $this->flash('Checklist item removed.');
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
        $this->resetPage('rolesPage');
        $this->resetPage('checklistPage');
    }

    public function render()
    {
        $rolesQuery = ModulePreConfigs::query()
            ->where('type', 'Job Description')
            ->where('module', 'Skills-Matrix')
            ->where('inventory_location_id', getCurrentUserLocation()->id)
            ->orderBy('name');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $rolesQuery->where(function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $roles = $rolesQuery->paginate($this->perPage, pageName: 'rolesPage');

        $checklistQuery = InductionChecklistItem::query()
            ->where('active', true)
            ->where(function ($q): void {
                $q->whereNull('inventory_location_id')
                    ->orWhere('inventory_location_id', getCurrentUserLocation()->id);
            })
            ->orderBy('sort_order');

        if ($this->search !== '') {
            $checklistQuery->where('name', 'like', '%'.$this->search.'%');
        }

        $checklist = $checklistQuery->paginate($this->perPage, pageName: 'checklistPage');

        return view('livewire.skills-matrix.education-requirements-manager', compact('roles', 'checklist'));
    }
}
