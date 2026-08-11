<?php

namespace App\Livewire\Lab;

use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\ParameterGroup;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ParameterGroupManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public int $perPage = 10;

    public bool $showModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public int $sortOrder = 0;

    public bool $active = true;

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('parameter_groups', 'name')->ignore($this->editingId),
            ],
            'sortOrder' => 'nullable|integer|min:0',
            'active' => 'boolean',
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function showCreateModal(): void
    {
        $this->resetForm();
        $nextSort = (int) ParameterGroup::query()->max('sort_order');
        $this->sortOrder = $nextSort + 1;
        $this->showModal = true;
    }

    public function showEditModal(string $id): void
    {
        $this->resetForm();
        $group = ParameterGroup::findOrFail($id);
        $this->editingId = $group->id;
        $this->name = $group->name;
        $this->sortOrder = (int) $group->sort_order;
        $this->active = (bool) $group->active;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $group = ParameterGroup::findOrFail($this->editingId);
            $group->update([
                'name' => $this->name,
                'sort_order' => $this->sortOrder,
                'active' => $this->active,
            ]);
            session()->flash('success', 'Parameter group updated successfully.');
        } else {
            ParameterGroup::create([
                'name' => $this->name,
                'sort_order' => $this->sortOrder,
                'active' => $this->active,
            ]);
            session()->flash('success', 'Parameter group created successfully.');
        }

        $this->closeModal();
    }

    public function delete(string $id): void
    {
        $group = ParameterGroup::find($id);

        if (! $group) {
            return;
        }

        if ($group->isInUse()) {
            session()->flash('error', 'This parameter group is assigned to one or more analysis parameters. Deactivate it instead of deleting.');

            return;
        }

        $group->delete();
        session()->flash('success', 'Parameter group deleted successfully.');
    }

    public function resetForm(): void
    {
        $this->name = '';
        $this->sortOrder = 0;
        $this->active = true;
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = ParameterGroup::query()->withCount('analysisElements');

        if ($this->search !== '') {
            $this->applyCaseInsensitiveSearch($query, ['name'], $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active');
        }

        return view('livewire.lab.parameter-group-manager', [
            'groups' => $query->orderBy('sort_order')->orderBy('name')->paginate($this->perPage),
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
