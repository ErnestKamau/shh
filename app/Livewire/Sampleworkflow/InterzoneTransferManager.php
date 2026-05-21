<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\Sampleworkflow\InterzoneTransfer;
use App\Models\SubmissionFormInstance;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\InterzoneTransferService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class InterzoneTransferManager extends Component
{
    public string $workflowStatus = '';

    public string $workflowSubTab = '';

    public bool $showModal = false;

    public string $modalMode = '';

    /** @var array<int, string> */
    public array $targetInstanceIds = [];

    public ?string $targetBatchId = null;

    public string $toZoneId = '';

    public bool $reportFromParentZone = true;

    public string $remarks = '';

    /** @var array<int, string> */
    public array $selectedSampleDetailIds = [];

    /** @var list<array{id: string, sample_code: string, processing_zone_id: ?string}> */
    public array $batchSampleOptions = [];

    public function mount(string $workflowStatus = '', string $workflowSubTab = ''): void
    {
        $this->workflowStatus = $workflowStatus;
        $this->workflowSubTab = $workflowSubTab;
    }

    #[On('open-interzone-transfer')]
    public function openTransferModal(string $mode, array $instanceIds = [], ?string $batchId = null): void
    {
        $this->resetValidation();
        $this->modalMode = $mode;
        $this->targetInstanceIds = array_values(array_filter($instanceIds));
        $this->targetBatchId = $batchId;
        $this->toZoneId = '';
        $this->reportFromParentZone = true;
        $this->remarks = '';
        $this->selectedSampleDetailIds = [];
        $this->batchSampleOptions = [];

        if ($mode === 'partial_batch' && $batchId) {
            $this->batchSampleOptions = SampleDetails::query()
                ->where('sample_header_id', $batchId)
                ->orderBy('sample_code')
                ->get(['id', 'sample_code', 'processing_zone_id'])
                ->map(fn (SampleDetails $detail) => [
                    'id' => (string) $detail->id,
                    'sample_code' => (string) $detail->sample_code,
                    'processing_zone_id' => $detail->processing_zone_id,
                ])
                ->all();
        }

        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->modalMode = '';
    }

    public function submitTransfer(InterzoneTransferService $service): void
    {
        $this->validate([
            'toZoneId' => ['required', 'string', Rule::exists('zones', 'id')],
            'reportFromParentZone' => ['boolean'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'selectedSampleDetailIds' => [Rule::requiredIf($this->modalMode === 'partial_batch'), 'array', 'min:1'],
            'selectedSampleDetailIds.*' => ['string', Rule::exists('sample_details', 'id')],
        ], [
            'toZoneId.required' => 'Select the destination zone.',
            'selectedSampleDetailIds.required' => 'Select at least one sample for a partial transfer.',
            'selectedSampleDetailIds.min' => 'Select at least one sample for a partial transfer.',
        ]);

        try {
            if ($this->modalMode === 'full_request') {
                $this->submitFullRequestTransfers($service);
            } elseif ($this->modalMode === 'full_batch') {
                $this->submitFullBatchTransfer($service);
            } elseif ($this->modalMode === 'partial_batch') {
                $this->submitPartialBatchTransfer($service);
            } else {
                throw new \InvalidArgumentException('Unknown interzone transfer mode.');
            }

            $this->closeModal();
            $this->dispatch('interzone-transfer-completed');
            session()->flash('success', 'Interzone transfer recorded successfully.');
        } catch (\Throwable $exception) {
            session()->flash('error', $exception->getMessage());
        }
    }

    public function getTransfersProperty(): Collection
    {
        return app(InterzoneTransferService::class)->listRecent(100);
    }

    public function getZonesProperty(): array
    {
        return app(InterzoneTransferService::class)->zonesForSelect();
    }

    public function getModalTitleProperty(): string
    {
        return match ($this->modalMode) {
            'full_request' => 'Full interzone transfer (request)',
            'full_batch' => 'Full interzone transfer (batch)',
            'partial_batch' => 'Partial interzone transfer (batch)',
            default => 'Interzone transfer',
        };
    }

    private function submitFullRequestTransfers(InterzoneTransferService $service): void
    {
        if ($this->targetInstanceIds === []) {
            throw new \InvalidArgumentException('Select at least one request to transfer.');
        }

        foreach ($this->targetInstanceIds as $instanceId) {
            $instance = SubmissionFormInstance::query()->with('batches')->findOrFail($instanceId);
            $service->transferFullRequest(
                $instance,
                $this->toZoneId,
                $this->reportFromParentZone,
                $this->remarks !== '' ? $this->remarks : null
            );
        }
    }

    private function submitFullBatchTransfer(InterzoneTransferService $service): void
    {
        if (! $this->targetBatchId) {
            throw new \InvalidArgumentException('No batch selected for transfer.');
        }

        $header = SampleHeader::query()->findOrFail($this->targetBatchId);
        $service->transferFullBatch(
            $header,
            $this->toZoneId,
            $this->reportFromParentZone,
            $this->remarks !== '' ? $this->remarks : null
        );
    }

    private function submitPartialBatchTransfer(InterzoneTransferService $service): void
    {
        if (! $this->targetBatchId) {
            throw new \InvalidArgumentException('No batch selected for transfer.');
        }

        $header = SampleHeader::query()->findOrFail($this->targetBatchId);
        $service->transferPartialBatch(
            $header,
            $this->selectedSampleDetailIds,
            $this->toZoneId,
            $this->reportFromParentZone,
            $this->remarks !== '' ? $this->remarks : null
        );
    }

    public function render()
    {
        return view('livewire.sampleworkflow.interzone-transfer-manager', [
            'transfers' => $this->transfers,
            'zones' => $this->zones,
        ]);
    }
}
