<?php

namespace App\Services\Commercial;

use App\Models\Billing\PricelistCustomer;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Models\TestRequestFormInstance;
use Illuminate\Support\Facades\DB;

final class CommercialEnquiryFromTrfService
{
    public function __construct(
        private CommercialEnquiryFieldMapper $fieldMapper,
        private CommercialEnquirySampleLineSync $lineSync,
    ) {}

    public function syncFromTrfi(TestRequestFormInstance $trfi, bool $asDraft = false): SampleSubmissionRequest
    {
        $trfi->loadMissing([
            'testRequestForm.sampleType',
            'crmCustomer',
            'submissionFormInstance',
        ]);

        return DB::transaction(function () use ($trfi, $asDraft): SampleSubmissionRequest {
            $enquiry = $this->findOrCreateEnquiry($trfi);
            $formData = is_array($trfi->form_data) ? $trfi->form_data : [];

            $this->fieldMapper->applyHeaderFieldsFromFormData($enquiry, $formData);

            $lines = $this->lineSync->linesForEnquirySync(
                $enquiry,
                $trfi,
                $trfi->submissionFormInstance,
            );
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);

            if ($trfi->crm_customer_id && PricelistCustomer::query()->where('customer_id', $trfi->crm_customer_id)->exists()) {
                $enquiry->pricing_source = 'contract';
            }

            $enquiry->status = $asDraft
                ? SampleSubmissionRequest::STATUS_DRAFT
                : SampleSubmissionRequest::STATUS_REQUESTED;
            $enquiry->source_channel = (string) ($trfi->source_channel ?? 'walk_in');
            $enquiry->crm_customer_id = $trfi->crm_customer_id ?? $enquiry->crm_customer_id;
            $enquiry->test_request_form_instance_id = $trfi->id;

            if ($trfi->submission_form_instance_id) {
                $enquiry->submission_form_instance_id = $trfi->submission_form_instance_id;
            }

            $enquiry->save();

            if ($trfi->sample_submission_request_id !== $enquiry->id) {
                $trfi->update(['sample_submission_request_id' => $enquiry->id]);
            }

            return $enquiry->fresh(['requestedAnalyses', 'customer', 'testRequestFormInstance']);
        });
    }

    public function resyncSampleDataFromTrfi(TestRequestFormInstance $trfi): ?SampleSubmissionRequest
    {
        $trfi->loadMissing(['testRequestForm', 'submissionFormInstance']);

        $enquiry = SampleSubmissionRequest::query()
            ->where('test_request_form_instance_id', $trfi->id)
            ->first();

        if ($enquiry === null && $trfi->submission_form_instance_id) {
            $enquiry = SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $trfi->submission_form_instance_id)
                ->first();
        }

        if ($enquiry === null) {
            return null;
        }

        return DB::transaction(function () use ($enquiry, $trfi): SampleSubmissionRequest {
            $formData = is_array($trfi->form_data) ? $trfi->form_data : [];
            $this->fieldMapper->applyHeaderFieldsFromFormData($enquiry, $formData);

            $lines = $this->lineSync->linesForEnquirySync(
                $enquiry,
                $trfi,
                $trfi->submissionFormInstance,
            );
            $this->lineSync->syncSampleLines($enquiry, $lines);
            $this->lineSync->syncRequestedAnalyses($enquiry, $lines);

            $enquiry->test_request_form_instance_id = $trfi->id;
            $enquiry->save();

            return $enquiry->fresh(['requestedAnalyses', 'customer', 'testRequestFormInstance']);
        });
    }

    private function findOrCreateEnquiry(TestRequestFormInstance $trfi): SampleSubmissionRequest
    {
        if ($trfi->sample_submission_request_id) {
            $existing = SampleSubmissionRequest::query()->find($trfi->sample_submission_request_id);
            if ($existing !== null) {
                return $existing;
            }
        }

        $existing = SampleSubmissionRequest::query()
            ->where('test_request_form_instance_id', $trfi->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        if ($trfi->submission_form_instance_id) {
            $existing = SampleSubmissionRequest::query()
                ->where('submission_form_instance_id', $trfi->submission_form_instance_id)
                ->first();

            if ($existing !== null) {
                return $existing;
            }

            $instance = $trfi->submissionFormInstance;
            if ($instance instanceof SubmissionFormInstance) {
                $portalRequestId = trim((string) ($instance->portal_request_id ?? ''));
                if ($portalRequestId !== '') {
                    $linkedViaPortal = SampleSubmissionRequest::query()->find($portalRequestId);
                    if ($linkedViaPortal !== null) {
                        return $linkedViaPortal;
                    }
                }
            }
        }

        return SampleSubmissionRequest::query()->create([
            'crm_customer_id' => $trfi->crm_customer_id,
            'status' => SampleSubmissionRequest::STATUS_REQUESTED,
            'source_channel' => (string) ($trfi->source_channel ?? 'walk_in'),
            'test_request_form_instance_id' => $trfi->id,
            'submission_form_instance_id' => $trfi->submission_form_instance_id,
        ]);
    }
}
