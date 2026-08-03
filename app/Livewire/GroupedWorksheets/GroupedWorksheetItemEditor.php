<?php

namespace App\Livewire\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\LogEntryWorksheets\LogEntryWorksheet;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Services\GroupedWorksheets\GroupedWorksheetCapturePreviewService;
use App\Services\GroupedWorksheets\GroupedWorksheetPipelineStages;
use App\Services\GroupedWorksheets\GroupedWorksheetReferenceValidator;
use Livewire\Component;

class GroupedWorksheetItemEditor extends Component
{
    use AppliesCaseInsensitiveSearch;

    public GroupedWorksheetHolder $holder;

    public bool $showAddModal = false;

    public bool $showEditModal = false;

    public ?string $editingItemId = null;

    public string $label = '';

    public string $description = '';

    public string $item_type = 'procedure';

    public string $reference_id = '';

    public bool $is_required = true;

    public string $referenceSearch = '';

    public bool $showReferenceDropdown = false;

    public bool $showItemTypeDropdown = false;

    public string $message = '';

    public string $messageType = '';

    public ?string $selectedItemId = null;

    /** Phase config (StageHeader items). */
    public ?int $config_stage_order = null;

    public string $config_procedure_worksheet_id = '';

    public string $config_section_key = '';

    /** @var array<int, string> */
    public array $config_row_keys = [];

    public bool $config_show_config_fields = false;

    public string $procedureSearch = '';

    public bool $showProcedureDropdown = false;

    public function mount(GroupedWorksheetHolder $holder): void
    {
        $this->holder = $holder->load('items');
        $this->syncSelectedItem();
    }

    public function render()
    {
        $this->holder->load('items');
        $pipelineStages = app(GroupedWorksheetPipelineStages::class);

        return view('livewire.grouped-worksheets.grouped-worksheet-item-editor', [
            'items' => $this->holder->items,
            'itemTypeOptions' => GroupedWorksheetItemType::options(),
            'capturePreview' => $this->capturePreview,
            'virtualResultsItem' => $pipelineStages->virtualResultsCaptureItemForDisplay($this->holder),
            'matrixSections' => $this->matrixSectionsForSelectedProcedure(),
            'matrixRows' => $this->matrixRowsForSelectedSection(),
            'pipelineMode' => $this->holder->getSettingValue('pipeline_mode', 'classic'),
        ]);
    }

    public function getCapturePreviewProperty(): ?array
    {
        $item = $this->selectedItem;

        if (! $item) {
            return null;
        }

        return app(GroupedWorksheetCapturePreviewService::class)->previewForItem($item);
    }

    public function getSelectedItemProperty(): ?GroupedWorksheetItem
    {
        if ($this->selectedItemId === null) {
            return null;
        }

        return $this->holder->items->firstWhere('id', $this->selectedItemId);
    }

    public function selectPipelineStage(string $itemId): void
    {
        $this->selectedItemId = $itemId;
    }

    public function closeModal(): void
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->resetItemForm();
    }

    public function getFilteredReferenceOptionsProperty(): array
    {
        return $this->referenceOptions();
    }

    public function getFilteredProcedureOptionsProperty(): array
    {
        $search = $this->procedureSearch;

        return ProcedureWorksheet::query()
            ->where('is_active', true)
            ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
            ->orderBy('name')
            ->limit(30)
            ->get(['id', 'name', 'description', 'layout_settings'])
            ->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'description' => $r->description,
                'is_sectioned_matrix' => $r->isSectionedMatrix(),
            ])
            ->all();
    }

    public function getSelectedReferenceProperty(): ?object
    {
        if ($this->reference_id === '') {
            return null;
        }

        return match (GroupedWorksheetItemType::tryFrom($this->item_type)) {
            GroupedWorksheetItemType::Formula => Formula::find($this->reference_id),
            GroupedWorksheetItemType::Procedure => ProcedureWorksheet::find($this->reference_id),
            GroupedWorksheetItemType::StageHeader => StageHeader::find($this->reference_id),
            GroupedWorksheetItemType::HybridWorksheet => HybridWorksheet::find($this->reference_id),
            GroupedWorksheetItemType::LogEntryWorksheet => LogEntryWorksheet::find($this->reference_id),
            default => null,
        };
    }

    public function getSelectedProcedureProperty(): ?ProcedureWorksheet
    {
        if ($this->config_procedure_worksheet_id === '') {
            return null;
        }

        return ProcedureWorksheet::find($this->config_procedure_worksheet_id);
    }

    public function getSelectedItemTypeLabelProperty(): string
    {
        return GroupedWorksheetItemType::options()[$this->item_type] ?? '';
    }

    public function updatedReferenceSearch(): void
    {
        $this->showReferenceDropdown = $this->referenceSearch !== '';
    }

    public function updatedProcedureSearch(): void
    {
        $this->showProcedureDropdown = $this->procedureSearch !== '';
    }

    public function updatedConfigSectionKey(): void
    {
        $this->config_row_keys = [];
    }

    /**
     * @return array<int, array{id: string, name: string, description?: string|null}>
     */
    protected function referenceOptions(): array
    {
        $search = $this->referenceSearch;

        return match (GroupedWorksheetItemType::tryFrom($this->item_type)) {
            GroupedWorksheetItemType::Formula => Formula::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            GroupedWorksheetItemType::Procedure => ProcedureWorksheet::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            GroupedWorksheetItemType::StageHeader => StageHeader::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => null])
                ->all(),
            GroupedWorksheetItemType::HybridWorksheet => HybridWorksheet::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            GroupedWorksheetItemType::LogEntryWorksheet => LogEntryWorksheet::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            default => [],
        };
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    protected function matrixSectionsForSelectedProcedure(): array
    {
        $procedure = $this->selectedProcedure;
        if (! $procedure || ! $procedure->isSectionedMatrix()) {
            return [];
        }

        return collect(data_get($procedure->layout_settings, 'sections', []))
            ->map(fn ($section) => [
                'key' => (string) ($section['key'] ?? ''),
                'label' => (string) ($section['label'] ?? $section['key'] ?? ''),
            ])
            ->filter(fn ($section) => $section['key'] !== '')
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    protected function matrixRowsForSelectedSection(): array
    {
        $procedure = $this->selectedProcedure;
        if (! $procedure || $this->config_section_key === '') {
            return [];
        }

        $section = $procedure->getMatrixSection($this->config_section_key);
        if (! $section) {
            return [];
        }

        return collect(data_get($section, 'rows', []))
            ->map(fn ($row) => [
                'key' => (string) ($row['key'] ?? ''),
                'label' => (string) ($row['label'] ?? $row['key'] ?? ''),
            ])
            ->filter(fn ($row) => $row['key'] !== '')
            ->values()
            ->all();
    }

    public function updatedItemType(): void
    {
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
        $this->resetPhaseConfig();
    }

    public function selectItemType(string $value): void
    {
        $this->item_type = $value;
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showItemTypeDropdown = false;
        $this->showReferenceDropdown = false;
        $this->resetPhaseConfig();
    }

    public function openAddModal(): void
    {
        $this->resetItemForm();
        $this->showAddModal = true;
    }

    public function openEditModal(string $itemId): void
    {
        $item = GroupedWorksheetItem::findOrFail($itemId);
        $this->editingItemId = $itemId;
        $this->label = $item->label;
        $this->description = $item->description ?? '';
        $this->item_type = $item->getItemTypeEnum()->value;
        $this->reference_id = $item->reference_id ?? '';
        $this->is_required = $item->is_required;
        $this->hydratePhaseConfigFromItem($item);
        $this->showEditModal = true;
    }

    public function selectReference(string $id): void
    {
        $this->reference_id = $id;
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
    }

    public function clearReference(): void
    {
        $this->reference_id = '';
        $this->referenceSearch = '';
    }

    public function selectProcedure(string $id): void
    {
        $this->config_procedure_worksheet_id = $id;
        $this->procedureSearch = '';
        $this->showProcedureDropdown = false;
        $this->config_section_key = '';
        $this->config_row_keys = [];
    }

    public function clearProcedure(): void
    {
        $this->config_procedure_worksheet_id = '';
        $this->procedureSearch = '';
        $this->config_section_key = '';
        $this->config_row_keys = [];
    }

    public function addItem(): void
    {
        $this->validateItem();
        app(GroupedWorksheetReferenceValidator::class)->validateGroupedItem($this->item_type, $this->reference_id);

        $maxOrder = $this->holder->items()->max('sort_order') ?? 0;

        GroupedWorksheetItem::create([
            'grouped_worksheet_holder_id' => $this->holder->id,
            'sort_order' => $maxOrder + 1,
            'label' => $this->label,
            'description' => $this->description ?: null,
            'item_type' => $this->item_type,
            'reference_id' => $this->reference_id,
            'is_required' => $this->is_required,
            'config' => $this->configPayload(),
        ]);

        $this->showAddModal = false;
        $this->resetItemForm();
        $this->setMessage('Stage added to pipeline.', 'success');
        $this->holder->refresh();
        $this->selectedItemId = $this->holder->items->sortByDesc('sort_order')->first()?->id;
    }

    public function updateItem(): void
    {
        $this->validateItem();
        app(GroupedWorksheetReferenceValidator::class)->validateGroupedItem($this->item_type, $this->reference_id);

        GroupedWorksheetItem::findOrFail($this->editingItemId)->update([
            'label' => $this->label,
            'description' => $this->description ?: null,
            'item_type' => $this->item_type,
            'reference_id' => $this->reference_id,
            'is_required' => $this->is_required,
            'config' => $this->configPayload(),
        ]);

        $this->showEditModal = false;
        $this->resetItemForm();
        $this->setMessage('Stage updated.', 'success');
        $this->holder->refresh();
    }

    public function deleteItem(string $itemId): void
    {
        GroupedWorksheetItem::findOrFail($itemId)->delete();
        $this->reindexItems();
        $this->setMessage('Stage removed.', 'success');
        $this->holder->refresh();
        $this->syncSelectedItem();
    }

    public function moveUp(string $itemId): void
    {
        $this->swapOrder($itemId, -1);
    }

    public function moveDown(string $itemId): void
    {
        $this->swapOrder($itemId, 1);
    }

    protected function swapOrder(string $itemId, int $direction): void
    {
        $items = $this->holder->items()->orderBy('sort_order')->get()->values();
        $index = $items->search(fn ($item) => $item->id === $itemId);

        if ($index === false) {
            return;
        }

        $targetIndex = $index + $direction;
        if ($targetIndex < 0 || $targetIndex >= $items->count()) {
            return;
        }

        $current = $items[$index];
        $target = $items[$targetIndex];
        $currentOrder = $current->sort_order;
        $current->update(['sort_order' => $target->sort_order]);
        $target->update(['sort_order' => $currentOrder]);
        $this->holder->refresh();
    }

    protected function reindexItems(): void
    {
        $items = $this->holder->items()->orderBy('sort_order')->get();
        foreach ($items as $index => $item) {
            $item->update(['sort_order' => $index + 1]);
        }
    }

    protected function validateItem(): void
    {
        $rules = app(GroupedWorksheetReferenceValidator::class)->groupedItemRules();
        $pipelineMode = (string) $this->holder->getSettingValue('pipeline_mode', 'classic');

        if ($this->item_type === GroupedWorksheetItemType::StageHeader->value) {
            $rules = array_merge($rules, app(GroupedWorksheetReferenceValidator::class)->stageHeaderConfigRules(
                $pipelineMode,
                filled($this->config_procedure_worksheet_id)
            ));
        }

        $this->validate($rules);

        if ($this->item_type === GroupedWorksheetItemType::StageHeader->value && filled($this->config_procedure_worksheet_id)) {
            app(GroupedWorksheetReferenceValidator::class)->validateProcedureExists($this->config_procedure_worksheet_id);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function configPayload(): ?array
    {
        if ($this->item_type !== GroupedWorksheetItemType::StageHeader->value) {
            return null;
        }

        $config = [];

        if ($this->config_stage_order !== null) {
            $config['stage_order'] = (int) $this->config_stage_order;
        }

        if (filled($this->config_procedure_worksheet_id)) {
            $config['procedure_worksheet_id'] = $this->config_procedure_worksheet_id;
            if (filled($this->config_section_key)) {
                $config['section_key'] = $this->config_section_key;
            }
            if (! empty($this->config_row_keys)) {
                $config['row_keys'] = array_values(array_filter($this->config_row_keys, fn ($key) => filled($key)));
            }
            $config['show_config_fields'] = $this->config_show_config_fields;
        }

        return $config === [] ? null : $config;
    }

    protected function hydratePhaseConfigFromItem(GroupedWorksheetItem $item): void
    {
        $this->resetPhaseConfig();

        if ($item->getItemTypeEnum() !== GroupedWorksheetItemType::StageHeader) {
            return;
        }

        $order = $item->getConfigValue('stage_order');
        $this->config_stage_order = $order !== null && $order !== '' ? (int) $order : null;
        $this->config_procedure_worksheet_id = (string) ($item->getConfigValue('procedure_worksheet_id') ?? '');
        $this->config_section_key = (string) ($item->getConfigValue('section_key') ?? '');
        $rowKeys = $item->getConfigValue('row_keys');
        $this->config_row_keys = is_array($rowKeys) ? array_values($rowKeys) : [];
        $this->config_show_config_fields = (bool) ($item->getConfigValue('show_config_fields') ?? false);
    }

    protected function resetPhaseConfig(): void
    {
        $this->config_stage_order = null;
        $this->config_procedure_worksheet_id = '';
        $this->config_section_key = '';
        $this->config_row_keys = [];
        $this->config_show_config_fields = false;
        $this->procedureSearch = '';
        $this->showProcedureDropdown = false;
    }

    protected function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->label = '';
        $this->description = '';
        $this->item_type = GroupedWorksheetItemType::Procedure->value;
        $this->reference_id = '';
        $this->is_required = true;
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
        $this->showItemTypeDropdown = false;
        $this->resetPhaseConfig();
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }

    protected function syncSelectedItem(): void
    {
        if ($this->selectedItemId !== null
            && $this->holder->items->contains('id', $this->selectedItemId)) {
            return;
        }

        $this->selectedItemId = $this->holder->items->first()?->id;
    }
}
