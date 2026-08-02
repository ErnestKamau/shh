<?php

namespace App\Services\Portal;

use App\BatchAmmendment;
use App\BatchLabSectionApprover;
use App\DTOs\Portal\Crm\PortalAmendmentDTO;
use App\Exceptions\Api\Portal\PortalApiException;
use App\SampleDetails;
use App\SampleHeader;
use App\Services\Sampleworkflow\BatchWorkflowStageSyncService;
use App\Services\Sampleworkflow\JobSampleNumberingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PortalAmendmentService
{
    public function __construct(
        private readonly BatchWorkflowStageSyncService $workflowSync,
        private readonly JobSampleNumberingService $numbering,
    ) {}

    /**
     * Raise a report amendment and move the batch into Sample Verification.
     *
     * @param  list<string|int>  $sampleIds
     */
    public function raise(
        string $customerId,
        string $batchId,
        array $sampleIds,
        string $reason,
        ?string $createdByUserId = null,
        ?string $portalAccountId = null,
        string $source = 'portal',
    ): PortalAmendmentDTO {
        return DB::transaction(function () use (
            $customerId,
            $batchId,
            $sampleIds,
            $reason,
            $createdByUserId,
            $portalAccountId,
            $source,
        ): PortalAmendmentDTO {
            $batch = $this->findCustomerBatch($customerId, $batchId);

            if ($batch->isInAmendmentProcess()) {
                throw PortalApiException::withMessage(
                    'amendment_in_progress',
                    'This report already has an amendment in progress.',
                    422,
                );
            }

            if (! $this->isEligibleReleasedReport($batch)) {
                throw PortalApiException::withMessage(
                    'report_not_eligible',
                    'Only released reports can be amended from the portal.',
                    422,
                );
            }

            $sampleMap = $this->resolveSampleMap($batch, $sampleIds);
            if ($sampleMap === []) {
                throw PortalApiException::withMessage(
                    'samples_required',
                    'Select at least one valid sample from this report.',
                    422,
                );
            }

            $version = ((int) ($batch->is_amendment ?? 0)) + 1;

            $amendment = new BatchAmmendment();
            $amendment->samples = json_encode($sampleMap);
            $amendment->created_by_id = $createdByUserId;
            $amendment->reason = $reason;
            $amendment->batch_id = $batch->id;
            $amendment->report_url = BatchAmmendment::snapshotReportUrl($batch) ?: 'portal';
            $amendment->version_number = $version;
            $amendment->save();

            $batch->is_amendment = $version;
            $batch->in_ammendment_proccess = 1;
            $batch->verify_user_id = null;
            $batch->approve_user_id = null;
            $batch->approval_date = null;
            $batch->report_verified_date = null;

            $this->workflowSync->applyWorkflowStatus(
                $batch,
                'Sample Verification',
                ($source === 'portal' ? 'Portal' : 'CRM').' amendment raised: '.$reason,
            );
            $batch->save();

            BatchAmmendment::flagSamplesForAmendment(
                $batch,
                array_values($sampleMap),
                $version,
            );

            $this->numbering->syncReportNumbersForBatch($batch, $version);

            BatchLabSectionApprover::where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Verification')
                ->update([
                    'status' => 0,
                    'approval_date' => null,
                ]);

            BatchLabSectionApprover::where('batch_id', $batch->id)
                ->where('batch_status', 'Sample Approval')
                ->delete();

            Log::channel('daily')->info('portal.amendment.raised', [
                'amendment_id' => $amendment->id,
                'batch_id' => $batch->id,
                'customer_id' => $customerId,
                'portal_account_id' => $portalAccountId,
                'version' => $version,
                'source' => $source,
            ]);

            return $this->toDto($amendment->fresh() ?? $amendment, $batch->fresh() ?? $batch);
        });
    }

    public function list(string $customerId, int $page, int $perPage): LengthAwarePaginator
    {
        $paginator = BatchAmmendment::query()
            ->whereHas('sampleHeader', function (Builder $query) use ($customerId): void {
                $query->where('crm_customer_id', $customerId);
            })
            ->with(['sampleHeader'])
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection(
            $paginator->getCollection()->map(function (BatchAmmendment $amendment): PortalAmendmentDTO {
                $batch = $amendment->sampleHeader;

                return $this->toDto($amendment, $batch);
            }),
        );

        return $paginator;
    }

    public function countInProgress(string $customerId): int
    {
        return SampleHeader::query()
            ->where('crm_customer_id', $customerId)
            ->where('in_ammendment_proccess', 1)
            ->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function eligibleReports(string $customerId): array
    {
        return $this->releasedReportsQuery($customerId)
            ->where(function (Builder $query): void {
                $query->whereNull('in_ammendment_proccess')
                    ->orWhere('in_ammendment_proccess', 0);
            })
            ->limit(100)
            ->get()
            ->map(fn (SampleHeader $batch): array => $this->mapEligibleReport($batch))
            ->all();
    }

    /**
     * @return list<array{id: string, sample_code: string, sample_description: string|null}>
     */
    public function samplesForReport(string $customerId, string $batchId): array
    {
        $batch = $this->findCustomerBatch($customerId, $batchId);

        return SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->orderBy('sample_code')
            ->get(['id', 'sample_code'])
            ->map(fn (SampleDetails $sample): array => [
                'id' => (string) $sample->id,
                'sample_code' => (string) ($sample->sample_code ?? ''),
                'sample_description' => null,
            ])
            ->all();
    }

    private function findCustomerBatch(string $customerId, string $batchId): SampleHeader
    {
        $batch = SampleHeader::query()
            ->where('id', $batchId)
            ->where('crm_customer_id', $customerId)
            ->first();

        if (! $batch) {
            throw PortalApiException::withMessage(
                'batch_not_found',
                'We could not find that report for this customer.',
                404,
            );
        }

        return $batch;
    }

    private function isEligibleReleasedReport(SampleHeader $batch): bool
    {
        return $this->releasedReportsQuery((string) $batch->crm_customer_id)
            ->where('sample_headers.id', $batch->id)
            ->exists();
    }

    private function releasedReportsQuery(string $customerId): Builder
    {
        $reportStatus = (string) config('dashboard.report_status', 'Completed');

        return SampleHeader::query()
            ->with([
                'sample_type:id,name',
                'samples:id,sample_header_id,report_number',
            ])
            ->where('crm_customer_id', $customerId)
            ->where(function (Builder $query): void {
                $query->whereNotNull('batch_report_url')
                    ->orWhereNotNull('batch_report_online_url');
            })
            ->where(function (Builder $query) use ($reportStatus): void {
                $query->where('status', $reportStatus)
                    ->orWhereExists(function ($sub): void {
                        $sub->select(DB::raw(1))
                            ->from('test_request_report_deliveries')
                            ->whereRaw('test_request_report_deliveries.batch_id = sample_headers.id::text')
                            ->where('channel', 'portal')
                            ->where('status', 'sent');
                    });
            })
            ->orderByDesc('updated_at');
    }

    /**
     * @param  list<string|int>  $sampleIds
     * @return array<string, string>
     */
    private function resolveSampleMap(SampleHeader $batch, array $sampleIds): array
    {
        $requested = collect($sampleIds)
            ->map(fn ($id) => (string) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($requested === []) {
            return [];
        }

        $rows = SampleDetails::query()
            ->where('sample_header_id', $batch->id)
            ->whereIn('id', $requested)
            ->get(['id', 'sample_code']);

        $map = [];
        foreach ($rows as $row) {
            $code = trim((string) ($row->sample_code ?? ''));
            $key = $code !== '' ? $code : (string) $row->id;
            $map[$key] = (string) $row->id;
        }

        return $map;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapEligibleReport(SampleHeader $batch): array
    {
        return [
            'batch_id' => (string) $batch->id,
            'report_number' => (string) ($this->resolveReportNumber($batch) ?? ''),
            'submission_request_number' => (string) ($this->resolveRequestReference($batch) ?? ''),
            'sample_type' => $batch->relationLoaded('sample_type') ? $batch->sample_type?->name : null,
            'released_date' => $batch->updated_at?->toIso8601String(),
            'can_raise_amendment' => ! $batch->isInAmendmentProcess(),
        ];
    }

    private function resolveReportNumber(SampleHeader $batch): ?string
    {
        $documentNumber = trim((string) ($batch->document_number ?? ''));
        if ($documentNumber !== '') {
            return $documentNumber;
        }

        if ($batch->relationLoaded('samples')) {
            $sampleReportNumber = $batch->samples
                ->map(fn ($sample) => trim((string) ($sample->report_number ?? '')))
                ->first(fn (string $value) => $value !== '');
            if (is_string($sampleReportNumber) && $sampleReportNumber !== '') {
                return $sampleReportNumber;
            }
        } else {
            $sampleReportNumber = SampleDetails::query()
                ->where('sample_header_id', $batch->id)
                ->whereNotNull('report_number')
                ->orderBy('id')
                ->value('report_number');
            $sampleReportNumber = trim((string) ($sampleReportNumber ?? ''));
            if ($sampleReportNumber !== '') {
                return $sampleReportNumber;
            }
        }

        $batchCode = trim((string) ($batch->batch_code ?? ''));

        return $batchCode !== '' ? $batchCode : null;
    }

    private function resolveRequestReference(SampleHeader $batch): ?string
    {
        $reference = trim((string) ($batch->reference_number ?? ''));
        if ($reference !== '') {
            return $reference;
        }

        $batchCode = trim((string) ($batch->batch_code ?? ''));

        return $batchCode !== '' ? $batchCode : null;
    }

    private function toDto(BatchAmmendment $amendment, ?SampleHeader $batch): PortalAmendmentDTO
    {
        $reportNumber = $batch ? $this->resolveReportNumber($batch) : null;
        $version = (int) ($amendment->version_number ?? 0);
        $reference = trim((string) ($reportNumber ?? 'REPORT')).'-V'.$version;

        $sampleCodes = [];
        $decoded = json_decode((string) $amendment->samples, true);
        if (is_array($decoded)) {
            $sampleCodes = array_map('strval', array_keys($decoded));
        }

        return new PortalAmendmentDTO(
            id: (string) $amendment->id,
            reference: $reference,
            reportNumber: $reportNumber !== null ? (string) $reportNumber : null,
            batchId: (string) ($amendment->batch_id ?? $batch?->id ?? ''),
            version: $version,
            reason: (string) ($amendment->reason ?? ''),
            status: $this->publicStatus($batch),
            sampleCodes: $sampleCodes,
            createdAt: $amendment->created_at?->toIso8601String(),
            updatedAt: $amendment->updated_at?->toIso8601String(),
        );
    }

    private function publicStatus(?SampleHeader $batch): string
    {
        if (! $batch) {
            return 'completed';
        }

        if ($batch->isInAmendmentProcess()) {
            $status = strtolower(trim((string) $batch->status));
            if (str_contains($status, 'verification')) {
                return 'in_verification';
            }

            return 'in_progress';
        }

        return 'completed';
    }
}
