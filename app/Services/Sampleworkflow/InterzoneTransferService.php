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
            ])
            ->latest()
            ->limit($limit)
            ->get();
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
        return DB::transaction(function () use ($instance, $toZoneId, $reportFromParentZone, $remarks) {
            $fromZoneId = $this->resolveInstanceOriginZoneId($instance);
            $this->ensureOriginZoneStored($instance, $fromZoneId);

            $transfer = $this->createTransferRecord(
                InterzoneTransfer::SCOPE_REQUEST,
                InterzoneTransfer::MODE_FULL,
                $fromZoneId,
                $toZoneId,
                $reportFromParentZone,
                $remarks,
                submissionFormInstanceId: (string) $instance->id,
            );

            $instance->update([
                'processing_zone_id' => $toZoneId,
            ]);

            foreach ($instance->batches as $batch) {
                $this->applyFullBatchZoneUpdate($batch, $fromZoneId, $toZoneId, $reportFromParentZone);
            }

            return $transfer->load(['fromZone', 'toZone']);
        });
    }

    public function transferFullBatch(
        SampleHeader $header,
        string $toZoneId,
        bool $reportFromParentZone,
        ?string $remarks = null
    ): InterzoneTransfer {
        return DB::transaction(function () use ($header, $toZoneId, $reportFromParentZone, $remarks) {
            $instance = $header->submission_form_instance_id
                ? SubmissionFormInstance::query()->find($header->submission_form_instance_id)
                : null;

            $fromZoneId = $this->resolveBatchOriginZoneId($header, $instance);
            $this->ensureBatchOriginZoneStored($header, $fromZoneId);

            if ($instance) {
                $this->ensureOriginZoneStored($instance, $fromZoneId);
            }

            $transfer = $this->createTransferRecord(
                InterzoneTransfer::SCOPE_BATCH,
                InterzoneTransfer::MODE_FULL,
                $fromZoneId,
                $toZoneId,
                $reportFromParentZone,
                $remarks,
                submissionFormInstanceId: $instance?->id,
                sampleHeaderId: (string) $header->id,
            );

            $this->applyFullBatchZoneUpdate($header, $fromZoneId, $toZoneId, $reportFromParentZone);

            if ($instance) {
                $instance->update(['processing_zone_id' => $toZoneId]);
            }

            return $transfer->load(['fromZone', 'toZone', 'sampleHeader']);
        });
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
        $sampleDetailIds = array_values(array_unique(array_filter($sampleDetailIds)));

        if ($sampleDetailIds === []) {
            throw new \InvalidArgumentException('Select at least one sample to transfer.');
        }

        return DB::transaction(function () use ($header, $sampleDetailIds, $toZoneId, $reportFromParentZone, $remarks) {
            $instance = $header->submission_form_instance_id
                ? SubmissionFormInstance::query()->find($header->submission_form_instance_id)
                : null;

            $fromZoneId = $this->resolveBatchOriginZoneId($header, $instance);
            $this->ensureBatchOriginZoneStored($header, $fromZoneId);

            $details = SampleDetails::query()
                ->where('sample_header_id', $header->id)
                ->whereIn('id', $sampleDetailIds)
                ->get();

            if ($details->count() !== count($sampleDetailIds)) {
                throw new \InvalidArgumentException('One or more selected samples are invalid for this batch.');
            }

            $transfer = $this->createTransferRecord(
                InterzoneTransfer::SCOPE_BATCH,
                InterzoneTransfer::MODE_PARTIAL,
                $fromZoneId,
                $toZoneId,
                $reportFromParentZone,
                $remarks,
                submissionFormInstanceId: $instance?->id,
                sampleHeaderId: (string) $header->id,
            );

            foreach ($details as $detail) {
                $detail->update(['processing_zone_id' => $toZoneId]);
                InterzoneTransferSample::query()->create([
                    'interzone_transfer_id' => $transfer->id,
                    'sample_detail_id' => $detail->id,
                ]);
            }

            $reportingZoneId = $reportFromParentZone ? $fromZoneId : $toZoneId;
            $header->update([
                'reporting_zone_id' => $reportingZoneId,
            ]);

            return $transfer->load(['fromZone', 'toZone', 'samples.sampleDetail']);
        });
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

    private function applyFullBatchZoneUpdate(
        SampleHeader $header,
        ?string $fromZoneId,
        string $toZoneId,
        bool $reportFromParentZone
    ): void {
        $reportingZoneId = $reportFromParentZone ? $fromZoneId : $toZoneId;

        $header->update([
            'processing_zone_id' => $toZoneId,
            'reporting_zone_id' => $reportingZoneId,
        ]);

        SampleDetails::query()
            ->where('sample_header_id', $header->id)
            ->update(['processing_zone_id' => $toZoneId]);
    }

    private function createTransferRecord(
        string $scope,
        string $mode,
        ?string $fromZoneId,
        string $toZoneId,
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

    private function isUuid(string $value): bool
    {
        return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value);
    }
}
