<?php

namespace App\Services\Sampleworkflow;

use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\SampleAnalysisStage;
use App\SampleHeader;
use App\Services\SubmissionFormBatchSyncService;
use App\Models\System\SystemConfiguration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
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
        ?string $configLabIdFallback = null,
    ): array {
        $context = $this->resolveContext($form);

        $batchCode = $this->batchCodeService->resolveBatchCodeForAcceptanceForm($form, $primaryZoneId);

        $reviewStage = SampleAnalysisStage::query()
            ->where('sample_workflow', 'Samples Request Review')
            ->orderBy('level')
            ->first();

        $receiptDate = $this->resolveReceiptDate(
            $context['receiptPayload'],
            $context['portalRequest'],
            $context['instance'],
            $context['mappedHeader'],
        );

        $dateCollected = $this->resolveDateCollected($form, $context, $receiptDate);

        $crmContactId = $this->resolveCrmContactId($context);
        [$crmUnitId, $crmUnitName] = $this->resolveCrmUnit($form, $context, $crmContactId);

        $labId = $this->resolveLabId($context, $configLabIdFallback);
        $labSectionIds = $this->resolveLabSectionIds($labId);
        $isQcBatch = $this->resolveIsQcBatch($context['instance'], $context['mappedRaw'], $context['mappedHeader']);

        $receivingOfficer = $this->resolveReceivingOfficer($context);

        $attributes = [
            'batch_code' => $batchCode,
            'status' => 'Samples Request Review',
            'crm_customer_id' => $this->resolveCrmCustomerId($form, $context),
            'crm_contact_id' => $crmContactId,
            'crm_unit_id' => $crmUnitId,
            'crm_unit_name' => $crmUnitName,
            'schedule_customer_email' => $this->resolveCustomerEmail($context, $crmContactId),
            'sample_type_id' => $primarySampleTypeId !== '' ? $primarySampleTypeId : null,
            'zone_id' => $primaryZoneId,
            'processing_zone_id' => $primaryZoneId,
            'lab_id' => $labId,
            'lab_section_ids' => $labSectionIds,
            'receipt_date' => $receiptDate,
            'date_collected' => $dateCollected,
            'radio_active_levels' => $this->resolveTimeOfReceipt($context),
            'priority' => $this->normalizePriority((string) $form->mode_of_work),
            'batch_scope' => $this->resolveModeOfService($form, $context['elementValues'], $context['mappedHeader']),
            'submit_by' => $this->resolveSubmitBy($context),
            'payment_done_by' => trim((string) ($form->customer_signer_name ?? '')) !== ''
                ? (string) $form->customer_signer_name
                : null,
            'description' => $this->firstNonEmptyString([
                $context['receiptPayload']['sample_description'] ?? null,
                $context['elementValues']['nature_of_sample'] ?? null,
                $context['mappedHeader']['description'] ?? null,
                $context['portalRequest']?->description_of_samples,
                $this->sampleTypeNamesFromInstance($context['instance']),
            ]),
            'reason_for_submission' => $this->firstNonEmptyString([
                $context['mappedHeader']['reason_for_submission'] ?? null,
                $context['elementValues']['purpose'] ?? null,
            ]),
            'batch_instructions' => $this->firstNonEmptyString([
                $context['mappedHeader']['batch_instructions'] ?? null,
                $context['elementValues']['safety_precautions'] ?? null,
            ]),
            'reference_number' => $this->firstNonEmptyString([
                $context['mappedHeader']['reference_number'] ?? null,
                $batchCode,
            ]),
            'receiving_officer' => $receivingOfficer['id'],
            'receiving_officer_name' => $receivingOfficer['name'],
            'current_account_status' => $this->resolveCurrentAccountStatus($form->crm_customer_id),
            'is_routine' => false,
            'routine_frequency' => 0,
            'submission_form_instance_id' => $form->submission_form_instance_id,
            'sample_tracking_stage' => $reviewStage?->id,
            'is_qc_batch' => $isQcBatch,
            'begin_proccess' => $isQcBatch ? 1 : 0,
        ];

        if (Schema::hasColumn('sample_headers', 'is_client_order')) {
            $attributes['is_client_order'] = 1;
        }

        $sfi = $context['instance'];
        if ($sfi !== null) {
            $sfi->loadMissing('values.element');
            $formData = app(\App\Services\SubmissionForm\SubmissionFormValueNormalizer::class)
                ->valuesMapFromInstance($sfi);
            $mapper = app(TrfSampleFieldMapper::class);
            $trfMapped = $mapper->mapToSampleHeader($formData, [
                'crm_customer_id' => $attributes['crm_customer_id'] ?? null,
                'crm_contact_id' => $attributes['crm_contact_id'] ?? null,
                'crm_unit_id' => $attributes['crm_unit_id'] ?? null,
                'crm_unit_name' => $attributes['crm_unit_name'] ?? null,
                'email' => $context['portalRequest']?->email,
            ]);
            $attributes = $mapper->mergeFillGaps($attributes, $trfMapped);
        }

        return $attributes;
    }

    /**
     * Apply resolved acceptance / receipt attributes onto an existing batch header.
     */
    public function applyToBatch(SampleHeader $batch, AnalysisAcceptanceForm $form, ?string $primaryZoneId = null): void
    {
        $primarySampleTypeId = (string) ($batch->sample_type_id ?? '');
        if ($primarySampleTypeId === '' && $form->relationLoaded('lines')) {
            $primarySampleTypeId = (string) ($form->lines->where('is_approved', true)->first()?->sample_type_id ?? '');
        }

        $attributes = $this->buildCreateAttributes($form, $primarySampleTypeId, $primaryZoneId ?? $batch->zone_id);

        unset($attributes['batch_code'], $attributes['status'], $attributes['submission_form_instance_id']);

        foreach ($attributes as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $batch->{$key} = $value;
        }

        $batch->save();
    }

    /**
     * Sync mapped submission form fields onto linked batches after acceptance creation.
     */
    public function syncLinkedSubmissionForm(AnalysisAcceptanceForm $form): void
    {
        if ($form->submission_form_instance_id === null || $form->submission_form_instance_id === '') {
            return;
        }

        $instance = SubmissionFormInstance::query()->find($form->submission_form_instance_id);
        if ($instance === null) {
            return;
        }

        app(SubmissionFormBatchSyncService::class)->sync($instance);
    }

    /**
     * @return array{
     *     instance: ?SubmissionFormInstance,
     *     portalRequest: ?SampleSubmissionRequest,
     *     receiptPayload: array<string, mixed>,
     *     mappedHeader: array<string, string>,
     *     mappedRaw: array<string, string>,
     *     elementValues: array<string, string>
     * }
     */
    private function resolveContext(AnalysisAcceptanceForm $form): array
    {
        $instance = $form->submission_form_instance_id
            ? SubmissionFormInstance::query()
                ->with(['crmCustomer', 'submittedBy', 'reviewedBy', 'values.element'])
                ->find($form->submission_form_instance_id)
            : null;

        $portalRequest = $instance
            ? $this->receiptNotificationService->findLinkedSubmissionRequest($instance)
            : null;

        if ($portalRequest === null && $form->sample_submission_request_id) {
            $portalRequest = SampleSubmissionRequest::query()
                ->with(['customer', 'contact'])
                ->find((string) $form->sample_submission_request_id);
        }

        $receiptPayload = is_array($form->receipt_notification_payload)
            ? $form->receipt_notification_payload
            : [];

        return [
            'instance' => $instance,
            'portalRequest' => $portalRequest,
            'receiptPayload' => $receiptPayload,
            'mappedHeader' => $this->extractMappedHeaderFieldsFromInstance($instance),
            'mappedRaw' => $this->extractMappedRawFieldsFromInstance($instance),
            'elementValues' => $this->extractElementValuesByName($instance),
        ];
    }

    private function resolveCrmCustomerId(AnalysisAcceptanceForm $form, array $context): ?string
    {
        $id = $this->firstNonEmptyString([
            $form->crm_customer_id,
            $context['instance']?->crm_customer_id,
            $context['portalRequest']?->crm_customer_id,
        ]);

        return $id !== null && $id !== '' ? (string) $id : null;
    }

    private function resolveCrmContactId(array $context): ?string
    {
        $raw = $this->firstNonEmptyString([
            $context['portalRequest']?->crm_contact_id,
            $context['mappedRaw']['crm_contact_id'] ?? null,
            $context['instance']?->submittedBy?->crm_contact_id,
            $context['instance']?->submittedBy?->crmcontact_id,
        ]);

        return $raw !== null && $raw !== '' ? (string) $raw : null;
    }

    /**
     * @return array{0: ?string, 1: string}
     */
    private function resolveCrmUnit(
        AnalysisAcceptanceForm $form,
        array $context,
        ?string $crmContactId,
    ): array {
        if ($crmContactId !== null && $crmContactId !== '') {
            $contact = CustomerContact::query()->find($crmContactId);
            if ($contact !== null && $contact->crm_company_unit_id) {
                $unit = CRMCompanyUnit::query()->find($contact->crm_company_unit_id);

                return [(string) $contact->crm_company_unit_id, $unit?->name ?? 'N/A'];
            }
        }

        $mappedUnitId = $context['mappedRaw']['crm_unit_id'] ?? null;
        if ($mappedUnitId !== null && $mappedUnitId !== '') {
            $unit = CRMCompanyUnit::query()->find($mappedUnitId);

            return [(string) $mappedUnitId, $unit?->name ?? 'N/A'];
        }

        $customerId = (string) ($form->crm_customer_id ?? '');
        if ($customerId !== '') {
            $customer = CRMCustomer::query()->with('units')->find($customerId);
            if ($customer && $customer->units->count() === 1) {
                $unit = $customer->units->first();

                return [(string) $unit->id, $unit->name ?? 'N/A'];
            }
        }

        $instance = $context['instance'];
        if ($instance) {
            $unitId = $instance->values()
                ->whereHas('element', fn ($q) => $q->where('mapping_field', 'crm_unit_id'))
                ->value('value');

            if ($unitId) {
                $unit = CRMCompanyUnit::query()->find($unitId);

                return [(string) $unitId, $unit?->name ?? 'N/A'];
            }
        }

        return [null, 'N/A'];
    }

    private function resolveCustomerEmail(array $context, ?string $crmContactId): ?string
    {
        if ($crmContactId !== null && $crmContactId !== '') {
            $contact = CustomerContact::query()->find($crmContactId);
            $email = trim((string) ($contact?->email ?? ''));
            if ($email !== '') {
                return $email;
            }
        }

        return $this->firstNonEmptyString([
            $context['mappedHeader']['customer_email'] ?? null,
            $context['mappedRaw']['customer_email'] ?? null,
            $context['portalRequest']?->email,
        ]);
    }

    private function resolveLabId(array $context, ?string $configLabIdFallback): ?string
    {
        $labId = $this->firstNonEmptyString([
            $context['instance']?->receiving_lab_id,
            $context['instance']?->reviewedBy?->lab_id,
            $context['mappedRaw']['lab_id'] ?? null,
            $configLabIdFallback,
        ]);

        return $labId !== null && $labId !== '' ? (string) $labId : null;
    }

    private function resolveLabSectionIds(?string $labId): ?string
    {
        if ($labId === null || $labId === '') {
            return null;
        }

        $sectionIds = SampleAnalysisStage::query()
            ->where('lab_id', $labId)
            ->where('active', 1)
            ->where('is_system', 0)
            ->pluck('id')
            ->all();

        return $sectionIds !== [] ? implode(',', $sectionIds) : null;
    }

    /**
     * @return array{id: ?string, name: ?string}
     */
    private function resolveReceivingOfficer(array $context): array
    {
        $reviewedBy = $context['instance']?->reviewedBy;
        if ($reviewedBy !== null) {
            return [
                'id' => (string) $reviewedBy->id,
                'name' => (string) $reviewedBy->name,
            ];
        }

        return ['id' => null, 'name' => null];
    }

    private function resolveSubmitBy(array $context): ?string
    {
        return $this->firstNonEmptyString([
            $context['receiptPayload']['submitter_name'] ?? null,
            $context['mappedHeader']['submit_by'] ?? null,
            $context['elementValues']['submitting_personnel'] ?? null,
            $context['portalRequest']?->submitting_officer_full_name,
            $context['portalRequest']?->submitted_by_full_name,
            $context['instance']?->submittedBy?->name,
        ]);
    }

    private function resolveCurrentAccountStatus(?string $customerId): string
    {
        if ($customerId === null || $customerId === '') {
            return 'N/a';
        }

        $customer = function_exists('getCrmCustomerByID')
            ? getCrmCustomerByID($customerId)
            : CRMCustomer::query()->find($customerId);

        if ($customer === null || ! isset($customer->account_status)) {
            return 'N/a';
        }

        $account = SystemConfiguration::query()->find($customer->account_status);

        return isset($account->id) ? (string) $account->key : 'N/a';
    }

    private function resolveDateCollected(
        AnalysisAcceptanceForm $form,
        array $context,
        string $receiptDateFallback,
    ): string {
        $collected = $this->firstNonEmptyString([
            $form->date_of_sampling?->format('Y-m-d'),
            $context['mappedHeader']['date_collected'] ?? null,
            $context['elementValues']['date_of_sampling'] ?? null,
            $context['elementValues']['request_date_of_service'] ?? null,
            $form->request_date?->format('Y-m-d'),
            $context['portalRequest']?->date_of_seizure?->format('Y-m-d'),
            $context['portalRequest']?->submission_date?->format('Y-m-d'),
            $context['instance']?->submitted_at?->format('Y-m-d'),
        ]);

        if ($collected !== null) {
            return $this->formatDateOnly($collected) ?? $receiptDateFallback;
        }

        return $receiptDateFallback;
    }

    /**
     * @param  array<string, mixed>  $receiptPayload
     * @param  array<string, string>  $mappedHeader
     */
    private function resolveReceiptDate(
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
        ];

        foreach ($candidates as $candidate) {
            $formatted = $this->formatDateOnly($candidate);
            if ($formatted !== null) {
                return $formatted;
            }
        }

        return now()->format('Y-m-d');
    }

    private function resolveTimeOfReceipt(array $context): ?string
    {
        $time = $this->firstNonEmptyString([
            $context['portalRequest']?->received_by_time,
            $context['mappedRaw']['radio_active_levels'] ?? null,
        ]);

        if ($time === null) {
            return null;
        }

        try {
            return Carbon::parse($time)->format('H:i');
        } catch (\Throwable) {
            return $time;
        }
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
     * Raw stored values for mapped sample_headers fields (UUIDs / codes).
     *
     * @return array<string, string>
     */
    private function extractMappedRawFieldsFromInstance(?SubmissionFormInstance $instance): array
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
            if ($field === '' || $instanceValue->value === null || $instanceValue->value === '') {
                continue;
            }

            $fields[$field] = trim((string) $instanceValue->value);
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

    /**
     * @param  array<string, string>  $mappedRaw
     * @param  array<string, string>  $mappedHeader
     */
    private function resolveIsQcBatch(?SubmissionFormInstance $instance, array $mappedRaw, array $mappedHeader): bool
    {
        if ($instance !== null && isset($instance->is_qc_batch)) {
            return (bool) $instance->is_qc_batch;
        }

        foreach ([$mappedRaw['is_qc_batch'] ?? null, $mappedHeader['is_qc_batch'] ?? null] as $candidate) {
            $resolved = $this->toBoolean($candidate);
            if ($resolved !== null) {
                return $resolved;
            }
        }

        return false;
    }

    private function toBoolean(mixed $value): ?bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if ($value === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off'], true)) {
            return false;
        }

        return null;
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

    /**
     * @param  array{
     *     instance: ?SubmissionFormInstance,
     *     portalRequest: ?SampleSubmissionRequest,
     * }  $context
     */
}
