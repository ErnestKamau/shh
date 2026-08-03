<?php

namespace App\Livewire\GroupedWorksheets;

use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use Livewire\Component;
use Livewire\WithPagination;

class GroupedWorksheetHolderManager extends Component
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

    public string $document_control_no = '';

    public string $revision = '';

    public ?string $issue_date = null;

    /** classic | phased */
    public string $pipeline_mode = 'classic';

    public bool $results_capture_enabled = true;

    public string $results_capture_label = 'Results capture';

    public bool $results_capture_required = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'document_control_no' => 'nullable|string|max:255',
            'revision' => 'nullable|string|max:255',
            'issue_date' => 'nullable|date',
            'pipeline_mode' => 'required|in:classic,phased',
            'results_capture_enabled' => 'boolean',
            'results_capture_label' => 'nullable|string|max:255',
            'results_capture_required' => 'boolean',
        ];
    }

    public function render()
    {
        $query = GroupedWorksheetHolder::query()->withCount('items');

        if ($this->search !== '') {
            $this->applyCaseInsensitiveSearch($query, ['name', 'description'], (string) $this->search);
        }

        $holders = $query->latest()->paginate($this->perPage);

        return view('livewire.grouped-worksheets.grouped-worksheet-holder-manager', [
            'holders' => $holders,
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
        $holder = GroupedWorksheetHolder::findOrFail($id);
        $this->editingId = $id;
        $this->name = $holder->name;
        $this->description = $holder->description ?? '';
        $this->is_active = $holder->is_active;
        $this->document_control_no = $holder->document_control_no ?? '';
        $this->revision = $holder->revision ?? '';
        $this->issue_date = $holder->issue_date?->format('Y-m-d');
        $this->pipeline_mode = (string) ($holder->getSettingValue('pipeline_mode', 'classic') ?: 'classic');
        $results = is_array($holder->getSettingValue('results_capture'))
            ? $holder->getSettingValue('results_capture')
            : [];
        $this->results_capture_enabled = (bool) ($results['enabled'] ?? true);
        $this->results_capture_label = (string) ($results['label'] ?? 'Results capture');
        $this->results_capture_required = (bool) ($results['required'] ?? true);
        $this->showEditModal = true;
    }

    public function save(): void
    {
        $this->validate();

        GroupedWorksheetHolder::create($this->holderPayload());

        $this->showCreateModal = false;
        $this->resetForm();
        session()->flash('message', 'Grouped worksheet created successfully.');
    }

    public function update(): void
    {
        $this->validate();

        GroupedWorksheetHolder::findOrFail($this->editingId)->update($this->holderPayload());

        $this->showEditModal = false;
        $this->resetForm();
        session()->flash('message', 'Grouped worksheet updated successfully.');
    }

    public function toggleActive(string $id): void
    {
        $holder = GroupedWorksheetHolder::findOrFail($id);
        $holder->is_active = ! $holder->is_active;
        $holder->save();
        session()->flash('message', 'Status updated.');
    }

    public function delete(string $id): void
    {
        GroupedWorksheetHolder::findOrFail($id)->delete();
        session()->flash('message', 'Grouped worksheet deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function holderPayload(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'is_active' => $this->is_active,
            'document_control_no' => $this->document_control_no ?: null,
            'revision' => $this->revision ?: null,
            'issue_date' => $this->issue_date ?: null,
            'settings' => $this->settingsPayload(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function settingsPayload(): array
    {
        $existing = [];
        if ($this->editingId) {
            $existing = GroupedWorksheetHolder::query()->find($this->editingId)?->settings ?? [];
        }

        if (! is_array($existing)) {
            $existing = [];
        }

        return array_merge($existing, [
            'pipeline_mode' => $this->pipeline_mode ?: 'classic',
            'results_capture' => [
                'enabled' => $this->results_capture_enabled,
                'label' => trim($this->results_capture_label) !== ''
                    ? trim($this->results_capture_label)
                    : 'Results capture',
                'required' => $this->results_capture_required,
            ],
        ]);
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
        $this->pipeline_mode = 'classic';
        $this->results_capture_enabled = true;
        $this->results_capture_label = 'Results capture';
        $this->results_capture_required = true;
    }
}
