<?php

namespace App\Services\Sampleworkflow;

use App\Models\Equipments\Equipment;
use App\Models\CRM\SamplePoint;
use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\ContractCustomerService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use App\User;

final class SampleReceivingCheckInService
{
    public function __construct(
        private CommercialEnquiryFromFormService $commercialEnquiryService,
        private EnquiryReceptionReadinessService $receptionReadinessService,
        private ContractCustomerService $contractCustomerService,
        private SubmissionFormValueNormalizer $valueNormalizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function buildCheckInContext(SubmissionFormInstance $instance): array
    {
        $instance->loadMissing([
            'crmCustomer',
            'submissionForm',
            'sampleSubmissionRequest.acceptedQuotation',
            'sampleSubmissionRequest.currentQuotation',
            'values.element',
        ]);

        $enquiry = $instance->sampleSubmissionRequest;
        $formData = $this->valueNormalizer->valuesMapFromInstance($instance);
        $values = $this->indexedFormValues($instance);

        $collectionData = is_array($enquiry?->collection_data) ? $enquiry->collection_data : [];

        $thermometerId = $formData['thermometer_id'] ?? $values['thermometer_id'] ?? $collectionData['thermometer_id'] ?? null;
        $thermometerLabel = $this->resolveThermometerLabel($thermometerId);

        $samplingApparatus = $formData['sampling_apparatus'] ?? $values['sampling_apparatus'] ?? $collectionData['sampling_apparatus'] ?? null;
        if (is_array($samplingApparatus)) {
            $samplingApparatus = implode(', ', array_filter($samplingApparatus));
        }

        $acceptedQuotation = $enquiry !== null
            ? $this->receptionReadinessService->resolveAcceptedQuotation($enquiry)
            : null;

        $sampleDescriptionRaw = (
            $enquiry?->sample_description
            ?? $enquiry?->description_of_samples
            ?? $values['sample_description']
            ?? $values['description_of_samples']
            ?? ''
        );

        return [
            'instance_id' => $instance->id,
            'form_number' => (string) $instance->canonicalFormNumber(),
            'customer_name' => (string) ($instance->crmCustomer?->name ?? ''),
            'sample_description' => $this->formatRichTextPlain($sampleDescriptionRaw),
            'sample_description_html' => $this->formatRichTextHtml($sampleDescriptionRaw),
            'number_of_samples' => (int) (
                $enquiry?->number_of_samples
                ?? $values['number_of_samples']
                ?? 1
            ),
            'sampling_date' => (string) (
                $formData['sampling_date']
                ?? $values['sampling_date']
                ?? $collectionData['sampling_date']
                ?? ''
            ),
            'sampling_location' => $this->resolveSamplingLocationLabel(
                $formData['sampling_location']
                ?? $values['sampling_location']
                ?? $collectionData['sampling_location']
                ?? null
            ),
            'sampling_apparatus' => (string) $samplingApparatus,
            'thermometer_id' => $thermometerLabel,
            'source_channel' => (string) ($enquiry?->source_channel ?? ''),
            'enquiry_status' => (string) ($enquiry?->status ?? ''),
            'enquiry_reference' => $enquiry !== null
                ? (string) ($enquiry->unique_identification ?? $enquiry->getFormattedNumberAttribute())
                : '',
            'quotation_number' => (string) ($acceptedQuotation?->quote_number ?? ''),
            'client_po_number' => (string) ($enquiry?->client_po_number ?? ''),
            'advance_payment_reference' => (string) ($enquiry?->advance_payment_reference ?? ''),
            'can_receive' => $this->canReceiveInstance($instance, $enquiry),
            'receive_block_reason' => $this->receiveBlockReason($instance, $enquiry),
            'is_commercial_trf' => $this->commercialEnquiryService->isCommercialTestRequestForm($instance),
        ];
    }

    /**
     * @param  list<string>  $instanceIds
     * @return list<array<string, mixed>>
     */
    public function buildCheckInContexts(array $instanceIds): array
    {
        if ($instanceIds === []) {
            return [];
        }

        return SubmissionFormInstance::query()
            ->with([
                'crmCustomer',
                'submissionForm',
                'sampleSubmissionRequest',
                'values.element',
            ])
            ->whereIn('id', $instanceIds)
            ->get()
            ->map(fn (SubmissionFormInstance $instance) => $this->buildCheckInContext($instance))
            ->values()
            ->all();
    }

    public function receiveInstance(SubmissionFormInstance $instance, User $user, ?string $notes = null): bool
    {
        if (! $this->canReceiveInstance($instance)) {
            return false;
        }

        $instance->markAsReceived($user, $notes);

        return true;
    }

    public function canReceiveInstance(SubmissionFormInstance $instance, ?SampleSubmissionRequest $enquiry = null): bool
    {
        if ($instance->status !== 'submitted') {
            return false;
        }

        if ($instance->batches()->exists()) {
            return false;
        }

        if ($instance->submissionForm === null || ($instance->submissionForm->form_type ?? '') !== 'template') {
            return false;
        }

        if (! $this->commercialEnquiryService->isCommercialTestRequestForm($instance)) {
            return true;
        }

        $enquiry ??= $instance->sampleSubmissionRequest;

        if ($enquiry === null) {
            return false;
        }

        return $this->receptionReadinessService->isEligibleForPhysicalReceive($enquiry);
    }

    public function receiveBlockReason(SubmissionFormInstance $instance, ?SampleSubmissionRequest $enquiry = null): ?string
    {
        if ($this->canReceiveInstance($instance, $enquiry)) {
            return null;
        }

        $enquiry ??= $instance->sampleSubmissionRequest;

        if ($enquiry !== null
            && $this->contractCustomerService->isScheduledEnquiry($enquiry)
            && in_array((string) $instance->status, ['received', 'Received'], true)) {
            return null;
        }

        if ($instance->status !== 'submitted') {
            return 'Request is not in submitted status.';
        }

        if ($instance->batches()->exists()) {
            return 'A batch has already been created for this request.';
        }

        if (! $this->commercialEnquiryService->isCommercialTestRequestForm($instance)) {
            return 'Request is not eligible for receiving.';
        }

        $enquiry ??= $instance->sampleSubmissionRequest;

        if ($enquiry === null) {
            return 'No commercial enquiry is linked to this test request.';
        }

        return match ((string) $enquiry->status) {
            SampleSubmissionRequest::STATUS_REQUESTED,
            SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS => 'Quotation has not been sent to the customer yet.',
            SampleSubmissionRequest::STATUS_QUOTATION_SENT => 'Waiting for the client to accept the quotation.',
            SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW => 'Quotation is under review with the client.',
            SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED => 'Record the customer PO before physical samples can be received.',
            default => 'Quotation must be accepted and PO recorded before physical samples can be received.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function indexedFormValues(SubmissionFormInstance $instance): array
    {
        $indexed = [];

        foreach ($instance->values as $row) {
            $element = $row->element;
            if ($element === null) {
                continue;
            }

            $keys = array_filter([
                $element->mapping_field,
                $element->name,
            ]);

            foreach ($keys as $key) {
                $indexed[(string) $key] = $row->value;
            }
        }

        return $indexed;
    }

    private function resolveThermometerLabel(mixed $thermometerId): string
    {
        if ($thermometerId === null || $thermometerId === '') {
            return '';
        }

        if (is_string($thermometerId) && ! preg_match('/^[0-9a-f-]{36}$/i', $thermometerId)) {
            return (string) $thermometerId;
        }

        $equipment = Equipment::query()->find($thermometerId);

        if ($equipment === null) {
            return (string) $thermometerId;
        }

        $number = trim((string) ($equipment->equipment_number ?? ''));

        return $number !== '' ? $number : (string) ($equipment->name ?? $thermometerId);
    }

    private function formatRichTextPlain(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return '';
        }

        if ($this->containsHtml($string)) {
            $plain = html_entity_decode(strip_tags($string), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $plain = trim(preg_replace('/\s+/u', ' ', $plain) ?? '');

            return $plain;
        }

        return $string;
    }

    private function formatRichTextHtml(mixed $value): string
    {
        $string = trim((string) ($value ?? ''));

        if ($string === '') {
            return '';
        }

        if ($this->containsHtml($string)) {
            $clean = strip_tags($string, '<p><br><strong><b><em><i><u><ul><ol><li><span><div>');
            $clean = trim($clean);

            return $clean;
        }

        return nl2br(e($string), false);
    }

    private function containsHtml(string $value): bool
    {
        return $value !== strip_tags($value);
    }

    private function resolveSamplingLocationLabel(mixed $value): string
    {
        $reference = trim((string) ($value ?? ''));
        if ($reference === '') {
            return '';
        }

        if (preg_match('/^[0-9a-f-]{36}$/i', $reference)) {
            $name = SamplePoint::query()->where('id', $reference)->value('name');

            return $name !== null && $name !== '' ? (string) $name : $reference;
        }

        return $reference;
    }

}
