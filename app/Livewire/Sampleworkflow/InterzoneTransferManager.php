<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\InterzoneTransferService;
use App\Zone;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class InterzoneTransferManager extends Component
{
    public string $workflowStatus = '';

    public string $workflowSubTab = '';

    public bool $showModal = false;

    /** request = selected submission instance(s); batch = single sample batch */
    public string $transferScope = 'request';

    /** full = all samples; partial = only checked samples */
    public string $transferType = 'full';

    /** @var array<int, string> */
    public array $targetInstanceIds = [];

    public ?string $targetBatchId = null;

    public ?string $targetBatchCode = null;

    public bool $reportFromParentZone = true;

    public string $remarks = '';

    /** Apply the same destination zone to sample rows in bulk. */
    public string $bulkToZoneId = '';

    /**
     * @var list<array{
     *     id: string,
     *     sample_code: string,
     *     processing_zone_id: ?string,
     *     current_zone_label: string,
     *     batch_id: string,
     *     batch_code: string,
     *     instance_id: string,
     *     instance_label: string
     * }>
     */
    public array $sampleRows = [];

    /** @var array<string, string> sample_detail_id => destination zone id */
    public array $sampleToZone = [];

    /** @var array<string, bool> sample_detail_id => included in transfer (partial only) */
    public array $sampleIncluded = [];

    public function mount(string $workflowStatus = '', string $workflowSubTab = ''): void
    {
        $this->workflowStatus = $workflowStatus;
        $this->workflowSubTab = $workflowSubTab;
    }

    /**
     * @param  array<int, string>  $instanceIds
     */
    #[On('open-interzone-transfer')]
    public function openTransferModal(
        string $scope = 'request',
        array $instanceIds = [],
        ?string $batchId = null,
        string $transferType = 'full',
        ?string $mode = null,
    ): void {
        $this->resetValidation();

        if ($mode !== null && $mode !== '') {
            [$this->transferScope, $this->transferType] = $this->mapLegacyMode($mode);
        } else {
            $this->transferScope = in_array($scope, ['request', 'batch'], true) ? $scope : 'request';
            $this->transferType = $transferType === 'partial' ? 'partial' : 'full';
        }

        if ($this->transferScope === 'request') {
            $this->transferType = 'full';
        } elseif ($this->transferType !== 'partial') {
            $this->transferType = 'full';
        }

        $this->targetInstanceIds = array_values(array_filter($instanceIds));
        $this->targetBatchId = $batchId;
        $this->targetBatchCode = null;
        $this->reportFromParentZone = true;
        $this->remarks = '';
        $this->bulkToZoneId = '';
        $this->sampleRows = [];
        $this->sampleToZone = [];
        $this->sampleIncluded = [];

        if ($this->transferScope === 'batch' && $this->targetBatchId) {
            $header = SampleHeader::query()->find($this->targetBatchId);
            $this->targetBatchCode = $header?->batch_code;
            $this->loadBatchSampleRows();
        } elseif ($this->transferScope === 'request') {
            $this->loadRequestSampleRows();
        }

        $this->initializeSampleTransferState();
        $this->showModal = true;
    }

    public function updatedTransferType(string $value): void
    {
        if ($value === 'full') {
            foreach ($this->sampleRows as $row) {
                $this->sampleIncluded[$row['id']] = true;
            }
            $this->resetValidation(['sampleIncluded']);
        } else {
            foreach ($this->sampleRows as $row) {
                $this->sampleIncluded[$row['id']] = false;
            }
        }
    }

    public function applyBulkZoneToAll(): void
    {
        if ($this->bulkToZoneId === '') {
            return;
        }

        foreach ($this->sampleRows as $row) {
            if ($this->isSampleActive($row['id'])) {
                $this->sampleToZone[$row['id']] = $this->bulkToZoneId;
            }
        }
    }

    public function applyBulkZoneToIncluded(): void
    {
        if ($this->bulkToZoneId === '') {
            return;
        }

        foreach ($this->sampleRows as $row) {
            if ($this->sampleIncluded[$row['id']] ?? false) {
                $this->sampleToZone[$row['id']] = $this->bulkToZoneId;
            }
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->transferScope = 'request';
        $this->transferType = 'full';
        $this->targetBatchId = null;
        $this->targetBatchCode = null;
        $this->sampleRows = [];
        $this->sampleToZone = [];
        $this->sampleIncluded = [];
        $this->bulkToZoneId = '';
    }

    public function submitTransfer(InterzoneTransferService $service): void
    {
        $this->validate($this->validationRules(), $this->validationMessages());

        try {
            if ($this->transferScope === 'batch' && $this->transferType === 'partial' && $this->activeSampleIds() === []) {
                throw new \InvalidArgumentException('Select at least one sample for a partial transfer.');
            }

            $assignments = $this->buildAssignments();

            if ($this->transferScope === 'request') {
                $this->submitRequestTransfers($service, $assignments);
            } elseif ($this->transferScope === 'batch') {
                if (! $this->targetBatchId) {
                    throw new \InvalidArgumentException('No batch selected for transfer.');
                }

                $header = SampleHeader::query()->findOrFail($this->targetBatchId);
                $service->transferBatchWithSampleZones(
                    $header,
                    $assignments,
                    $this->transferType === 'partial'
                        ? \App\Models\Sampleworkflow\InterzoneTransfer::MODE_PARTIAL
                        : \App\Models\Sampleworkflow\InterzoneTransfer::MODE_FULL,
                    $this->reportFromParentZone,
                    $this->remarks !== '' ? $this->remarks : null
                );
            } else {
                throw new \InvalidArgumentException('Invalid interzone transfer configuration.');
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
        return 'Interzone transfer';
    }

    public function getModalSummaryProperty(): string
    {
        if ($this->transferScope === 'batch') {
            $code = $this->targetBatchCode ?? $this->targetBatchId ?? 'batch';

            return $this->transferType === 'partial'
                ? "Assign a destination zone per sample on batch {$code}."
                : "Assign destination zones for each sample on batch {$code}.";
        }

        $count = count($this->targetInstanceIds);

        return $count === 1
            ? 'Assign destination zones for each sample on the selected request.'
            : "Assign destination zones per sample across {$count} selected requests.";
    }

    public function getShowsTransferTypeSelectorProperty(): bool
    {
        return $this->transferScope === 'batch';
    }

    public function getShowsSamplePartialCheckboxesProperty(): bool
    {
        return $this->transferScope === 'batch' && $this->transferType === 'partial';
    }

    public function getShowsSampleAssignmentsProperty(): bool
    {
        return $this->sampleRows !== [];
    }

    /**
     * @return array<string, array<int, string|mixed>>
     */
    private function validationRules(): array
    {
        $rules = [
            'transferType' => ['required', Rule::in(['full', 'partial'])],
            'reportFromParentZone' => ['boolean'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'bulkToZoneId' => ['nullable', 'string', Rule::exists('zones', 'id')],
        ];

        foreach ($this->activeSampleIds() as $sampleId) {
            $rules["sampleToZone.{$sampleId}"] = ['required', 'string', Rule::exists('zones', 'id')];
        }

        if ($this->transferScope === 'batch' && $this->transferType === 'partial') {
            $rules['sampleIncluded'] = ['array'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    private function validationMessages(): array
    {
        return [
            'transferType.in' => 'Select a valid transfer type.',
            'sampleToZone.*.required' => 'Select a destination zone for each included sample.',
            'sampleToZone.*.exists' => 'Select a valid destination zone.',
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function mapLegacyMode(string $mode): array
    {
        return match ($mode) {
            'full_batch' => ['batch', 'full'],
            'partial_batch' => ['batch', 'partial'],
            default => ['request', 'full'],
        };
    }

    private function initializeSampleTransferState(): void
    {
        foreach ($this->sampleRows as $row) {
            $sampleId = $row['id'];
            $this->sampleToZone[$sampleId] = $this->sampleToZone[$sampleId] ?? '';
            $this->sampleIncluded[$sampleId] = $this->transferType === 'full';
        }
    }

    private function loadBatchSampleRows(): void
    {
        if (! $this->targetBatchId) {
            $this->sampleRows = [];

            return;
        }

        $header = SampleHeader::query()
            ->with(['samples:id,sample_header_id,sample_code,processing_zone_id'])
            ->find($this->targetBatchId);

        if (! $header) {
            $this->sampleRows = [];

            return;
        }

        $zoneLabels = $this->zoneLabelMap();

        $this->sampleRows = $header->samples
            ->sortBy('sample_code')
            ->map(fn (SampleDetails $detail) => $this->mapSampleRow(
                $detail,
                (string) $header->id,
                (string) ($header->batch_code ?? 'Batch'),
                (string) ($header->submission_form_instance_id ?? ''),
                '',
                $zoneLabels
            ))
            ->values()
            ->all();
    }

    private function loadRequestSampleRows(): void
    {
        if ($this->targetInstanceIds === []) {
            $this->sampleRows = [];

            return;
        }

        $instances = SubmissionFormInstance::query()
            ->whereIn('id', $this->targetInstanceIds)
            ->with(['batches.samples:id,sample_header_id,sample_code,processing_zone_id'])
            ->orderBy('form_number')
            ->get();

        $zoneLabels = $this->zoneLabelMap();
        $rows = [];

        foreach ($instances as $instance) {
            $instanceLabel = $instance->form_number ?? $instance->title ?? 'Request';
            foreach ($instance->batches as $batch) {
                foreach ($batch->samples->sortBy('sample_code') as $detail) {
                    $rows[] = $this->mapSampleRow(
                        $detail,
                        (string) $batch->id,
                        (string) ($batch->batch_code ?? 'Batch'),
                        (string) $instance->id,
                        (string) $instanceLabel,
                        $zoneLabels
                    );
                }
            }
        }

        $this->sampleRows = $rows;
    }

    /**
     * @param  array<string, string>  $zoneLabels
     * @return array{
     *     id: string,
     *     sample_code: string,
     *     processing_zone_id: ?string,
     *     current_zone_label: string,
     *     batch_id: string,
     *     batch_code: string,
     *     instance_id: string,
     *     instance_label: string
     * }
     */
    private function mapSampleRow(
        SampleDetails $detail,
        string $batchId,
        string $batchCode,
        string $instanceId,
        string $instanceLabel,
        array $zoneLabels
    ): array {
        $processingZoneId = $detail->processing_zone_id ? (string) $detail->processing_zone_id : null;

        return [
            'id' => (string) $detail->id,
            'sample_code' => (string) $detail->sample_code,
            'processing_zone_id' => $processingZoneId,
            'current_zone_label' => $processingZoneId
                ? ($zoneLabels[$processingZoneId] ?? 'Current zone')
                : '—',
            'batch_id' => $batchId,
            'batch_code' => $batchCode,
            'instance_id' => $instanceId,
            'instance_label' => $instanceLabel,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function zoneLabelMap(): array
    {
        return Zone::query()
            ->orderBy('value')
            ->get(['id', 'key', 'value'])
            ->mapWithKeys(fn (Zone $zone) => [
                (string) $zone->id => trim(($zone->key ? $zone->key . ' — ' : '') . $zone->value),
            ])
            ->all();
    }

    /**
     * @return list<string>
     */
    private function activeSampleIds(): array
    {
        $ids = [];
        foreach ($this->sampleRows as $row) {
            if ($this->isSampleActive($row['id'])) {
                $ids[] = $row['id'];
            }
        }

        return $ids;
    }

    private function isSampleActive(string $sampleId): bool
    {
        if ($this->transferScope === 'batch' && $this->transferType === 'partial') {
            return (bool) ($this->sampleIncluded[$sampleId] ?? false);
        }

        return true;
    }

    /**
     * @return list<array{sample_detail_id: string, to_zone_id: string}>
     */
    private function buildAssignments(): array
    {
        $assignments = [];

        foreach ($this->sampleRows as $row) {
            if (! $this->isSampleActive($row['id'])) {
                continue;
            }

            $zoneId = trim((string) ($this->sampleToZone[$row['id']] ?? ''));
            if ($zoneId === '') {
                continue;
            }

            $assignments[] = [
                'sample_detail_id' => $row['id'],
                'to_zone_id' => $zoneId,
            ];
        }

        if ($assignments === []) {
            throw new \InvalidArgumentException('Assign a destination zone for at least one sample.');
        }

        return $assignments;
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function submitRequestTransfers(InterzoneTransferService $service, array $assignments): void
    {
        if ($this->targetInstanceIds === []) {
            throw new \InvalidArgumentException('Select at least one request to transfer.');
        }

        $byInstance = [];
        foreach ($this->sampleRows as $row) {
            if (! $this->isSampleActive($row['id'])) {
                continue;
            }

            $zoneId = trim((string) ($this->sampleToZone[$row['id']] ?? ''));
            if ($zoneId === '') {
                continue;
            }

            $byInstance[$row['instance_id']][] = [
                'sample_detail_id' => $row['id'],
                'to_zone_id' => $zoneId,
            ];
        }

        foreach ($this->targetInstanceIds as $instanceId) {
            $instanceAssignments = $byInstance[$instanceId] ?? [];
            if ($instanceAssignments === []) {
                continue;
            }

            $instance = SubmissionFormInstance::query()->with('batches')->findOrFail($instanceId);
            $service->transferRequestWithSampleZones(
                $instance,
                $instanceAssignments,
                $this->reportFromParentZone,
                $this->remarks !== '' ? $this->remarks : null
            );
        }
    }

    public function render()
    {
        return view('livewire.sampleworkflow.interzone-transfer-manager', [
            'transfers' => $this->transfers,
            'zones' => $this->zones,
            'transferService' => app(InterzoneTransferService::class),
        ]);
    }
}
