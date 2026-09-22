<?php

namespace App\Services\Sampleworkflow;

use App\BatchAmmendment;
use App\Models\TestRequestReportRevision;
use App\SampleHeader;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Generate Test Reports for multiple Sample Approval jobs belonging to one customer.
 * Shared by Brazil (brl) and UAE (uae).
 */
final class BulkTestRequestReportService
{
    public function __construct(
        private readonly TestRequestReportPdfService $pdfService,
        private readonly JobSampleNumberingService $numbering,
    ) {
    }

    /**
     * @param  list<string>  $batchIds
     * @param  array{
     *     include_reference_method?: bool,
     *     show_specification?: bool,
     *     show_specification_standard?: bool,
     *     show_mu_percent?: bool,
     *     lab_section_id?: string|null
     * }  $options
     * @return array{
     *     generated: list<array{batch_id: string, batch_code: string, report_number: string, online_url: string, filename: string}>,
     *     skipped: list<string>,
     *     customer_id: string|null,
     *     customer_name: string|null
     * }
     */
    public function generate(array $batchIds, User $actor, string $language = 'en', array $options = []): array
    {
        $ids = collect($batchIds)
            ->map(static fn ($id): string => trim((string) $id))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $generated = [];
        $skipped = [];
        $customerId = null;
        $customerName = null;
        $filterLabSectionIds = app(TestRequestReportDataService::class)
            ->normalizeLabSectionIds($options['lab_section_ids'] ?? ($options['lab_section_id'] ?? null));
        $isSectionOnlyPrint = $filterLabSectionIds !== [];

        if ($ids === []) {
            return [
                'generated' => [],
                'skipped' => [],
                'customer_id' => null,
                'customer_name' => null,
            ];
        }

        $batches = SampleHeader::query()
            ->with(['customer:id,name', 'approvers'])
            ->whereIn('id', $ids)
            ->get();

        $customerIds = $batches
            ->map(static fn (SampleHeader $batch): string => trim((string) ($batch->crm_customer_id ?? '')))
            ->filter()
            ->unique()
            ->values();

        if ($customerIds->count() > 1) {
            return [
                'generated' => [],
                'skipped' => ['Select jobs for one customer only. Bulk Test Reports cannot mix customers.'],
                'customer_id' => null,
                'customer_name' => null,
            ];
        }

        if ($customerIds->count() === 1) {
            $customerId = $customerIds->first();
            $customerName = (string) ($batches->first()?->customer?->name ?? '');
        }

        $byId = $batches->keyBy(static fn (SampleHeader $batch): string => (string) $batch->id);

        foreach ($ids as $batchId) {
            $batch = $byId->get($batchId);
            if (! $batch) {
                $skipped[] = $batchId.' (not found)';
                continue;
            }

            if ((string) $batch->status !== 'Sample Approval') {
                $skipped[] = $batch->batch_code.' (not in Sample Approval)';
                continue;
            }

            if (! $batch->hasCompletedSampleApproval()) {
                $skipped[] = $batch->batch_code.' (not approved yet)';
                continue;
            }

            if ($batch->samples()->count() === 0) {
                $skipped[] = $batch->batch_code.' (no samples)';
                continue;
            }

            try {
                $result = DB::transaction(function () use ($batch, $actor, $language, $options, $isSectionOnlyPrint, $filterLabSectionIds): array {
                    if ($isSectionOnlyPrint) {
                        $jobSectionIds = collect($batch->labSectionsForDisplay())
                            ->pluck('id')
                            ->map(static fn ($id): string => (string) $id)
                            ->all();

                        if (array_diff($filterLabSectionIds, $jobSectionIds) !== []) {
                            throw new \RuntimeException('lab section not on this job');
                        }

                        $hasSectionResults = \App\CapturedResult::query()
                            ->where('sample_header_id', $batch->id)
                            ->whereIn('lab_section_id', $filterLabSectionIds)
                            ->exists();

                        if (! $hasSectionResults) {
                            throw new \RuntimeException('no results for selected lab section');
                        }

                        $sequence = max(1, (int) ($batch->test_request_report_sequence ?? 0));
                        $stored = $this->pdfService->generateAndStore(
                            $batch->fresh(['customer', 'sample_type', 'samples']),
                            $sequence,
                            $language,
                            array_merge($options, ['lab_section_ids' => $filterLabSectionIds]),
                        );

                        $reportNumber = app(AmendmentReportConfigurationService::class)
                            ->formatReportNumber((string) $batch->batch_code, $sequence);

                        return [
                            'batch_id' => (string) $batch->id,
                            'batch_code' => (string) $batch->batch_code,
                            'report_number' => $reportNumber,
                            'online_url' => $stored['online_url'],
                            'filename' => $stored['filename'],
                        ];
                    }

                    $ammendment = BatchAmmendment::resolveForBatch($batch);
                    $wasInAmendment = (int) ($batch->in_ammendment_proccess ?? 0) === 1;

                    $nextFromSequence = ((int) ($batch->test_request_report_sequence ?? 0)) + 1;
                    $amendmentVersion = max(1, (int) ($batch->is_amendment ?? 1));
                    $batch->test_request_report_sequence = max($nextFromSequence, $amendmentVersion);

                    if ($wasInAmendment) {
                        $batch->in_ammendment_proccess = 0;
                    }
                    $batch->save();

                    $notes = $ammendment ? (string) $ammendment->reason : null;

                    TestRequestReportRevision::query()->create([
                        'batch_id' => $batch->id,
                        'revision_no' => $batch->test_request_report_sequence,
                        'language' => $language,
                        'notes' => $notes !== '' ? $notes : null,
                        'generated_by' => $actor->id,
                    ]);

                    $this->numbering->syncReportNumbersForBatch($batch, (int) $batch->test_request_report_sequence);

                    $stored = $this->pdfService->generateAndStore(
                        $batch->fresh(['customer', 'sample_type', 'samples']),
                        (int) $batch->test_request_report_sequence,
                        $language,
                        $options,
                        (string) $actor->id,
                    );

                    $reportNumber = app(AmendmentReportConfigurationService::class)
                        ->formatReportNumber((string) $batch->batch_code, (int) $batch->test_request_report_sequence);

                    $batch->batch_report_url = $stored['relative_path'];
                    $batch->batch_report_online_url = $stored['online_url'];
                    $batch->save();

                    return [
                        'batch_id' => (string) $batch->id,
                        'batch_code' => (string) $batch->batch_code,
                        'report_number' => $reportNumber,
                        'online_url' => $stored['online_url'],
                        'filename' => $stored['filename'],
                    ];
                });

                $generated[] = $result;
            } catch (\Throwable $e) {
                $skipped[] = $batch->batch_code.' ('.$e->getMessage().')';
            }
        }

        return [
            'generated' => $generated,
            'skipped' => $skipped,
            'customer_id' => $customerId,
            'customer_name' => $customerName,
        ];
    }
}
