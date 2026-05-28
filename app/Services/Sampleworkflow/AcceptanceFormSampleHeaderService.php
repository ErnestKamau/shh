<?php

namespace App\Services\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AcceptanceFormSampleHeaderService
{
    public function __construct(
        private readonly AcceptanceFormBatchCodeService $batchCodeService,
        private readonly SampleReceiptNotificationService $receiptNotificationService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildCreateAttributes(
        AnalysisAcceptanceForm $form,
        string $primarySampleTypeId,
        ?string $primaryZoneId,
        ?string $crmUnitId,
        string $crmUnitName,
        ?string $createdByUserId,
    ): array {
        $instance = $form->submission_form_instance_id
            ? SubmissionFormInstance::query()
                ->with(['crmCustomer', 'submittedBy', 'values.element'])
                ->find($form->submission_form_instance_id)
            : null;

        $portalRequest = $instance
            ? $this->receiptNotificationService->findLinkedSubmissionRequest($instance)
            : null;

        if ($portalRequest === null && $form->sample_submission_request_id) {
            $portalRequest = SampleSubmissionRequest::query()
                ->with(['customer'])
                ->find((string) $form->sample_submission_request_id);
        }

        $receiptPayload = is_array($form->receipt_notification_payload)
            ? $form->receipt_notification_payload
            : [];

        $mappedHeader = $this->extractMappedHeaderFieldsFromInstance($instance);
        $elementValues = $this->extractElementValuesByName($instance);

        $batchCode = $this->batchCodeService->resolveBatchCodeForAcceptanceForm($form, $primaryZoneId);

        $reviewStage = SampleAnalysisStage::query()
            ->where('sample_workflow', 'Samples Request Review')
            ->orderBy('level')
            ->first();

        $receiptDate = $this->resolveReceiptDate($form, $receiptPayload, $portalRequest, $instance, $mappedHeader);
        $submitBy = $this->firstNonEmptyString([
            $receiptPayload['submitter_name'] ?? null,
            $mappedHeader['submit_by'] ?? null,
            $elementValues['submitting_personnel'] ?? null,
            $form->customer_signer_name,
            $portalRequest?->submitting_officer_full_name,
            $portalRequest?->submitted_by_full_name,
            $instance?->submittedBy?->name,
        ]);

        $description = $this->firstNonEmptyString([
            $receiptPayload['sample_description'] ?? null,
            $elementValues['nature_of_sample'] ?? null,
            $mappedHeader['description'] ?? null,
            $portalRequest?->description_of_samples,
            $this->sampleTypeNamesFromInstance($instance),
        ]);

        $batchScope = $this->resolveModeOfService(
            $form,
            $elementValues,
            $mappedHeader
        );

        $referenceNumber = $this->firstNonEmptyString([
            $mappedHeader['reference_number'] ?? null,
            $batchCode,
        ]);

        return [
            'batch_code' => $batchCode,
            'status' => 'Samples Request Review',
            'crm_customer_id' => $form->crm_customer_id,
            'crm_unit_id' => $crmUnitId,
            'crm_unit_name' => $crmUnitName,
            'sample_type_id' => $primarySampleTypeId !== '' ? $primarySampleTypeId : null,
            'zone_id' => $primaryZoneId,
            'processing_zone_id' => $primaryZoneId,
            'receipt_date' => $receiptDate,
            'date_collected' => $form->date_of_sampling?->format('Y-m-d') ?? $this->formatDateOnly($receiptDate),
            'priority' => $this->normalizePriority((string) $form->mode_of_work),
            'batch_scope' => $batchScope,
            'submit_by' => $submitBy,
            'description' => $description,
            'reason_for_submission' => $this->firstNonEmptyString([
                $mappedHeader['reason_for_submission'] ?? null,
                $elementValues['purpose'] ?? null,
            ]),
            'batch_instructions' => $this->firstNonEmptyString([
                $mappedHeader['batch_instructions'] ?? null,
                $elementValues['safety_precautions'] ?? null,
            ]),
            'reference_number' => $referenceNumber,
            'is_routine' => false,
            'routine_frequency' => 0,
            'is_client_order' => 1,
            'submission_form_instance_id' => $form->submission_form_instance_id,
            'sample_tracking_stage' => $reviewStage?->id,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function extractMappedHeaderFieldsFromInstance(?SubmissionFormInstance $instance): array
    {
        if ($instance === null) {
            return [];
        }

        $fields = [];

        foreach ($instance->values as $instanceValue) {
            $element = $instanceValue->element;
            if ($element === null || ! $element->is_mapped) {
                continue;
            }

            if (strtolower((string) ($element->mapping_table ?? '')) !== 'sample_headers') {
                continue;
            }

            $field = trim((string) ($element->mapping_field ?? ''));
            if ($field === '') {
                continue;
            }

            $display = trim((string) $instance->resolveDisplayValue($element, $instanceValue->value));
            if ($display === '' && $instanceValue->value !== null && $instanceValue->value !== '') {
                $display = trim((string) $instanceValue->value);
            }

            if ($display !== '') {
                $fields[$field] = $display;
            }
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    private function extractElementValuesByName(?SubmissionFormInstance $instance): array
    {
        if ($instance === null) {
            return [];
        }

        $values = [];

        foreach ($instance->values as $instanceValue) {
            $element = $instanceValue->element;
            if ($element === null) {
                continue;
            }

            $name = Str::snake((string) ($element->name ?? ''));
            if ($name === '') {
                continue;
            }

            $display = trim((string) $instance->resolveDisplayValue($element, $instanceValue->value));
            if ($display === '' && $instanceValue->value !== null && $instanceValue->value !== '') {
                $display = trim((string) $instanceValue->value);
            }

            if ($display !== '') {
                $values[$name] = $display;
            }
        }

        return $values;
    }

    /**
     * @param  array<string, mixed>  $receiptPayload
     * @return array<string, mixed>
     */
    private function resolveReceiptDate(
        AnalysisAcceptanceForm $form,
        array $receiptPayload,
        ?SampleSubmissionRequest $portalRequest,
        ?SubmissionFormInstance $instance,
        array $mappedHeader,
    ): string {
        $candidates = [
            $portalRequest?->received_by_date,
            $receiptPayload['sample_receiving_date'] ?? null,
            $mappedHeader['receipt_date'] ?? null,
            $instance?->reviewed_at,
            $portalRequest?->submission_date,
            $form->customer_signed_at,
        ];

        foreach ($candidates as $candidate) {
            $formatted = $this->formatDateOnly($candidate);
            if ($formatted !== null) {
                return $formatted;
            }
        }

        return now()->format('Y-m-d');
    }

    /**
     * @param  array<string, string>  $elementValues
     * @param  array<string, string>  $mappedHeader
     */
    private function resolveModeOfService(
        AnalysisAcceptanceForm $form,
        array $elementValues,
        array $mappedHeader,
    ): string {
        $fromElement = $this->firstNonEmptyString([
            $elementValues['mode_of_service'] ?? null,
            $mappedHeader['batch_scope'] ?? null,
        ]);

        if ($fromElement !== null) {
            return $fromElement;
        }

        $mode = trim((string) $form->mode_of_work);

        return $mode !== '' ? $mode : 'Normal';
    }

    private function sampleTypeNamesFromInstance(?SubmissionFormInstance $instance): ?string
    {
        if ($instance === null) {
            return null;
        }

        $names = $instance->getResolvedSampleTypeNames();

        return $names !== [] ? implode(', ', $names) : null;
    }

    /**
     * @param  list<mixed>  $candidates
     */
    private function firstNonEmptyString(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if ($candidate === null) {
                continue;
            }

            if ($candidate instanceof \DateTimeInterface) {
                return $candidate->format('Y-m-d H:i:s');
            }

            $text = trim((string) $candidate);
            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function normalizePriority(string $modeOfWork): string
    {
        return strtolower($modeOfWork) === 'express' ? 'Express' : 'Normal';
    }

    private function formatDateOnly(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
