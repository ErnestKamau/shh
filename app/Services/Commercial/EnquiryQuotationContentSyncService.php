<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\QuotationHeader;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\SubmissionForm\SubmissionFormInstanceDocumentAttachmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Rebuild enquiry lab content from a linked billing quotation after in-place quote edits.
 * Preserves staff-entered TRF answers (description, qty/unit, categories, declarations).
 */
final class EnquiryQuotationContentSyncService
{
    /** @var list<string> */
    private const PRESERVED_CONFIG_KEYS = [
        'sample_description',
        'sample_quantity',
        'sample_quantity_unit',
        'location',
        'sampling_point',
        'production_date',
        'expiration_date',
        'batch_number',
        'test_category',
        'test_requirements',
        'parameter_category',
        'field_ph',
        'field_appearance',
        'field_residual_chlorine',
        'field_odor',
        'field_sample_temp',
        'sample_condition',
        'state_of_sample',
        'customer_sample_id',
        'number_of_samples',
    ];

    public function __construct(
        private readonly EnquiryFromQuotationService $enquiryFromQuotationService,
        private readonly QuotationFromEnquiryService $quotationFromEnquiryService,
        private readonly AcceptanceFormSampleConfigService $sampleConfigService,
        private readonly CommercialEnquirySampleLineSync $sampleLineSync,
        private readonly PortalEnquiryFormInstanceSyncService $formInstanceSync,
        private readonly SubmissionFormInstanceDocumentAttachmentService $documentAttachmentService,
    ) {}

    /**
     * @return list<string>
     */
    public function mismatchWarnings(SampleSubmissionRequest $enquiry, ?QuotationHeader $quotation = null): array
    {
        $quotation ??= $enquiry->currentQuotation;
        if ($quotation === null && $enquiry->current_quotation_header_id) {
            $quotation = QuotationHeader::query()->with('details')->find($enquiry->current_quotation_header_id);
        }

        if ($quotation === null) {
            return [];
        }

        $configs = is_array($enquiry->enquiry_sample_configuration)
            ? $enquiry->enquiry_sample_configuration
            : [];

        return $this->quotationFromEnquiryService->quotationMismatchWarnings($configs, $quotation);
    }

    public function hasContentMismatch(SampleSubmissionRequest $enquiry, ?QuotationHeader $quotation = null): bool
    {
        return $this->mismatchWarnings($enquiry, $quotation) !== [];
    }

    public function canSync(SampleSubmissionRequest $enquiry): bool
    {
        if (in_array((string) $enquiry->status, [
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED,
            SampleSubmissionRequest::STATUS_READY_FOR_RECEPTION,
            SampleSubmissionRequest::STATUS_SAMPLE_INTEGRITY_CHECK,
            SampleSubmissionRequest::STATUS_IN_REVIEW,
            SampleSubmissionRequest::STATUS_ACCEPTED,
        ], true)) {
            return false;
        }

        $quotationId = trim((string) ($enquiry->current_quotation_header_id ?? ''));
        if ($quotationId === '') {
            return false;
        }

        $quotation = QuotationHeader::query()->with('details')->find($quotationId);
        if ($quotation === null || (string) $quotation->status !== QuotationApprovalService::HEADER_STATUS_COMPLETE) {
            return false;
        }

        return $this->hasContentMismatch($enquiry, $quotation);
    }

    public function sync(SampleSubmissionRequest $enquiry): SampleSubmissionRequest
    {
        return DB::transaction(function () use ($enquiry): SampleSubmissionRequest {
            $enquiry = SampleSubmissionRequest::query()
                ->lockForUpdate()
                ->find($enquiry->id);

            if ($enquiry === null) {
                throw new RuntimeException('The enquiry no longer exists.');
            }

            if (! $this->canSync($enquiry)) {
                throw new RuntimeException('This request cannot sync from its quotation right now.');
            }

            $quotation = QuotationHeader::query()
                ->with('details')
                ->find($enquiry->current_quotation_header_id);

            if ($quotation === null) {
                throw new RuntimeException('The linked quotation was not found.');
            }

            $previousConfigs = is_array($enquiry->enquiry_sample_configuration)
                ? $enquiry->enquiry_sample_configuration
                : [];

            $numberOfSamples = max(
                1,
                (int) ($enquiry->number_of_samples ?? 0),
                count($previousConfigs),
            );

            $configs = $this->enquiryFromQuotationService->sampleConfigsFromQuotation(
                $quotation,
                $numberOfSamples,
            );
            $configs = $this->mergePreservedConfigFields($configs, $previousConfigs);
            $this->sampleConfigService->validateConfigs($configs);

            $enquiry->enquiry_sample_configuration = $configs;
            $enquiry->number_of_samples = count($configs);
            $enquiry->save();

            $this->sampleLineSync->syncFromSampleConfigs($enquiry, $configs);

            $this->quotationFromEnquiryService->seedEnquirySubcontractFlagsFromQuotation(
                $enquiry->fresh() ?? $enquiry,
                $quotation,
            );

            $enquiry = $enquiry->fresh([
                'customer',
                'requestedAnalyses',
                'submissionFormInstance',
            ]) ?? $enquiry;

            $instance = $enquiry->submissionFormInstance;
            $submit = $instance !== null
                && in_array(strtolower((string) $instance->status), ['submitted'], true);

            $this->formInstanceSync->syncAllSampleTypesFromEnquiry($enquiry, submit: $submit);

            $enquiry = $enquiry->fresh([
                'customer',
                'requestedAnalyses',
                'submissionFormInstance',
                'currentQuotation',
            ]) ?? $enquiry;

            $this->refreshQuotationPdfOnInstance($enquiry, $quotation);

            if ((string) $enquiry->status === SampleSubmissionRequest::STATUS_QUOTATION_SENT) {
                $enquiry->quotation_content_stale_at = now();
                $enquiry->save();
            }

            return $enquiry->fresh([
                'currentQuotation.details',
                'submissionFormInstance',
                'requestedAnalyses',
                'contact',
            ]) ?? $enquiry;
        });
    }

    /**
     * @param  list<array<string, mixed>>  $newConfigs
     * @param  list<array<string, mixed>>  $oldConfigs
     * @return list<array<string, mixed>>
     */
    private function mergePreservedConfigFields(array $newConfigs, array $oldConfigs): array
    {
        $oldByType = [];
        foreach ($oldConfigs as $old) {
            if (! is_array($old)) {
                continue;
            }
            $typeId = trim((string) ($old['sample_type_id'] ?? ''));
            $oldByType[$typeId][] = $old;
        }

        $typeCounters = [];

        foreach ($newConfigs as $index => $config) {
            if (! is_array($config)) {
                continue;
            }

            $typeId = trim((string) ($config['sample_type_id'] ?? ''));
            $typeIndex = $typeCounters[$typeId] ?? 0;
            $typeCounters[$typeId] = $typeIndex + 1;

            $old = $oldByType[$typeId][$typeIndex]
                ?? $oldConfigs[$index]
                ?? null;

            if (! is_array($old)) {
                continue;
            }

            foreach (self::PRESERVED_CONFIG_KEYS as $key) {
                $newValue = $config[$key] ?? null;
                $oldValue = $old[$key] ?? null;

                if ($key === 'number_of_samples') {
                    $oldCount = (int) ($oldValue ?? 0);
                    if ($oldCount > 0) {
                        $newConfigs[$index][$key] = $oldCount;
                    }

                    continue;
                }

                if ($this->isFilled($newValue)) {
                    continue;
                }

                if ($this->isFilled($oldValue)) {
                    $newConfigs[$index][$key] = $oldValue;
                }
            }
        }

        return $newConfigs;
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return trim((string) $value) !== '';
    }

    private function refreshQuotationPdfOnInstance(
        SampleSubmissionRequest $enquiry,
        QuotationHeader $quotation,
    ): void {
        $instance = $enquiry->submissionFormInstance;
        if ($instance === null) {
            return;
        }

        $uploaderId = Auth::id() !== null ? (string) Auth::id() : null;
        if ($uploaderId === null || ! Str::isUuid($uploaderId)) {
            $uploaderId = trim((string) ($quotation->approved_by ?? $quotation->prepared_by_id ?? '')) ?: null;
        }

        try {
            $this->documentAttachmentService->attachQuotation(
                $instance,
                $quotation->fresh() ?? $quotation,
                $uploaderId,
            );
        } catch (Throwable $exception) {
            Log::warning('Failed to refresh quotation PDF after request quotation sync.', [
                'enquiry_id' => $enquiry->id,
                'quotation_id' => $quotation->id,
                'instance_id' => $instance->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
