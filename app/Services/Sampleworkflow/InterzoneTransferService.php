<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\InterzoneTransfer;
use App\Models\Sampleworkflow\InterzoneTransferSample;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Models\SubmissionFormInstanceValue;
use App\SampleDetails;
use App\SampleHeader;
use App\User;
use App\Zone;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InterzoneTransferService
{
    public function listRecent(int $limit = 50): Collection
    {
        return InterzoneTransfer::query()
            ->with([
                'fromZone:id,key,value',
                'toZone:id,key,value',
                'initiatedByUser:id,name',
                'submissionFormInstance:id,form_number,title',
                'sampleHeader:id,batch_code',
                'samples.sampleDetail:id,sample_code',
                'samples.toZone:id,key,value',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    public function transferRequestWithSampleZones(
        SubmissionFormInstance $instance,
        array $assignments,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        $assignments = $this->normalizeAssignments($assignments);

        if ($assignments === []) {
            throw new \InvalidArgumentException('Assign a destination zone for at least one sample.');
        }

        return DB::transaction(function () use ($instance, $assignments, $reportFromParentZone, $remarks) {
            $fromZoneId = $this->resolveInstanceOriginZoneId($instance);
            $this->ensureOriginZoneStored($instance, $fromZoneId);

            $this->assertAssignmentsBelongToInstance($instance, $assignments);

            $transfer = $this->createTransferRecord(
                InterzoneTransfer::SCOPE_REQUEST,
                InterzoneTransfer::MODE_FULL,
                $fromZoneId,
                $this->resolveHeaderToZoneId($assignments),
                $reportFromParentZone,
                $remarks,
                submissionFormInstanceId: (string) $instance->id,
            );

            $this->applySampleZoneAssignments($assignments, $transfer);

            $this->syncInstanceAndBatchesAfterSampleTransfers($instance, $assignments, $fromZoneId, $reportFromParentZone);

            return $transfer->load(['fromZone', 'toZone', 'samples.toZone', 'samples.sampleDetail']);
        });
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    public function transferBatchWithSampleZones(
        SampleHeader $header,
        array $assignments,
        string $mode,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        $assignments = $this->normalizeAssignments($assignments);

        if ($assignments === []) {
            throw new \InvalidArgumentException('Assign a destination zone for at least one sample.');
        }

        if (! in_array($mode, [InterzoneTransfer::MODE_FULL, InterzoneTransfer::MODE_PARTIAL], true)) {
            throw new \InvalidArgumentException('Invalid transfer mode.');
        }

        return DB::transaction(function () use ($header, $assignments, $mode, $reportFromParentZone, $remarks) {
            $instance = $header->submission_form_instance_id
                ? SubmissionFormInstance::query()->find($header->submission_form_instance_id)
                : null;

            $fromZoneId = $this->resolveBatchOriginZoneId($header, $instance);
            $this->ensureBatchOriginZoneStored($header, $fromZoneId);

            if ($instance) {
                $this->ensureOriginZoneStored($instance, $fromZoneId);
            }

            $this->assertAssignmentsBelongToBatch($header, $assignments, $mode);

            $transfer = $this->createTransferRecord(
                InterzoneTransfer::SCOPE_BATCH,
                $mode,
                $fromZoneId,
                $this->resolveHeaderToZoneId($assignments),
                $reportFromParentZone,
                $remarks,
                submissionFormInstanceId: $instance?->id,
                sampleHeaderId: (string) $header->id,
            );

            $this->applySampleZoneAssignments($assignments, $transfer);

            $this->syncBatchHeaderAfterSampleTransfers($header, $assignments, $fromZoneId, $reportFromParentZone);

            if ($instance) {
                $this->syncInstanceAfterBatchSampleTransfers($instance, $fromZoneId, $reportFromParentZone);
            }

            return $transfer->load(['fromZone', 'toZone', 'samples.toZone', 'samples.sampleDetail', 'sampleHeader']);
        });
    }

    /**
     * @param  list<string>  $sampleDetailIds
     */
    public function transferFullRequest(
        SubmissionFormInstance $instance,
        string $toZoneId,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        $assignments = $this->buildUniformAssignmentsForInstance($instance, $toZoneId);

        return $this->transferRequestWithSampleZones($instance, $assignments, $reportFromParentZone, $remarks);
    }

    public function transferFullBatch(
        SampleHeader $header,
        string $toZoneId,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        $assignments = $this->buildUniformAssignmentsForBatch($header, $toZoneId);

        return $this->transferBatchWithSampleZones(
            $header,
            $assignments,
            InterzoneTransfer::MODE_FULL,
            $reportFromParentZone,
            $remarks
        );
    }

    /**
     * @param  list<string>  $sampleDetailIds
     */
    public function transferPartialBatch(
        SampleHeader $header,
        array $sampleDetailIds,
        string $toZoneId,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        $assignments = array_map(
            fn (string $sampleDetailId) => [
                'sample_detail_id' => $sampleDetailId,
                'to_zone_id' => $toZoneId,
            ],
            array_values(array_unique(array_filter($sampleDetailIds)))
        );

        return $this->transferBatchWithSampleZones(
            $header,
            $assignments,
            InterzoneTransfer::MODE_PARTIAL,
            $reportFromParentZone,
            $remarks
        );
    }

    public function resolveInstanceOriginZoneId(SubmissionFormInstance $instance): ?string
    {
        if (! empty($instance->zone_id)) {
            return (string) $instance->zone_id;
        }

        $fromForm = $this->resolveZoneIdFromFormValues($instance);
        if ($fromForm) {
            return $fromForm;
        }

        $linkedRequest = $this->resolveLinkedSubmissionRequest($instance);
        if ($linkedRequest) {
            $fromRequest = $this->resolveZoneIdFromSubmissionRequest($linkedRequest);
            if ($fromRequest) {
                return $fromRequest;
            }
        }

        if ($instance->submitted_by) {
            $user = User::query()->select('zone_id')->find((string) $instance->submitted_by);
            if ($user?->zone_id) {
                return (string) $user->zone_id;
            }
        }

        return Auth::user()?->zone_id ? (string) Auth::user()->zone_id : null;
    }

    public function resolveBatchOriginZoneId(SampleHeader $header, ?SubmissionFormInstance $instance = null): ?string
    {
        if (! empty($header->zone_id)) {
            return (string) $header->zone_id;
        }

        $instance ??= $header->submission_form_instance_id
            ? SubmissionFormInstance::query()->find($header->submission_form_instance_id)
            : null;

        if ($instance) {
            $zoneId = $instance->processing_zone_id ?: $instance->zone_id;
            if ($zoneId) {
                return (string) $zoneId;
            }

            return $this->resolveInstanceOriginZoneId($instance);
        }

        return Auth::user()?->zone_id ? (string) Auth::user()->zone_id : null;
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function zonesForSelect(): array
    {
        return Zone::query()
            ->orderBy('value')
            ->get(['id', 'key', 'value'])
            ->map(fn (Zone $zone) => [
                'id' => (string) $zone->id,
                'label' => trim(($zone->key ? $zone->key . ' — ' : '') . $zone->value),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function zoneLabelsById(): array
    {
        $labels = [];
        foreach ($this->zonesForSelect() as $zone) {
            $labels[$zone['id']] = $zone['label'];
        }

        return $labels;
    }

    public function formatTransferDestinations(InterzoneTransfer $transfer): string
    {
        $transfer->loadMissing(['toZone:id,key,value', 'samples.toZone:id,key,value']);

        $zoneLabels = $transfer->samples
            ->map(fn (InterzoneTransferSample $row) => $this->formatZoneLabel($row->toZone))
            ->filter()
            ->unique()
            ->values();

        if ($zoneLabels->isNotEmpty()) {
            return $zoneLabels->count() === 1
                ? $zoneLabels->first()
                : $zoneLabels->implode(', ');
        }

        return $this->formatZoneLabel($transfer->toZone) ?? '—';
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     * @return list<array{sample_detail_id: string, to_zone_id: string}>
     */
    private function normalizeAssignments(array $assignments): array
    {
        $normalized = [];

        foreach ($assignments as $assignment) {
            $sampleDetailId = (string) ($assignment['sample_detail_id'] ?? '');
            $toZoneId = (string) ($assignment['to_zone_id'] ?? '');

            if ($sampleDetailId === '' || $toZoneId === '') {
                continue;
            }

            $normalized[$sampleDetailId] = [
                'sample_detail_id' => $sampleDetailId,
                'to_zone_id' => $toZoneId,
            ];
        }

        return array_values($normalized);
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function resolveHeaderToZoneId(array $assignments): ?string
    {
        $zoneIds = array_values(array_unique(array_column($assignments, 'to_zone_id')));

        return count($zoneIds) === 1 ? $zoneIds[0] : null;
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function applySampleZoneAssignments(
        array $assignments,
        InterzoneTransfer $transfer,
    ): void {
        $sampleDetailIds = array_column($assignments, 'sample_detail_id');

        $details = SampleDetails::query()
            ->whereIn('id', $sampleDetailIds)
            ->get()
            ->keyBy('id');

        foreach ($assignments as $assignment) {
            $detail = $details->get($assignment['sample_detail_id']);
            if (! $detail) {
                continue;
            }

            $detail->update(['processing_zone_id' => $assignment['to_zone_id']]);

            InterzoneTransferSample::query()->create([
                'interzone_transfer_id' => $transfer->id,
                'sample_detail_id' => $detail->id,
                'to_zone_id' => $assignment['to_zone_id'],
            ]);
        }
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function syncBatchHeaderAfterSampleTransfers(
        SampleHeader $header,
        array $assignments,
        ?string $fromZoneId,
        bool $reportFromParentZone
    ): void {
        $destinationZoneIds = array_values(array_unique(array_column($assignments, 'to_zone_id')));
        $batchUpdates = [];

        if (count($destinationZoneIds) === 1) {
            $batchUpdates['processing_zone_id'] = $destinationZoneIds[0];
        }

        $batchUpdates['reporting_zone_id'] = $this->resolveReportingZoneId(
            $fromZoneId,
            $destinationZoneIds,
            $reportFromParentZone
        );

        if ($batchUpdates !== []) {
            $header->update($batchUpdates);
        }
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function syncInstanceAndBatchesAfterSampleTransfers(
        SubmissionFormInstance $instance,
        array $assignments,
        ?string $fromZoneId,
        bool $reportFromParentZone
    ): void {
        $instance->load(['batches.samples']);

        $destinationZoneIds = array_values(array_unique(array_column($assignments, 'to_zone_id')));

        if (count($destinationZoneIds) === 1) {
            $instance->update(['processing_zone_id' => $destinationZoneIds[0]]);
        }

        foreach ($instance->batches as $batch) {
            $batchAssignments = array_values(array_filter(
                $assignments,
                fn (array $row) => $this->sampleBelongsToBatch($row['sample_detail_id'], $batch)
            ));

            if ($batchAssignments !== []) {
                $this->syncBatchHeaderAfterSampleTransfers($batch, $batchAssignments, $fromZoneId, $reportFromParentZone);
            }
        }
    }

    private function syncInstanceAfterBatchSampleTransfers(
        SubmissionFormInstance $instance,
        ?string $fromZoneId,
        bool $reportFromParentZone
    ): void {
        $instance->load(['batches.samples']);

        $allDestinationZoneIds = [];
        foreach ($instance->batches as $batch) {
            foreach ($batch->samples as $sample) {
                if ($sample->processing_zone_id) {
                    $allDestinationZoneIds[] = (string) $sample->processing_zone_id;
                }
            }
        }

        $unique = array_values(array_unique($allDestinationZoneIds));

        if (count($unique) === 1) {
            $instance->update(['processing_zone_id' => $unique[0]]);
        }
    }

    /**
     * @param  list<string>  $destinationZoneIds
     */
    private function resolveReportingZoneId(
        ?string $fromZoneId,
        array $destinationZoneIds,
        bool $reportFromParentZone
    ): ?string {
        if ($reportFromParentZone) {
            return $fromZoneId;
        }

        $unique = array_values(array_unique($destinationZoneIds));

        return count($unique) === 1 ? $unique[0] : $fromZoneId;
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function assertAssignmentsBelongToBatch(
        SampleHeader $header,
        array $assignments,
        string $mode
    ): void {
        $expectedCount = SampleDetails::query()->where('sample_header_id', $header->id)->count();
        $sampleDetailIds = array_column($assignments, 'sample_detail_id');

        $validCount = SampleDetails::query()
            ->where('sample_header_id', $header->id)
            ->whereIn('id', $sampleDetailIds)
            ->count();

        if ($validCount !== count($sampleDetailIds)) {
            throw new \InvalidArgumentException('One or more selected samples are invalid for this batch.');
        }

        if ($mode === InterzoneTransfer::MODE_FULL && count($sampleDetailIds) !== $expectedCount) {
            throw new \InvalidArgumentException('Full transfer requires a destination zone for every sample on the batch.');
        }
    }

    /**
     * @param  list<array{sample_detail_id: string, to_zone_id: string}>  $assignments
     */
    private function assertAssignmentsBelongToInstance(
        SubmissionFormInstance $instance,
        array $assignments
    ): void {
        $instance->load(['batches.samples']);

        $validIds = [];
        foreach ($instance->batches as $batch) {
            foreach ($batch->samples as $sample) {
                $validIds[] = (string) $sample->id;
            }
        }

        $validIds = array_values(array_unique($validIds));

        if ($validIds === []) {
            throw new \InvalidArgumentException('No samples found on this request to transfer.');
        }

        foreach (array_column($assignments, 'sample_detail_id') as $sampleDetailId) {
            if (! in_array($sampleDetailId, $validIds, true)) {
                throw new \InvalidArgumentException('One or more selected samples are invalid for this request.');
            }
        }
    }

    private function sampleBelongsToBatch(string $sampleDetailId, SampleHeader $batch): bool
    {
        return SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->where('id', $sampleDetailId)
            ->exists();
    }

    /**
     * @return list<array{sample_detail_id: string, to_zone_id: string}>
     */
    private function buildUniformAssignmentsForBatch(SampleHeader $header, string $toZoneId): array
    {
        return SampleDetails::query()
            ->where('sample_header_id', $header->id)
            ->orderBy('sample_code')
            ->pluck('id')
            ->map(fn ($id) => [
                'sample_detail_id' => (string) $id,
                'to_zone_id' => $toZoneId,
            ])
            ->all();
    }

    /**
     * @return list<array{sample_detail_id: string, to_zone_id: string}>
     */
    private function buildUniformAssignmentsForInstance(SubmissionFormInstance $instance, string $toZoneId): array
    {
        $instance->load(['batches.samples']);

        $assignments = [];
        foreach ($instance->batches as $batch) {
            foreach ($batch->samples as $sample) {
                $assignments[] = [
                    'sample_detail_id' => (string) $sample->id,
                    'to_zone_id' => $toZoneId,
                ];
            }
        }

        return $assignments;
    }

    private function createTransferRecord(
        string $scope,
        string $mode,
        ?string $fromZoneId,
        ?string $toZoneId,
        bool $reportFromParentZone,
        ?string $remarks,
        ?string $submissionFormInstanceId = null,
        ?string $sampleHeaderId = null,
    ): InterzoneTransfer {
        return InterzoneTransfer::query()->create([
            'transfer_scope' => $scope,
            'transfer_mode' => $mode,
            'submission_form_instance_id' => $submissionFormInstanceId,
            'sample_header_id' => $sampleHeaderId,
            'from_zone_id' => $fromZoneId,
            'to_zone_id' => $toZoneId,
            'report_from_parent_zone' => $reportFromParentZone,
            'status' => InterzoneTransfer::STATUS_COMPLETED,
            'remarks' => $remarks,
            'initiated_by' => Auth::id(),
            'transferred_at' => now(),
        ]);
    }

    private function ensureOriginZoneStored(SubmissionFormInstance $instance, ?string $zoneId): void
    {
        if ($zoneId && empty($instance->zone_id)) {
            $instance->update(['zone_id' => $zoneId]);
        }

        if (empty($instance->processing_zone_id) && $zoneId) {
            $instance->update(['processing_zone_id' => $zoneId]);
        }
    }

    private function ensureBatchOriginZoneStored(SampleHeader $header, ?string $zoneId): void
    {
        if ($zoneId && empty($header->zone_id)) {
            $header->update(['zone_id' => $zoneId]);
        }

        if (empty($header->processing_zone_id) && $zoneId) {
            $header->update(['processing_zone_id' => $zoneId]);
        }
    }

    private function resolveZoneIdFromFormValues(SubmissionFormInstance $instance): ?string
    {
        $value = SubmissionFormInstanceValue::query()
            ->where('submission_form_instance_id', $instance->id)
            ->whereHas('element', function ($query) {
                $query->where('element_type', 'zone_select')
                    ->orWhere('mapping_field', 'zone_id');
            })
            ->whereNotNull('value')
            ->where('value', '!=', '')
            ->value('value');

        return $value ? (string) $value : null;
    }

    private function resolveLinkedSubmissionRequest(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        foreach ([$instance->portal_request_id, $instance->target_record_id] as $candidateId) {
            if (empty($candidateId)) {
                continue;
            }

            $request = SampleSubmissionRequest::query()->find((string) $candidateId);
            if ($request) {
                return $request;
            }
        }

        return null;
    }

    private function resolveZoneIdFromSubmissionRequest(SampleSubmissionRequest $request): ?string
    {
        foreach (['zone_id', 'zone'] as $attribute) {
            $value = $request->getAttribute($attribute);
            if (! empty($value) && $this->isUuid((string) $value)) {
                return (string) $value;
            }
        }

        return null;
    }

    private function formatZoneLabel(?Zone $zone): ?string
    {
        if (! $zone) {
            return null;
        }

        return trim(($zone->key ? $zone->key . ' — ' : '') . $zone->value) ?: null;
    }

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
