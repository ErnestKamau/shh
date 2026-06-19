<?php

namespace App\Services\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;

/**
 * Resolves job/batch codes for the portal acceptance pipeline.
 */
class AcceptanceFormBatchCodeService
{
    public function __construct(
        private readonly JobSampleNumberingService $numberingService,
    ) {}

    public function resolveBatchCodeForAcceptanceForm(
        AnalysisAcceptanceForm $form,
        ?string $preferredZoneId = null,
    ): string {
        $instance = $form->submission_form_instance_id
            ? SubmissionFormInstance::query()->with('batches')->find($form->submission_form_instance_id)
            : null;

        if ($instance?->batches?->isNotEmpty()) {
            return (string) $instance->batches->first()->batch_code;
        }

        return $this->numberingService->generateJobNumber();
    }

    public function resolveBatchCode(SubmissionFormInstance $instance): string
    {
        $instance->loadMissing('batches');

        if ($instance->batches->isNotEmpty()) {
            return (string) $instance->batches->first()->batch_code;
        }

        return $this->numberingService->generateJobNumber();
    }
}
