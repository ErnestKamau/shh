<?php

namespace App\Services\TestRequestForm;

use App\Models\SubmissionForm;
use App\Models\SubmissionFormInstance;

final class TestRequestFormSubmissionContext
{
    public function __construct(
        public string $sourceChannel,
        public ?string $crmCustomerId = null,
        public ?string $portalAccountId = null,
        public ?string $submittedBy = null,
        public ?string $samplingScheduleId = null,
        public ?SubmissionFormInstance $existingSubmissionFormInstance = null,
        public ?SubmissionForm $portalSubmissionForm = null,
        public ?string $zoneId = null,
        public ?string $receivingLabId = null,
        public bool $syncEnquiry = true,
        public bool $generatePdf = true,
        public bool $isDraft = false,
        public bool $markShadowAsReceived = false,
    ) {}
}
