<?php

namespace App\Livewire\Worksheets;

use App\Enums\HybridWorksheetBlockType;
use App\Models\Formulars\Formula;
use App\Models\HybridWorksheets\HybridWorksheet;
use App\Models\HybridWorksheets\HybridWorksheetVersion;
use App\Models\StageHeader;
use App\SampleHeader;
use Illuminate\Support\Collection;
use Livewire\Component;

class HybridWorksheetRunner extends Component
{
    public SampleHeader $batch;

    public HybridWorksheet $hybridWorksheet;

    public int $currentBlockIndex = 0;

    public function mount(SampleHeader $batch, HybridWorksheet $hybridWorksheet): void
    {
        $this->batch = $batch;
        $this->hybridWorksheet = $hybridWorksheet->load('activeVersion.blocks');
    }

    public function render()
    {
        $version = $this->hybridWorksheet->activeVersion;
        $blocks = $version
            ? $version->blocks()->with(['formulaSteps', 'procedureSteps', 'sequenceStages'])->orderBy('sort_order')->get()
            : collect();
        $currentBlock = $blocks->values()->get($this->currentBlockIndex);

        $formula = null;
        $stageHeaders = collect();
        $procedureWorksheetId = null;

        if ($currentBlock) {
            $type = $currentBlock->getBlockTypeEnum();
            match ($type) {
                HybridWorksheetBlockType::FormulaReference => $formula = Formula::with('activeVersion')->find($currentBlock->reference_id),
                HybridWorksheetBlockType::ProcedureReference => $procedureWorksheetId = $currentBlock->reference_id,
                HybridWorksheetBlockType::StageHeaderReference => $stageHeaders = StageHeader::with(['method', 'analyte', 'sampleType', 'testStages'])
                    ->where('id', $currentBlock->reference_id)
                    ->get(),
                default => null,
            };
        }

        return view('livewire.worksheets.hybrid-worksheet-runner', [
            'blocks' => $blocks,
            'currentBlock' => $currentBlock,
            'formula' => $formula,
            'stageHeaders' => $stageHeaders,
            'stageHeadersPayload' => $this->stageHeadersPayload($stageHeaders),
            'procedureWorksheetId' => $procedureWorksheetId,
            'version' => $version,
        ]);
    }

    public function nextBlock(): void
    {
        $blocks = $this->hybridWorksheet->activeVersion?->blocks ?? collect();
        if ($this->currentBlockIndex < $blocks->count() - 1) {
            $this->currentBlockIndex++;
            $this->dispatch('init-method-sequences');
        } else {
            $this->dispatch('groupedStageCompleted');
        }
    }

    public function previousBlock(): void
    {
        if ($this->currentBlockIndex > 0) {
            $this->currentBlockIndex--;
            $this->dispatch('init-method-sequences');
        }
    }

    /**
     * @param  Collection<int, StageHeader>  $stageHeaders
     * @return array<int, array<string, mixed>>
     */
    protected function stageHeadersPayload(Collection $stageHeaders): array
    {
        return $stageHeaders->map(function ($stageHeader) {
            return [
                'id' => $stageHeader->id,
                'name' => $stageHeader->name,
                'method_name' => $stageHeader->method ? $stageHeader->method->name : 'N/A',
                'analyte_name' => $stageHeader->analyte ? $stageHeader->analyte->name : 'N/A',
                'sample_type_name' => $stageHeader->sampleType ? $stageHeader->sampleType->name : 'All',
                'total_days' => $stageHeader->total_days,
                'stages_count' => $stageHeader->testStages->count(),
            ];
        })->values()->all();
    }
}
