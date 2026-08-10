<?php

namespace App\Livewire\Lab;

use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\TypeOfAnalysis;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TypeOfAnalysisManager extends Component
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
                Rule::unique('types_of_analysis', 'name')->ignore($this->editingId),
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
        $nextSort = (int) TypeOfAnalysis::query()->max('sort_order');
        $this->sortOrder = $nextSort + 1;
        $this->showModal = true;
    }

    public function showEditModal(string $id): void
    {
        $this->resetForm();
        $type = TypeOfAnalysis::findOrFail($id);
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->sortOrder = (int) $type->sort_order;
        $this->active = (bool) $type->active;
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
            $type = TypeOfAnalysis::findOrFail($this->editingId);
            $type->update([
                'name' => $this->name,
                'sort_order' => $this->sortOrder,
                'active' => $this->active,
            ]);
            session()->flash('success', 'Type of Analysis updated successfully.');
        } else {
            TypeOfAnalysis::create([
                'name' => $this->name,
                'sort_order' => $this->sortOrder,
                'active' => $this->active,
            ]);
            session()->flash('success', 'Type of Analysis created successfully.');
        }

        $this->closeModal();
    }

    public function delete(string $id): void
    {
        $type = TypeOfAnalysis::find($id);

        if (! $type) {
            return;
        }

        if ($type->isInUse()) {
            session()->flash('error', 'This type is assigned to one or more analysis parameters. Deactivate it instead of deleting.');

            return;
        }

        $type->delete();
        session()->flash('success', 'Type of Analysis deleted successfully.');
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
        $query = TypeOfAnalysis::query()->withCount('analysisElements');

        if ($this->search !== '') {
            $this->applyCaseInsensitiveSearch($query, ['name'], $this->search);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter === 'active');
        }

        return view('livewire.lab.type-of-analysis-manager', [
            'types' => $query->orderBy('sort_order')->orderBy('name')->paginate($this->perPage),
            'perPageOptions' => [10, 25, 50, 100],
        ]);
    }
}
