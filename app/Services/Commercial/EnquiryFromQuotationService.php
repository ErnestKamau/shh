<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationDetailAnalysisSplit;
use App\QuotationHeader;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class EnquiryFromQuotationService
{
    public const SOURCE_CHANNEL = 'quotation';

    public const INTENT_PREPARE = 'prepare';

    public const INTENT_ALREADY_SENT = 'already_sent';

    public const INTENT_ACCEPTED = 'accepted';

    public function __construct(
        private readonly QuotationFromEnquiryService $quotationService,
        private readonly AcceptanceFormSampleConfigService $sampleConfigService,
        private readonly CommercialEnquirySampleLineSync $sampleLineSync,
        private readonly PortalEnquiryFormInstanceSyncService $formInstanceSync,
        private readonly EnquiryReceptionReadinessService $receptionReadinessService,
    ) {}

    /**
     * @param  array{
     *     number_of_samples?: int,
     *     reference_number?: string|null,
     *     date_expected?: string|null,
     *     sample_description?: string|null,
     *     enquiry_notes?: string|null,
     *     creation_intent?: string|null,
     *     client_po_number?: string|null,
     *     po_skipped?: bool|null
     * }  $intake
     */
    public function create(
        QuotationHeader $sourceQuotation,
        array $intake,
        string $creationToken,
    ): SampleSubmissionRequest {
        $creationToken = trim($creationToken);
        if ($creationToken === '') {
            throw new RuntimeException('A quotation enquiry creation token is required.');
        }

        $existing = SampleSubmissionRequest::query()
            ->where('quotation_creation_token', $creationToken)
            ->first();
        if ($existing !== null) {
            return $existing->load(['currentQuotation.details', 'submissionFormInstance']);
        }

        return DB::transaction(function () use ($sourceQuotation, $intake, $creationToken): SampleSubmissionRequest {
            $source = QuotationHeader::query()
                ->with('details')
                ->lockForUpdate()
                ->find($sourceQuotation->id);

            if ($source === null) {
                throw new RuntimeException('The selected quotation no longer exists.');
            }

            $existing = SampleSubmissionRequest::query()
                ->where('quotation_creation_token', $creationToken)
                ->first();
            if ($existing !== null) {
                return $existing->load(['currentQuotation.details', 'submissionFormInstance']);
            }

            $lines = $this->eligibleQuotationLines($source);
            $numberOfSamples = max(
                1,
                (int) ($intake['number_of_samples'] ?? $this->inferPhysicalSampleCount($lines)),
            );
            $intent = $this->normalizeIntent($intake['creation_intent'] ?? null);

            $enquiry = SampleSubmissionRequest::query()->create([
                'crm_customer_id' => $source->crm_customer_id,
                'crm_contact_id' => $source->crm_customer_contact_id,
                'status' => SampleSubmissionRequest::STATUS_QUOTATION_READY_TO_SEND,
                'source_channel' => self::SOURCE_CHANNEL,
                'created_from_quotation_header_id' => $source->id,
                'quotation_creation_token' => $creationToken,
                'pricing_source' => 'existing_quotation',
                'reference_number' => $this->nullableString($intake['reference_number'] ?? null),
                'date_expected' => $this->nullableString($intake['date_expected'] ?? null),
                'sample_description' => $this->nullableString($intake['sample_description'] ?? null),
                'enquiry_notes' => $this->nullableString($intake['enquiry_notes'] ?? null),
                'request_date_of_service' => now()->toDateString(),
                'number_of_samples' => $numberOfSamples,
                'sample_type_id' => $lines[0]['sample_type_id'],
                'batch_sample_type_id' => $lines[0]['sample_type_id'],
            ]);

            $ownedQuotation = $this->cloneQuotationForEnquiry($source, $enquiry);
            $enquiry->current_quotation_header_id = $ownedQuotation->id;
            $enquiry->save();

            $configs = $this->buildSampleConfigs($lines, $numberOfSamples);
            $sampleDescription = $this->nullableString($intake['sample_description'] ?? null);
            if ($sampleDescription !== null) {
                foreach ($configs as $index => $config) {
                    $configs[$index]['sample_description'] = $sampleDescription;
                }
            }
            $this->sampleConfigService->validateConfigs($configs);

            $enquiry->enquiry_sample_configuration = $configs;
            $enquiry->number_of_samples = count($configs);
            $enquiry->save();
            $this->sampleLineSync->syncFromSampleConfigs($enquiry, $configs);

            $this->formInstanceSync->syncFromEnquiry($enquiry->fresh([
                'customer',
                'requestedAnalyses',
            ]), submit: false);

            $enquiry = $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
            ]) ?? $enquiry;

            return $this->applyCreationIntent(
                $enquiry,
                $enquiry->currentQuotation ?? $ownedQuotation,
                $intent,
                $intake,
            );
        }, attempts: 3);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function eligibleQuotationLines(QuotationHeader $quotation): array
    {
        $quotation->loadMissing('details');

        if ((string) $quotation->status !== QuotationApprovalService::HEADER_STATUS_COMPLETE) {
            throw new RuntimeException('Only completed quotations can be used to create an enquiry.');
        }

        if (strcasecmp((string) ($quotation->quotation_type ?? ''), 'Analysis') !== 0) {
            throw new RuntimeException('Only analysis quotations can be converted into laboratory enquiries.');
        }

        if (trim((string) ($quotation->crm_customer_id ?? '')) === '') {
            throw new RuntimeException('The quotation must be linked to a CRM customer.');
        }

        if ($quotation->expiring_date === null
            || Carbon::parse($quotation->expiring_date)->startOfDay()->lt(now()->startOfDay())) {
            throw new RuntimeException('The quotation has expired and cannot create a new enquiry.');
        }

        if ($quotation->details->isEmpty()) {
            throw new RuntimeException('The quotation has no analysis lines.');
        }

        $lines = $this->quotationService->buildInlineLinesFromQuotationHeader($quotation);
        if ($lines === []) {
            throw new RuntimeException('The quotation has no laboratory analyses that can be mapped.');
        }

        foreach ($lines as $line) {
            if (trim((string) ($line['sample_type_id'] ?? '')) === '') {
                throw new RuntimeException('Every quotation line must have a sample type before creating an enquiry.');
            }

            $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
            $packageElementIds = array_values(array_filter(
                is_array($line['package_element_ids'] ?? null) ? $line['package_element_ids'] : [],
                static fn (mixed $id): bool => trim((string) $id) !== '',
            ));

            if ($elementId === '' && $packageElementIds === []) {
                throw new RuntimeException(
                    'Every quotation line must contain explicitly selected parameters before creating an enquiry.'
                );
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     */
    public function inferPhysicalSampleCount(array $lines): int
    {
        $grouped = collect($lines)->groupBy(
            static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')),
        );

        $total = 0;
        foreach ($grouped as $group) {
            $total += max(1, (int) $group->max(
                static fn (array $line): int => max(
                    1,
                    (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1),
                )
            ));
        }

        return max(1, $total);
    }

    /**
     * @return list<string>
     */
    public static function creationIntents(): array
    {
        return [
            self::INTENT_PREPARE,
            self::INTENT_ALREADY_SENT,
            self::INTENT_ACCEPTED,
        ];
    }

    private function normalizeIntent(?string $intent): string
    {
        $intent = trim((string) $intent);
        if ($intent === '' || ! in_array($intent, self::creationIntents(), true)) {
            return self::INTENT_PREPARE;
        }

        return $intent;
    }

    /**
     * @param  array<string, mixed>  $intake
     */
    private function applyCreationIntent(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $ownedQuotation,
        string $intent,
        array $intake,
    ): SampleSubmissionRequest {
        if ($intent === self::INTENT_ALREADY_SENT) {
            $sentAt = now();
            $ownedQuotation->sent_to_customer_at = $sentAt;
            $ownedQuotation->save();

            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_SENT;
            $enquiry->quotation_first_sent_to_customer_at = $sentAt;
            $enquiry->save();

            return $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
            ]) ?? $enquiry;
        }

        if ($intent === self::INTENT_ACCEPTED) {
            $sentAt = $ownedQuotation->sent_to_customer_at ?? now();
            $ownedQuotation->sent_to_customer_at = $sentAt;
            $ownedQuotation->is_approved = 1;
            $ownedQuotation->is_complete = 1;
            $ownedQuotation->save();

            $enquiry->quotation_first_sent_to_customer_at = $enquiry->quotation_first_sent_to_customer_at ?? $sentAt;
            $enquiry->status = SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED;
            $enquiry->save();

            $poNumber = $this->nullableString($intake['client_po_number'] ?? null)
                ?? $this->nullableString($intake['reference_number'] ?? null);

            return $this->receptionReadinessService->markReadyForReception(
                $enquiry->fresh() ?? $enquiry,
                (string) $ownedQuotation->id,
                [
                    'client_po_number' => $poNumber,
                    'po_skipped' => filter_var($intake['po_skipped'] ?? ($poNumber === null), FILTER_VALIDATE_BOOLEAN),
                ],
            )->load(['currentQuotation.details', 'submissionFormInstance', 'requestedAnalyses']);
        }

        return $enquiry;
    }

    private function cloneQuotationForEnquiry(
        QuotationHeader $source,
        SampleSubmissionRequest $enquiry,
    ): QuotationHeader {
        $clone = $source->replicate([
            'quote_number',
            'laboratory_ref',
            'sample_submission_request_id',
            'source_quotation_header_id',
            'revision_of_quotation_header_id',
            'sent_to_customer_at',
            'upload_url',
            'customer_acceptance_signature',
            'customer_acceptance_signer_name',
            'customer_acceptance_signed_at',
            'customer_acceptance_contact_id',
            'customer_acceptance_channel',
            'approval_requested_at',
            'approval_requested_by',
            'approval_decision_at',
            'approval_comments',
        ]);

        $clone->quote_number = $this->ownedQuotationNumber($source, $enquiry);
        $clone->laboratory_ref = $clone->quote_number;
        $clone->sample_submission_request_id = $enquiry->id;
        $clone->source_quotation_header_id = $source->id;
        $clone->from_enquiry = true;
        $clone->status = QuotationApprovalService::HEADER_STATUS_COMPLETE;
        $clone->is_draft = 0;
        $clone->is_complete = 1;
        $clone->is_approved = 1;
        $clone->save();

        foreach ($source->details as $detail) {
            $clonedDetail = $detail->replicate(['quotation_header_id']);
            $clonedDetail->quotation_header_id = $clone->id;
            $clonedDetail->save();

            QuotationDetailAnalysisSplit::query()
                ->where('quotation_detail_id', $detail->id)
                ->each(function (QuotationDetailAnalysisSplit $split) use ($clonedDetail): void {
                    QuotationDetailAnalysisSplit::query()->create([
                        'quotation_detail_id' => $clonedDetail->id,
                        'analysis_type_id' => $split->analysis_type_id,
                    ]);
                });
        }

        return $clone->fresh(['details']) ?? $clone;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @return list<array<string, mixed>>
     */
    private function buildSampleConfigs(array $lines, int $numberOfSamples): array
    {
        $groups = collect($lines)->groupBy(
            static fn (array $line): string => trim((string) ($line['sample_type_id'] ?? '')),
        );
        $singleType = $groups->count() === 1;
        $prefill = [];
        $rowIndex = 0;

        foreach ($groups as $sampleTypeId => $groupLines) {
            $group = $groupLines->values();
            $elementIds = $group
                ->flatMap(function (array $line): array {
                    $ids = is_array($line['package_element_ids'] ?? null)
                        ? $line['package_element_ids']
                        : [];
                    $elementId = trim((string) ($line['analysis_element_id'] ?? ''));
                    if ($elementId !== '') {
                        array_unshift($ids, $elementId);
                    }

                    return $ids;
                })
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();
            $analysisTypeIds = $group
                ->pluck('analysis_type_id')
                ->map(static fn (mixed $id): string => trim((string) $id))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $groupSampleCount = $singleType
                ? $numberOfSamples
                : max(1, (int) $group->max(
                    static fn (array $line): int => max(
                        1,
                        (int) ($line['physical_sample_count'] ?? $line['quantity'] ?? 1),
                    )
                ));

            $first = $group->first();
            $prefill[] = [
                'row_index' => $rowIndex,
                'sample_type_id' => $sampleTypeId,
                'analysis_type_id' => $analysisTypeIds[0] ?? null,
                'analysis_element_id' => $elementIds[0] ?? null,
                'parameter_label' => (string) ($first['parameter_label'] ?? 'Parameter'),
                'number_of_samples' => $groupSampleCount,
                'attributes' => [
                    'analysis_element_ids' => $elementIds,
                    'analysis_type_ids' => $analysisTypeIds,
                ],
            ];
            $rowIndex++;
        }

        return $this->sampleConfigService->normalizeConfigsAnalysisTypeIds(
            $this->sampleConfigService->buildConfigsFromPrefill($prefill),
        );
    }

    private function ownedQuotationNumber(
        QuotationHeader $source,
        SampleSubmissionRequest $enquiry,
    ): string {
        $base = trim((string) ($source->quote_number ?? 'QUOTE'));
        $candidate = $base.'-E'.strtoupper(substr(str_replace('-', '', (string) $enquiry->id), 0, 8));

        if (! QuotationHeader::query()->where('quote_number', $candidate)->exists()) {
            return $candidate;
        }

        return $candidate.'-'.strtoupper(substr(str_replace('-', '', (string) $enquiry->id), -4));
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
