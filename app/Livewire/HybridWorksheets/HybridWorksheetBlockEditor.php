<?php

namespace App\Livewire\HybridWorksheets;

use App\Enums\HybridWorksheetBlockType;
use App\Models\Formulars\Formula;
use App\Models\HybridWorksheets\HybridFormulaStep;
use App\Models\HybridWorksheets\HybridProcedureStep;
use App\Models\HybridWorksheets\HybridSequenceStage;
use App\Models\HybridWorksheets\HybridWorksheetBlock;
use App\Models\HybridWorksheets\HybridWorksheetVersion;
use App\Models\Procedures\ProcedureWorksheet;
use App\Models\StageHeader;
use App\Livewire\Concerns\AppliesCaseInsensitiveSearch;
use App\Services\GroupedWorksheets\GroupedWorksheetReferenceValidator;
use Livewire\Component;

class HybridWorksheetBlockEditor extends Component
{
    use AppliesCaseInsensitiveSearch;

    public HybridWorksheetVersion $version;

    public bool $showAddModal = false;

    public bool $showEditModal = false;

    public ?string $editingBlockId = null;

    public string $label = '';

    public string $block_type = 'formula_reference';

    public string $reference_id = '';

    public string $referenceSearch = '';

    public bool $showReferenceDropdown = false;

    public bool $showBlockTypeDropdown = false;

    public string $message = '';

    public string $messageType = '';

    public bool $showInlineEditor = false;

    public ?string $inlineBlockId = null;

    public function mount(HybridWorksheetVersion $version): void
    {
        $this->version = $version->load(['hybridWorksheet', 'blocks']);
    }

    public function render()
    {
        $this->version->load('blocks');

        return view('livewire.hybrid-worksheets.hybrid-worksheet-block-editor', [
            'blocks' => $this->version->blocks,
            'blockTypeOptions' => array_merge(
                HybridWorksheetBlockType::referenceOptions(),
                HybridWorksheetBlockType::inlineOptions()
            ),
        ]);
    }

    public function getFilteredReferenceOptionsProperty(): array
    {
        return $this->referenceOptions();
    }

    public function getSelectedReferenceProperty(): ?object
    {
        if ($this->reference_id === '' || ! HybridWorksheetBlockType::tryFrom($this->block_type)?->isReference()) {
            return null;
        }

        return match (HybridWorksheetBlockType::from($this->block_type)) {
            HybridWorksheetBlockType::FormulaReference => Formula::find($this->reference_id),
            HybridWorksheetBlockType::ProcedureReference => ProcedureWorksheet::find($this->reference_id),
            HybridWorksheetBlockType::StageHeaderReference => StageHeader::find($this->reference_id),
            default => null,
        };
    }

    public function getSelectedBlockTypeLabelProperty(): string
    {
        $options = array_merge(
            HybridWorksheetBlockType::referenceOptions(),
            HybridWorksheetBlockType::inlineOptions()
        );

        return $options[$this->block_type] ?? '';
    }

    public function getIsReferenceBlockTypeProperty(): bool
    {
        return HybridWorksheetBlockType::tryFrom($this->block_type)?->isReference() ?? false;
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
        $type = HybridWorksheetBlockType::tryFrom($this->block_type);
        if (! $type || ! $type->isReference()) {
            return [];
        }

        $search = $this->referenceSearch;

        return match ($type) {
            HybridWorksheetBlockType::FormulaReference => Formula::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')->limit(30)->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])->all(),
            HybridWorksheetBlockType::ProcedureReference => ProcedureWorksheet::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')->limit(30)->get(['id', 'name', 'description'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => $r->description])->all(),
            HybridWorksheetBlockType::StageHeaderReference => StageHeader::query()
                ->when($search, fn ($q) => $this->applyCaseInsensitiveSearch($q, ['name'], (string) $search))
                ->orderBy('name')->limit(30)->get(['id', 'name'])
                ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'description' => null])->all(),
            default => [],
        };
    }

    public function updatedBlockType(): void
    {
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
    }

    public function selectBlockType(string $value): void
    {
        $this->block_type = $value;
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showBlockTypeDropdown = false;
        $this->showReferenceDropdown = false;
    }

    public function openAddModal(): void
    {
        $this->resetBlockForm();
        $this->showAddModal = true;
    }

    public function openEditModal(string $blockId): void
    {
        $block = HybridWorksheetBlock::findOrFail($blockId);
        $this->editingBlockId = $blockId;
        $this->label = $block->label ?? '';
        $this->block_type = $block->getBlockTypeEnum()->value;
        $this->reference_id = $block->reference_id ?? '';
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

    public function addBlock(): void
    {
        $this->persistBlock();
        $this->showAddModal = false;
        $this->resetBlockForm();
        $this->setMessage('Block added.', 'success');
    }

    public function updateBlock(): void
    {
        $block = HybridWorksheetBlock::findOrFail($this->editingBlockId);
        $payload = $this->blockPayload();
        app(GroupedWorksheetReferenceValidator::class)->validateHybridBlock($this->block_type, $payload['reference_id'] ?? null);
        $block->update($payload);
        $this->showEditModal = false;
        $this->resetBlockForm();
        $this->setMessage('Block updated.', 'success');
        $this->version->refresh();
    }

    protected function persistBlock(): void
    {
        $payload = $this->blockPayload();
        app(GroupedWorksheetReferenceValidator::class)->validateHybridBlock($this->block_type, $payload['reference_id'] ?? null);

        $maxOrder = $this->version->blocks()->max('sort_order') ?? 0;
        $block = HybridWorksheetBlock::create(array_merge($payload, [
            'hybrid_worksheet_version_id' => $this->version->id,
            'sort_order' => $maxOrder + 1,
        ]));

        $type = HybridWorksheetBlockType::from($this->block_type);
        if ($type === HybridWorksheetBlockType::FormulaInline) {
            HybridFormulaStep::create([
                'hybrid_worksheet_block_id' => $block->id,
                'step_number' => 1,
                'variable_name' => 'input_1',
                'step_type' => 'input',
                'label' => 'Input 1',
            ]);
        } elseif ($type === HybridWorksheetBlockType::ProcedureInline) {
            HybridProcedureStep::create([
                'hybrid_worksheet_block_id' => $block->id,
                'step' => 'Step 1',
                'order' => 1,
            ]);
        } elseif ($type === HybridWorksheetBlockType::SequenceInline) {
            HybridSequenceStage::create([
                'hybrid_worksheet_block_id' => $block->id,
                'name' => 'Stage 1',
                'order' => 1,
            ]);
        }

        $this->version->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    protected function blockPayload(): array
    {
        $this->validate(app(GroupedWorksheetReferenceValidator::class)->hybridBlockRules());

        $type = HybridWorksheetBlockType::from($this->block_type);

        return [
            'label' => $this->label ?: null,
            'block_type' => $this->block_type,
            'reference_id' => $type->isReference() ? $this->reference_id : null,
        ];
    }

    public function deleteBlock(string $blockId): void
    {
        HybridWorksheetBlock::findOrFail($blockId)->delete();
        $this->reindexBlocks();
        $this->setMessage('Block removed.', 'success');
        $this->version->refresh();
    }

    public function moveUp(string $blockId): void
    {
        $this->swapBlockOrder($blockId, -1);
    }

    public function moveDown(string $blockId): void
    {
        $this->swapBlockOrder($blockId, 1);
    }

    public function openInlineEditor(string $blockId): void
    {
        $this->inlineBlockId = $blockId;
        $this->showInlineEditor = true;
        $this->version->load(['blocks.formulaSteps', 'blocks.procedureSteps', 'blocks.sequenceStages']);
    }

    public function closeInlineEditor(): void
    {
        $this->showInlineEditor = false;
        $this->inlineBlockId = null;
    }

    public function addInlineFormulaStep(string $blockId): void
    {
        $block = HybridWorksheetBlock::findOrFail($blockId);
        $next = ($block->formulaSteps()->max('step_number') ?? 0) + 1;
        HybridFormulaStep::create([
            'hybrid_worksheet_block_id' => $blockId,
            'step_number' => $next,
            'variable_name' => 'var_'.$next,
            'step_type' => 'input',
            'label' => 'Input '.$next,
        ]);
    }

    public function addInlineProcedureStep(string $blockId): void
    {
        $block = HybridWorksheetBlock::findOrFail($blockId);
        $next = ($block->procedureSteps()->max('order') ?? 0) + 1;
        HybridProcedureStep::create([
            'hybrid_worksheet_block_id' => $blockId,
            'step' => 'Step '.$next,
            'order' => $next,
        ]);
    }

    public function addInlineSequenceStage(string $blockId): void
    {
        $block = HybridWorksheetBlock::findOrFail($blockId);
        $next = ($block->sequenceStages()->max('order') ?? 0) + 1;
        HybridSequenceStage::create([
            'hybrid_worksheet_block_id' => $blockId,
            'name' => 'Stage '.$next,
            'order' => $next,
        ]);
    }

    protected function swapBlockOrder(string $blockId, int $direction): void
    {
        $blocks = $this->version->blocks()->orderBy('sort_order')->get()->values();
        $index = $blocks->search(fn ($b) => $b->id === $blockId);
        if ($index === false) {
            return;
        }
        $targetIndex = $index + $direction;
        if ($targetIndex < 0 || $targetIndex >= $blocks->count()) {
            return;
        }
        $current = $blocks[$index];
        $target = $blocks[$targetIndex];
        $swap = $current->sort_order;
        $current->update(['sort_order' => $target->sort_order]);
        $target->update(['sort_order' => $swap]);
        $this->version->refresh();
    }

    protected function reindexBlocks(): void
    {
        foreach ($this->version->blocks()->orderBy('sort_order')->get() as $index => $block) {
            $block->update(['sort_order' => $index + 1]);
        }
    }

    protected function resetBlockForm(): void
    {
        $this->editingBlockId = null;
        $this->label = '';
        $this->block_type = HybridWorksheetBlockType::FormulaReference->value;
        $this->reference_id = '';
        $this->referenceSearch = '';
        $this->showReferenceDropdown = false;
        $this->showBlockTypeDropdown = false;
    }

    protected function setMessage(string $message, string $type): void
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}
