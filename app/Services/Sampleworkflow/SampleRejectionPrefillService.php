<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\SampleType;

class SampleRejectionPrefillService
{
    public function __construct(
        private readonly AcceptanceFormPricingService $pricingService,
    ) {}

    /**
     * @return array{
     *     request_no: string,
     *     client_name: string,
     *     date_sample_received: ?string,
     *     type_of_sample: string,
     *     number_of_samples: int,
     *     submission_form_instance_id: ?string,
     *     sample_submission_request_id: ?string,
     *     crm_customer_id: ?string
     * }
     */
    public function buildFromSelection(?string $submissionRequestId, ?string $submissionFormInstanceId): array
    {
        $prefill = $this->pricingService->buildPrefillFromSelection($submissionRequestId, $submissionFormInstanceId);

        $requestNo = '';
        $instanceId = $submissionFormInstanceId;
        $requestId = $submissionRequestId;

        if ($submissionFormInstanceId) {
            $instance = SubmissionFormInstance::query()->find($submissionFormInstanceId);
            if ($instance) {
                $requestNo = (string) ($instance->getDocumentControlNumber() ?? $instance->form_number ?? '');
                if ($requestId === null
                    && (string) $instance->target_record_type === SampleSubmissionRequest::class
                    && ! empty($instance->target_record_id)) {
                    $requestId = (string) $instance->target_record_id;
                }
            }
        }

        if ($requestNo === '' && $submissionRequestId) {
            $request = SampleSubmissionRequest::query()->find($submissionRequestId);
            $requestNo = (string) ($request?->formatted_number ?? '');
        }

        $typeName = '';
        if (! empty($prefill['sample_type_id'])) {
            $typeName = (string) (SampleType::query()->find($prefill['sample_type_id'])?->name ?? '');
        }

        return [
            'request_no' => $requestNo,
            'client_name' => (string) ($prefill['customer_name'] ?? ''),
            'date_sample_received' => $prefill['request_date'] ?? null,
            'type_of_sample' => $typeName,
            'number_of_samples' => max(1, (int) ($prefill['number_of_samples'] ?? 1)),
            'submission_form_instance_id' => $instanceId,
            'sample_submission_request_id' => $requestId,
            'crm_customer_id' => $prefill['customer_id'] ?? null,
        ];
    }
}
