<?php

namespace App\Services\Commercial;

use App\Models\SampleSubmissionRequest;
use App\Models\TestRequestFormInstance;

/**
 * @deprecated-remove TRF_LAYER_MANIFEST.md Phase 1
 * Replaced by CommercialEnquirySyncService.
 */
final class CommercialEnquiryFromTrfService
{
    public function __construct(
        private CommercialEnquirySyncService $syncService,
    ) {}

    public function syncFromTrfi(TestRequestFormInstance $trfi, bool $asDraft = false): SampleSubmissionRequest
    {
        $trfi->loadMissing(['submissionFormInstance']);

        $instance = $trfi->submissionFormInstance;
        if ($instance === null) {
            throw new \RuntimeException('Deprecated TRF sync requires a linked submission form instance.');
        }

        $enquiry = $this->syncService->syncFromSubmittedInstance($instance);
        if ($enquiry === null) {
            throw new \RuntimeException('Commercial enquiry sync failed for submission form instance '.$instance->id);
        }

        return $enquiry;
    }

    public function resyncSampleDataFromTrfi(TestRequestFormInstance $trfi): ?SampleSubmissionRequest
    {
        $trfi->loadMissing(['submissionFormInstance']);

        $instance = $trfi->submissionFormInstance;
        if ($instance === null) {
            return null;
        }

        return $this->syncService->resyncSampleDataFromInstance($instance);
    }
}
