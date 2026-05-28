<?php

namespace App\Livewire\Equipment\Depreciation;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Models\Equipments\Depreciation\DepreciationMethod;
use Livewire\Component;
use Livewire\WithPagination;

class DepreciationMethodManager extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    /** @var array<int, int> */
    public array $perPageOptions = [25, 50, 75, 100];

    public bool $showModal = false;

    public ?string $editId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public ?string $default_rate = null;

    public bool $is_active = true;

    protected $paginationTheme = 'bootstrap';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(string $id): void
    {
        $method = DepreciationMethod::query()->findOrFail($id);
        $this->editId = $id;
        $this->code = $method->code instanceof DepreciationMethodCode ? $method->code->value : (string) $method->code;
        $this->name = $method->name;
        $this->description = $method->description ?? '';
        $this->default_rate = $method->default_rate !== null ? (string) $method->default_rate : null;
        $this->is_active = (bool) $method->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => 'required|string|max:50|unique:depreciation_methods,code,' . ($this->editId ?? 'NULL') . ',id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_rate' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $payload = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'default_rate' => $this->default_rate,
            'is_active' => $this->is_active,
        ];

        if ($this->editId) {
            DepreciationMethod::query()->where('id', $this->editId)->update($payload);
        } else {
            DepreciationMethod::query()->create($payload);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Depreciation method saved.');
    }

    public function resetForm(): void
    {
        $this->editId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->default_rate = null;
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        $methods = DepreciationMethod::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('code', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.equipment.depreciation.depreciation-method-manager', [
            'methods' => $methods,
        ]);
    }
}
