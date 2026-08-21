<?php

namespace App\Services\Commercial;

use App\Models\CRM\CRMCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Facades\DB;

final class CommercialEnquirySyncService
{
    public const SOURCE_PORTAL = 'portal';

    public const SOURCE_WALK_IN = 'walk_in';

    public const SOURCE_SCHEDULED = 'scheduled';

    public const SOURCE_OFFLINE = 'offline';

    public const SOURCE_STAFF = 'staff';

    public static function isOfflineChannel(?string $channel): bool
    {
        return strtolower(trim((string) $channel)) === self::SOURCE_OFFLINE;
    }

    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
        private CommercialEnquiryFieldMapper $fieldMapper,
        private CommercialEnquirySampleLineSync $lineSync,
        private ContractCustomerService $contractCustomerService,
        private QuotationFromEnquiryService $quotationFromEnquiryService,
        private EnquiryReceptionReadinessService $receptionReadinessService,
    ) {}

    public function isLaboratoryServiceRequestForm(SubmissionFormInstance $instance): bool
    {
        return $this->isCommercialTestRequestForm($instance);
    }

    public function isCommercialTestRequestForm(SubmissionFormInstance $instance): bool
    {
        $instance->loadMissing(['submissionForm']);

        $code = strtoupper((string) ($instance->submissionForm->document_code ?? ''));
        $name = strtolower((string) ($instance->submissionForm->name ?? ''));

        if ($code === 'LSR-001' || str_contains($name, 'laboratory service request')) {
            return true;
        }

        if (str_starts_with($code, 'TRF-')) {
            return true;
        }

        if (str_contains($name, 'test request form')) {
            return true;
        }

        $form = $instance->submissionForm;

        return $form->form_type === 'template'
            && (bool) $form->is_customer_portal_form
            && str_starts_with($code, 'TRF-');
    }

    public function syncFromInstance(SubmissionFormInstance $instance, bool $asDraft = false): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm']);

        return $this->syncFromFormInstance($instance, $asDraft
            ? SampleSubmissionRequest::STATUS_DRAFT
            : SampleSubmissionRequest::STATUS_REQUESTED);
    }

    public function syncFromSubmittedInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        return $this->syncFromInstance($instance, asDraft: false);
    }

    public function syncFromDraftInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        return $this->syncFromInstance($instance, asDraft: true);
    }

    public function resyncSampleDataFromInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm']);

        $enquiry = SampleSubmissionRequest::query()
            ->where('submission_form_instance_id', $instance->id)
            ->first();

        if ($enquiry === null) {
            return null;
        }

        return DB::transaction(function () use ($enquiry, $instance): SampleSubmissionRequest {
            $this->applyHeaderFields($enquiry, $instance);
            $lines = $this->sampleLineService->linesForInstance($instance);
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);
            $enquiry->submission_form_instance_id = $instance->id;
            $enquiry->save();

            return $enquiry->fresh(['requestedAnalyses', 'customer', 'submissionFormInstance']);
        });
    }

    private function syncFromFormInstance(SubmissionFormInstance $instance, string $status): SampleSubmissionRequest
    {
        return DB::transaction(function () use ($instance, $status): SampleSubmissionRequest {
            $enquiry = $this->findOrCreateEnquiry($instance);
            $this->applyHeaderFields($enquiry, $instance);
            $lines = $this->sampleLineService->linesForInstance($instance);
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);

            if ($instance->crm_customer_id && $this->contractCustomerService->hasAssignedPricelist((string) $instance->crm_customer_id)) {
                $enquiry->pricing_source = 'customer_pricelist';
            }

            $preserveQuotationWorkflowStatus = $enquiry->created_from_quotation_header_id !== null
                && $enquiry->current_quotation_header_id !== null
                && in_array((string) $enquiry->status, SampleSubmissionRequest::COMMERCIAL_PIPELINE_STATUSES, true);
            if (! $preserveQuotationWorkflowStatus) {
                $enquiry->status = $status;
            }
            $enquiry->source_channel = $this->resolveSourceChannel($instance);
            $resolvedCustomerId = app(CommercialEnquiryCustomerResolver::class)->resolveCustomerId($enquiry)
                ?? $this->resolveCrmCustomerIdFromInstance($instance);
            $enquiry->crm_customer_id = $instance->crm_customer_id ?? $resolvedCustomerId ?? $enquiry->crm_customer_id;
            $enquiry->submission_form_instance_id = $instance->id;
            $this->fieldMapper->mergeContractMetadataIntoCollectionData(
                $enquiry,
                $this->contractCustomerService->contractIntakeMetadata(
                    $enquiry->crm_customer_id !== null ? (string) $enquiry->crm_customer_id : null
                ),
            );
            $enquiry->save();

            // Link walk-in/portal TRF so later Process Enquiry sync reuses it
            // instead of creating a blank sibling instance.
            if (trim((string) ($instance->portal_request_id ?? '')) !== (string) $enquiry->id) {
                $instance->portal_request_id = (string) $enquiry->id;
                $instance->save();
            }

            $asDraft = $status === SampleSubmissionRequest::STATUS_DRAFT;

            if (! $asDraft && $this->shouldAutoMarkReadyForReception($enquiry)) {
                if (! $this->contractCustomerService->hasAssignedPricelist((string) $enquiry->crm_customer_id)) {
                    $enquiry->pricing_source = $this->contractCustomerService->isScheduledEnquiry($enquiry)
                        ? 'sampling_contract'
                        : ($enquiry->pricing_source ?: 'customer_pricelist');
                    $enquiry->save();
                }

                $header = $this->quotationFromEnquiryService->createInternalContractQuotation(
                    $enquiry->fresh(['requestedAnalyses', 'customer', 'contact'])
                );
                $enquiry = $this->receptionReadinessService->markReadyForReception(
                    $enquiry->fresh(),
                    (string) $header->id,
                    ['po_skipped' => true],
                );
            }

            return $enquiry->fresh(['requestedAnalyses', 'customer', 'submissionFormInstance']);
        });
    }

    private function shouldAutoMarkReadyForReception(SampleSubmissionRequest $enquiry): bool
    {
        return $this->contractCustomerService->isScheduledEnquiry($enquiry)
            || self::isOfflineChannel((string) ($enquiry->source_channel ?? ''));
    }

    private function findOrCreateEnquiry(SubmissionFormInstance $instance): SampleSubmissionRequest
    {
        $existing = SampleSubmissionRequest::query()
            ->where('submission_form_instance_id', $instance->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $portalRequestId = trim((string) ($instance->portal_request_id ?? ''));
        if ($portalRequestId !== '') {
            $linkedViaPortal = SampleSubmissionRequest::query()->find($portalRequestId);
            if ($linkedViaPortal !== null) {
                return $linkedViaPortal;
            }
        }

        return SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $instance->crm_customer_id ?? $this->resolveCrmCustomerIdFromInstance($instance),
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => $this->resolveSourceChannel($instance),
            'submission_form_instance_id' => $instance->id,
        ]);
    }

    private function resolveSourceChannel(SubmissionFormInstance $instance): string
    {
        $channel = trim((string) ($instance->source_channel ?? ''));
        if ($channel !== '') {
            return $channel;
        }

        if ($instance->sampling_schedule_id) {
            return self::SOURCE_SCHEDULED;
        }

        if ($instance->portal_account_id !== null && $instance->portal_account_id !== '') {
            return self::SOURCE_PORTAL;
        }

        return self::SOURCE_WALK_IN;
    }

    private function applyHeaderFields(SampleSubmissionRequest $enquiry, SubmissionFormInstance $instance): void
    {
        $this->fieldMapper->applyHeaderFieldsFromIndexedValues($enquiry, $this->indexedScalarValues($instance));
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedScalarValues(SubmissionFormInstance $instance): array
    {
        $values = [];

        foreach ($instance->values as $row) {
            $name = (string) ($row->element->name ?? '');
            if ($name === '') {
                continue;
            }

            if ($row->array_index === null) {
                $values[$name] = $row->value;
            }
        }

        return $values;
    }

    private function resolveCrmCustomerIdFromInstance(SubmissionFormInstance $instance): ?string
    {
        if (! empty($instance->crm_customer_id)) {
            return (string) $instance->crm_customer_id;
        }

        foreach ($this->indexedScalarValues($instance) as $key => $value) {
            if (! in_array($key, ['customer_name', 'client_name', 'customer', 'client'], true)) {
                continue;
            }

            $name = trim((string) $value);
            if ($name === '') {
                continue;
            }

            $customerId = CRMCustomer::query()
                ->whereRaw('name ILIKE ?', [$name])
                ->value('id');

            if ($customerId !== null) {
                return (string) $customerId;
            }
        }

        return null;
    }
}
