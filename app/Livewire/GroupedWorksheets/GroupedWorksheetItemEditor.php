<?php

namespace App\Livewire\GroupedWorksheets;

use App\Enums\GroupedWorksheetItemType;
use App\Models\Formulars\Formula;
use App\Models\GroupedWorksheets\GroupedWorksheetHolder;
use App\Models\GroupedWorksheets\GroupedWorksheetItem;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use App\Services\GroupedWorksheets\GroupedWorksheetCapturePreviewService;
use App\Services\GroupedWorksheets\GroupedWorksheetReferenceValidator;
use Livewire\Component;

class GroupedWorksheetItemEditor extends Component
{
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

    public function mount(GroupedWorksheetHolder $holder): void
    {
        $this->holder = $holder->load('items');
        $this->syncSelectedItem();
    }

    public function render()
    {
        $this->holder->load('items');

        return view('livewire.grouped-worksheets.grouped-worksheet-item-editor', [
            'items' => $this->holder->items,
            'itemTypeOptions' => GroupedWorksheetItemType::options(),
            'capturePreview' => $this->capturePreview,
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
            default => null,
        };
    }

    public function getSelectedItemTypeLabelProperty(): string
    {
        return GroupedWorksheetItemType::options()[$this->item_type] ?? '';
    }

    public function updatedReferenceSearch(): void
    {
        $this->showReferenceDropdown = $this->referenceSearch !== '';
    }

    /**
     * @return array<int, array{id: string, name: string, description?: string|null}>
     */
    protected function referenceOptions(): array
    {
        $search = $this->referenceSearch;

        return match (GroupedWorksheetItemType::tryFrom($this->item_type)) {
            GroupedWorksheetItemType::Formula => Formula::query()
                ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            GroupedWorksheetItemType::Procedure => ProcedureWorksheet::query()
                ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            GroupedWorksheetItemType::StageHeader => StageHeader::query()
                ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => null])
                ->all(),
            GroupedWorksheetItemType::HybridWorksheet => HybridWorksheet::query()
                ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->orderBy('name')
                ->limit(30)
                ->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])
                ->all(),
            default => [],
        };
    }

    public function updatedItemType(): void
    {
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
    }

    public function selectItemType(string $value): void
    {
        $this->item_type = $value;
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showItemTypeDropdown = false;
        $this->showReferenceDropdown = false;
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
        $this->reference_id = $item->reference_id;
        $this->is_required = $item->is_required;
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
        $this->validate(app(GroupedWorksheetReferenceValidator::class)->groupedItemRules());
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
