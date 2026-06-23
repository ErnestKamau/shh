<?php

namespace App\Services\Commercial;

use App\Models\Billing\PricelistCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Facades\DB;

final class CommercialEnquiryFromFormService
{
    public function __construct(
        private SubmissionRequestSampleLineService $sampleLineService,
        private CommercialEnquiryFromTrfService $trfService,
        private CommercialEnquiryFieldMapper $fieldMapper,
        private CommercialEnquirySampleLineSync $lineSync,
    ) {}

    public function isLaboratoryServiceRequestForm(SubmissionFormInstance $instance): bool
    {
        return $this->isCommercialTestRequestForm($instance);
    }

    public function isCommercialTestRequestForm(SubmissionFormInstance $instance): bool
    {
        $instance->loadMissing(['submissionForm', 'testRequestFormInstance']);

        if ($instance->testRequestFormInstance !== null) {
            return true;
        }

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

    public function syncFromSubmittedInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm', 'testRequestFormInstance']);

        if ($instance->testRequestFormInstance !== null) {
            return $this->trfService->syncFromTrfi($instance->testRequestFormInstance);
        }

        return $this->syncFromLegacyFormInstance($instance, SampleSubmissionRequest::STATUS_REQUESTED);
    }

    public function resyncSampleDataFromInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm', 'testRequestFormInstance']);

        if ($instance->testRequestFormInstance !== null) {
            return $this->trfService->resyncSampleDataFromTrfi($instance->testRequestFormInstance);
        }

        $enquiry = SampleSubmissionRequest::query()
            ->where('submission_form_instance_id', $instance->id)
            ->first();

        if ($enquiry === null) {
            return null;
        }

        return DB::transaction(function () use ($enquiry, $instance): SampleSubmissionRequest {
            $this->applyLegacyHeaderFields($enquiry, $instance);
            $lines = $this->sampleLineService->linesForInstance($instance);
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);
            $enquiry->save();

            return $enquiry->fresh(['requestedAnalyses', 'customer', 'submissionFormInstance.testRequestFormInstance']);
        });
    }

    public function syncFromDraftInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        if (! $this->isCommercialTestRequestForm($instance)) {
            return null;
        }

        $instance->loadMissing(['crmCustomer', 'values.element', 'submissionForm', 'testRequestFormInstance']);

        if ($instance->testRequestFormInstance !== null) {
            return $this->trfService->syncFromTrfi($instance->testRequestFormInstance, asDraft: true);
        }

        return $this->syncFromLegacyFormInstance($instance, SampleSubmissionRequest::STATUS_DRAFT);
    }

    private function syncFromLegacyFormInstance(
        SubmissionFormInstance $instance,
        string $status,
    ): SampleSubmissionRequest {
        return DB::transaction(function () use ($instance, $status): SampleSubmissionRequest {
            $enquiry = $this->findOrCreateEnquiry($instance);
            $this->applyLegacyHeaderFields($enquiry, $instance);
            $lines = $this->sampleLineService->linesForInstance($instance);
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);

            if (PricelistCustomer::query()->where('customer_id', $enquiry->crm_customer_id)->exists()) {
                $enquiry->pricing_source = 'contract';
            }

            $enquiry->status = $status;
            $enquiry->source_channel = $this->resolveSourceChannel($instance);
            $enquiry->submission_form_instance_id = $instance->id;
            $enquiry->save();

            return $enquiry->fresh(['requestedAnalyses', 'customer']);
        });
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
            'crm_customer_id' => $instance->crm_customer_id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => $this->resolveSourceChannel($instance),
            'submission_form_instance_id' => $instance->id,
        ]);
    }

    private function resolveSourceChannel(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing('testRequestFormInstance');

        if ($instance->testRequestFormInstance?->source_channel) {
            return (string) $instance->testRequestFormInstance->source_channel;
        }

        if ($instance->testRequestFormInstance?->sampling_schedule_id) {
            return 'scheduled';
        }

        if ($instance->portal_account_id !== null && $instance->portal_account_id !== '') {
            return 'portal';
        }

        return 'walk_in';
    }

    private function applyLegacyHeaderFields(SampleSubmissionRequest $enquiry, SubmissionFormInstance $instance): void
    {
        $this->fieldMapper->applyHeaderFieldsFromIndexedValues($enquiry, $this->indexedValues($instance));
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedValues(SubmissionFormInstance $instance): array
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
}
