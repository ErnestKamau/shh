<?php

namespace App\Livewire\HybridWorksheets;

use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\HybridWorksheets\HybridWorksheetVersion;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class HybridWorksheetManager extends Component
{
    use AppliesCaseInsensitiveSearch;
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function render()
    {
        $query = HybridWorksheet::with(['activeVersion']);

        if ($this->search !== '') {
            $this->applyCaseInsensitiveSearch($query, ['name', 'description'], (string) $this->search);
        }

        $worksheets = $query->latest()->paginate($this->perPage);

        return view('livewire.hybrid-worksheets.hybrid-worksheet-manager', [
            'worksheets' => $worksheets,
        ]);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function edit(string $id): void
    {
        $worksheet = HybridWorksheet::findOrFail($id);
        $this->editingId = $id;
        $this->name = $worksheet->name;
        $this->description = $worksheet->description ?? '';
        $this->is_active = $worksheet->is_active;
        $this->showEditModal = true;
    }

    public function save()
    {
        $this->validate();

        $worksheet = HybridWorksheet::create([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ]);

        $version = HybridWorksheetVersion::create([
            'hybrid_worksheet_id' => $worksheet->id,
            'version_number' => 1,
            'is_active' => true,
            'created_by' => Auth::id() ? (string) Auth::id() : null,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Hybrid worksheet created. Add blocks next.');

        return redirect()->route('formulars.hybrid-worksheets.edit', [
            'hybridWorksheet' => $worksheet->id,
            'hybridWorksheetVersion' => $version->id,
        ]);
    }

    public function update(): void
    {
        $this->validate();

        HybridWorksheet::findOrFail($this->editingId)->update([
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Hybrid worksheet updated.');
    }

    public function toggleActive(string $id): void
    {
        $worksheet = HybridWorksheet::findOrFail($id);
        $worksheet->is_active = ! $worksheet->is_active;
        $worksheet->save();
        session()->flash('message', 'Status updated.');
    }

    public function delete(string $id): void
    {
        HybridWorksheet::findOrFail($id)->delete();
        session()->flash('message', 'Hybrid worksheet deleted.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
    }
}
