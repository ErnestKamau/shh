<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;

/**
 * @deprecated Use CommercialEnquirySyncService directly. Retained as alias during TRF consolidation.
 */
final class CommercialEnquiryFromFormService
{
    public function __construct(
        private CommercialEnquirySyncService $syncService,
    ) {}

    public function isLaboratoryServiceRequestForm(SubmissionFormInstance $instance): bool
    {
        return $this->syncService->isLaboratoryServiceRequestForm($instance);
    }

    public function isCommercialTestRequestForm(SubmissionFormInstance $instance): bool
    {
        return $this->syncService->isCommercialTestRequestForm($instance);
    }

    public function syncFromSubmittedInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        return $this->syncService->syncFromSubmittedInstance($instance);
    }

    public function resyncSampleDataFromInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        return $this->syncService->resyncSampleDataFromInstance($instance);
    }

    public function syncFromDraftInstance(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        return $this->syncService->syncFromDraftInstance($instance);
    }
}
