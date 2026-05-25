<?php

namespace App\Livewire\LogEntryWorksheets;

use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use Livewire\Component;
use Livewire\WithPagination;

class LogEntryWorksheetManager extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 25;

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?string $editingId = null;

    public string $name = '';

    public string $description = '';

    public bool $is_active = true;

    public string $document_control_no = '';

    public string $revision = '';

    public ?string $issue_date = null;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'document_control_no' => 'nullable|string|max:255',
            'revision' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
        ];
    }

    public function render()
    {
        $query = LogEntryWorksheet::query()->withCount(['columns', 'mandatoryFields']);

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            });
        }

        $worksheets = $query->latest()->paginate($this->perPage);

        return view('livewire.log-entry-worksheets.log-entry-worksheet-manager', [
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
        $this->resetForm();
        $worksheet = LogEntryWorksheet::findOrFail($id);
        $this->editingId = $id;
        $this->name = $worksheet->name;
        $this->description = $worksheet->description ?? '';
        $this->is_active = $worksheet->is_active;
        $this->document_control_no = $worksheet->document_control_no ?? '';
        $this->revision = $worksheet->revision ?? '';
        $this->issue_date = $worksheet->issue_date?->format('Y-m-d');
        $this->showEditModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $worksheet = LogEntryWorksheet::create($this->worksheetPayload());

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Log entry worksheet created.');
        $this->redirect(route('formulars.log-entry-worksheets.edit', $worksheet->id), navigate: true);
    }

    public function update(): void
    {
        $this->validate();

        LogEntryWorksheet::findOrFail($this->editingId)->update($this->worksheetPayload());

        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Log entry worksheet updated.');
    }

    public function toggleActive(string $id): void
    {
        $worksheet = LogEntryWorksheet::findOrFail($id);
        $worksheet->is_active = ! $worksheet->is_active;
        $worksheet->save();
        session()->flash('message', 'Status updated.');
    }

    public function delete(string $id): void
    {
        LogEntryWorksheet::findOrFail($id)->delete();
        session()->flash('message', 'Log entry worksheet deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function worksheetPayload(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'document_control_no' => $this->document_control_no ?: null,
            'revision' => $this->revision ?: null,
            'issue_date' => $this->issue_date ?: null,
        ];
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->is_active = true;
        $this->document_control_no = '';
        $this->revision = '';
        $this->issue_date = null;
    }
}
