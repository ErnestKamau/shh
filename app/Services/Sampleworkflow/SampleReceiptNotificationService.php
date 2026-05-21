<?php

namespace App\Services\Sampleworkflow;

use App\BatchAttachment;
use App\Models\SampleSubmissionRequest;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use App\Services\SubmissionForm\SubmissionRequestSampleLineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SampleReceiptNotificationService
{
    public const ATTACHMENT_TITLE = 'Sample Receipt Notification (GCLA 01)';

    public const LAB_NUMBER_PENDING_SUFFIX = ' — Lab. no. assigned after customer signs';

    /**
     * @return array<string, mixed>
     */
    public static function emptyForm(): array
    {
        return [
            'client_or_authority_name' => '',
            'sample_description' => '',
            'submitter_name' => '',
            'submitter_designation' => '',
            'submitter_signature' => '',
            'laboratory_identification_number' => '',
            'number_of_samples' => 0,
            'receiver_name' => '',
            'receiver_designation' => '',
            'receiver_signature' => '',
            'sample_receiving_date' => '',
        ];
    }

    public function pendingDraftRelativePath(?string $submissionFormInstanceId, ?string $submissionRequestId): ?string
    {
        if ($submissionFormInstanceId !== null && $submissionFormInstanceId !== '') {
            return 'batch-attachments/sample-receipt-notification-pending-instance-' . $submissionFormInstanceId . '.json';
        }

        if ($submissionRequestId !== null && $submissionRequestId !== '') {
            return 'batch-attachments/sample-receipt-notification-pending-request-' . $submissionRequestId . '.json';
        }

        return null;
    }

    public function batchDraftRelativePath(string $batchId): string
    {
        return 'batch-attachments/sample-receipt-notification-batch-' . $batchId . '.json';
    }

    /**
     * @param  array<string, mixed>  $form
     */
    public function savePendingDraft(?string $submissionFormInstanceId, ?string $submissionRequestId, array $form): void
    {
        $path = $this->pendingDraftRelativePath($submissionFormInstanceId, $submissionRequestId);
        if ($path === null) {
            return;
        }

        Storage::disk('public')->put($path, json_encode(['form' => $form], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>
     */
    public function loadPendingDraft(?string $submissionFormInstanceId, ?string $submissionRequestId): array
    {
        $merged = self::emptyForm();

        $paths = [];
        if ($submissionFormInstanceId !== null && $submissionFormInstanceId !== '') {
            $paths[] = 'batch-attachments/sample-receipt-notification-pending-instance-' . $submissionFormInstanceId . '.json';
        }
        if ($submissionRequestId !== null && $submissionRequestId !== '') {
            $paths[] = 'batch-attachments/sample-receipt-notification-pending-request-' . $submissionRequestId . '.json';
        }

        foreach ($paths as $path) {
            if (! Storage::disk('public')->exists($path)) {
                continue;
            }
            $decoded = json_decode((string) Storage::disk('public')->get($path), true);
            if (! is_array($decoded)) {
                continue;
            }
            $saved = $decoded['form'] ?? null;
            if (is_array($saved)) {
                $merged = $this->mergeFormPayloads($merged, $saved);
            }
        }

        return $merged;
    }

    public function deletePendingDraft(?string $submissionFormInstanceId, ?string $submissionRequestId): void
    {
        $paths = [];
        if ($submissionFormInstanceId !== null && $submissionFormInstanceId !== '') {
            $paths[] = 'batch-attachments/sample-receipt-notification-pending-instance-' . $submissionFormInstanceId . '.json';
        }
        if ($submissionRequestId !== null && $submissionRequestId !== '') {
            $paths[] = 'batch-attachments/sample-receipt-notification-pending-request-' . $submissionRequestId . '.json';
        }
        foreach ($paths as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function persistAfterAcceptanceCreated(
        AnalysisAcceptanceForm $form,
        array $wizardReceiptFields,
        ?string $instanceId,
        ?string $requestId
    ): void {
        $pending = $this->loadPendingDraft($instanceId, $requestId);
        $merged = $this->mergeFormPayloads($pending, $wizardReceiptFields);
        $merged = $this->mergeFormPayloads($merged, $this->hydrateDefaultsFromAcceptanceForm($form));
        $form->update(['receipt_notification_payload' => $merged]);
        $this->deletePendingDraft($instanceId, $requestId);
    }

    /**
     * @param  array<string, mixed>  $patch
     */
    public function mergePayloadIntoAcceptanceForm(AnalysisAcceptanceForm $form, array $patch): void
    {
        $existing = is_array($form->receipt_notification_payload) ? $form->receipt_notification_payload : [];
        $merged = $this->mergeFormPayloads($existing, $patch);
        $form->update(['receipt_notification_payload' => $merged]);
    }

    /**
     * @return array<string, mixed>
     */
    public function hydrateDefaultsFromAcceptanceForm(AnalysisAcceptanceForm $form): array
    {
        $defaults = self::emptyForm();
        $defaults['client_or_authority_name'] = (string) ($form->customer_name ?? '');
        $defaults['number_of_samples'] = (int) ($form->number_of_samples ?? 0);

        return $defaults;
    }

    /**
     * Prefill GCLA 01 from submission/receiving data before the acceptance form is created.
     *
     * @param  array<string, mixed>  $wizardContext
     * @return array<string, mixed>
     */
    public function hydrateDefaultsFromWizardSelection(
        ?string $submissionFormInstanceId,
        ?string $submissionRequestId,
        array $wizardContext = []
    ): array {
        $defaults = self::emptyForm();
        $requestReference = '';
        $instance = null;
        $portalRequest = null;

        if ($submissionRequestId !== null && $submissionRequestId !== '') {
            $portalRequest = SampleSubmissionRequest::query()
                ->with(['customer', 'batch'])
                ->find($submissionRequestId);
        }

        if ($submissionFormInstanceId !== null && $submissionFormInstanceId !== '') {
            $instance = SubmissionFormInstance::query()
                ->with(['crmCustomer', 'submittedBy', 'batches', 'submissionForm', 'values.element'])
                ->find($submissionFormInstanceId);

            if ($portalRequest === null && $instance !== null) {
                $portalRequest = $this->findLinkedSubmissionRequest($instance);
            }
        }

        if ($portalRequest !== null) {
            $requestReference = (string) $portalRequest->formatted_number;
            $defaults['client_or_authority_name'] = (string) ($portalRequest->customer?->name ?? '');
            $defaults['sample_description'] = (string) ($portalRequest->description_of_samples ?? '');
            $defaults['submitter_name'] = (string) (
                $portalRequest->submitting_officer_full_name
                ?: $portalRequest->submitted_by_full_name
                ?: ''
            );
            $defaults['submitter_designation'] = (string) (
                $portalRequest->submitting_officer_title
                ?: $portalRequest->submitted_by_title
                ?: ''
            );
            $defaults['submitter_signature'] = (string) ($portalRequest->submitted_by_signature ?? '');
            $defaults['number_of_samples'] = (int) ($portalRequest->number_of_samples ?? 0);
            $defaults['sample_receiving_date'] = optional($portalRequest->received_by_date)->format('Y-m-d')
                ?? optional($portalRequest->submission_date)->format('Y-m-d')
                ?? '';
        }

        if ($instance !== null) {
            $requestReference = $this->resolveRequestReference($instance, $portalRequest, $requestReference);

            $defaults['client_or_authority_name'] = $this->resolveClientNameForInstance($instance)
                ?: $defaults['client_or_authority_name'];

            $sampleTypeNames = $instance->getResolvedSampleTypeNames();
            if ($defaults['sample_description'] === '' && $sampleTypeNames !== []) {
                $defaults['sample_description'] = implode(', ', $sampleTypeNames);
            }

            $submitterFromForm = $this->pickInstancePersonnelValue($instance, [
                'submitting_officer',
                'submitted_by_full_name',
                'submitted by',
                'submitting officer',
                'submitter',
                'person submitting',
            ]);

            if ($submitterFromForm !== '') {
                $defaults['submitter_name'] = $submitterFromForm;
            } elseif ($defaults['submitter_name'] === '') {
                $defaults['submitter_name'] = (string) ($instance->submittedBy?->name ?? '');
            }

            $designationFromForm = $this->pickInstancePersonnelValue($instance, [
                'submitting_officer_title',
                'submitted_by_title',
                'designation',
                'title',
            ]);

            if ($designationFromForm !== '') {
                $defaults['submitter_designation'] = $designationFromForm;
            }

            $sampleLineSeeds = app(SubmissionRequestSampleLineService::class)->seedsForAcceptancePrefill($instance);
            $sampleDetailCount = count($instance->getAllSampleDetails());
            $defaults['number_of_samples'] = max(
                $defaults['number_of_samples'],
                count($sampleLineSeeds),
                $sampleDetailCount,
                1
            );

            if ($defaults['sample_receiving_date'] === '' && $instance->reviewed_at !== null) {
                $defaults['sample_receiving_date'] = $instance->reviewed_at->format('Y-m-d');
            }
        }

        $defaults['laboratory_identification_number'] = $this->formatPendingLaboratoryIdentification($requestReference);

        if ($defaults['sample_receiving_date'] === '') {
            $defaults['sample_receiving_date'] = now()->format('Y-m-d');
        }

        $merged = $this->mergeFormPayloads($defaults, $wizardContext);

        if (isset($wizardContext['number_of_samples']) && (int) $wizardContext['number_of_samples'] > 0) {
            $merged['number_of_samples'] = (int) $wizardContext['number_of_samples'];
        }

        if (isset($wizardContext['client_or_authority_name'])
            && trim((string) $wizardContext['client_or_authority_name']) !== '') {
            $merged['client_or_authority_name'] = (string) $wizardContext['client_or_authority_name'];
        }

        if ($requestReference !== '' && !str_contains((string) $merged['laboratory_identification_number'], $requestReference)) {
            $merged['laboratory_identification_number'] = $this->formatPendingLaboratoryIdentification($requestReference);
        }

        return $this->applyReceivingPersonFromAuth($merged);
    }

    /**
     * @return array<string, mixed>
     */
    public function applyReceivingPersonFromAuth(array $form): array
    {
        $user = Auth::user();
        if ($user === null) {
            return $form;
        }

        $user->loadMissing('roles');

        $form['receiver_name'] = (string) $user->name;
        $form['receiver_designation'] = (string) ($user->roles->first()?->name ?? '');

        return $form;
    }

    private function resolveClientNameForInstance(SubmissionFormInstance $instance): string
    {
        $fromCrm = trim((string) ($instance->crmCustomer?->name ?? ''));
        if ($fromCrm !== '') {
            return $fromCrm;
        }

        return $this->pickInstancePersonnelValue($instance, [
            'customer_name',
            'submitting_agency',
            'name_of_client',
            'name of client',
            'client',
        ]);
    }

    private function resolveRequestReference(
        SubmissionFormInstance $instance,
        ?SampleSubmissionRequest $portalRequest,
        string $existingReference
    ): string {
        if ($existingReference !== '') {
            return $existingReference;
        }

        $documentNumber = trim((string) ($instance->getDocumentControlNumber() ?: $instance->form_number ?: ''));
        if ($documentNumber !== '') {
            return $documentNumber;
        }

        if ($portalRequest !== null) {
            return (string) $portalRequest->formatted_number;
        }

        $linked = $this->findLinkedSubmissionRequest($instance);
        if ($linked !== null) {
            return (string) $linked->formatted_number;
        }

        return trim((string) ($instance->title ?? ''));
    }

    private function findLinkedSubmissionRequest(SubmissionFormInstance $instance): ?SampleSubmissionRequest
    {
        $candidateIds = collect([
            $instance->portal_request_id,
            $instance->target_record_id,
        ])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();

        if ($candidateIds === []) {
            return null;
        }

        return SampleSubmissionRequest::query()
            ->whereIn('id', $candidateIds)
            ->first();
    }

    public function formatPendingLaboratoryIdentification(string $requestReference): string
    {
        $requestReference = trim($requestReference);

        if ($requestReference === '') {
            return 'Lab. no. to be assigned after customer signs';
        }

        return $requestReference . self::LAB_NUMBER_PENDING_SUFFIX;
    }

    /**
     * @param  list<string>  $hints
     */
    private function pickInstancePersonnelValue(SubmissionFormInstance $instance, array $hints): string
    {
        foreach ($instance->values as $value) {
            $element = $value->element;
            if ($element === null) {
                continue;
            }

            $haystack = strtolower(implode(' ', array_filter([
                (string) ($element->name ?? ''),
                (string) ($element->label ?? ''),
                (string) ($element->mapping_field ?? ''),
            ])));

            foreach ($hints as $hint) {
                if ($hint === '' || !str_contains($haystack, strtolower($hint))) {
                    continue;
                }

                $display = trim((string) $instance->resolveDisplayValue($element, $value->value));
                if ($display !== '') {
                    return $display;
                }
            }
        }

        return '';
    }

    public function findAcceptanceFormForBatch(SampleHeader $batch): ?AnalysisAcceptanceForm
    {
        $byHeader = AnalysisAcceptanceForm::query()
            ->where('sample_header_id', $batch->id)
            ->orderByDesc('updated_at')
            ->first();

        if ($byHeader) {
            return $byHeader;
        }

        if ($batch->submission_form_instance_id) {
            return AnalysisAcceptanceForm::query()
                ->where('submission_form_instance_id', $batch->submission_form_instance_id)
                ->orderByDesc('updated_at')
                ->first();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveFormStateForBatch(SampleHeader $batch): array
    {
        $acceptanceForm = $this->findAcceptanceFormForBatch($batch);
        $fromColumn = is_array($acceptanceForm?->receipt_notification_payload) ? $acceptanceForm->receipt_notification_payload : [];
        $legacyPath = $this->batchDraftRelativePath($batch->id);
        $fromFile = self::emptyForm();

        if (Storage::disk('public')->exists($legacyPath)) {
            $decoded = json_decode((string) Storage::disk('public')->get($legacyPath), true);
            if (is_array($decoded) && is_array($decoded['form'] ?? null)) {
                $fromFile = array_merge(self::emptyForm(), $decoded['form']);
            }
        }

        $merged = $this->mergeFormPayloads($this->hydrateDefaultsFromBatch($batch), $fromFile);
        $merged = $this->mergeFormPayloads($merged, $fromColumn);

        if ($acceptanceForm) {
            $merged = $this->mergeFormPayloads($merged, $this->hydrateDefaultsFromAcceptanceForm($acceptanceForm));
        }

        return $merged;
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveFormStateForAcceptanceForm(AnalysisAcceptanceForm $form): array
    {
        $fromColumn = is_array($form->receipt_notification_payload) ? $form->receipt_notification_payload : [];
        $pending = $this->loadPendingDraft(
            (string) ($form->submission_form_instance_id ?? ''),
            (string) ($form->sample_submission_request_id ?? '')
        );

        $merged = $this->mergeFormPayloads(self::emptyForm(), $pending);
        $merged = $this->mergeFormPayloads($merged, $this->hydrateDefaultsFromAcceptanceForm($form));
        $merged = $this->mergeFormPayloads($merged, $fromColumn);

        if ($form->sample_header_id) {
            $batch = SampleHeader::query()->find((string) $form->sample_header_id);
            if ($batch) {
                $merged = $this->mergeFormPayloads($merged, $this->hydrateDefaultsFromBatch($batch));
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $patch
     */
    public function applyBatchDefaultsAfterCustomerSign(AnalysisAcceptanceForm $form): void
    {
        $form->refresh();
        if (! $form->sample_header_id) {
            return;
        }

        $batch = SampleHeader::query()->find((string) $form->sample_header_id);
        if (! $batch) {
            return;
        }

        $existing = is_array($form->receipt_notification_payload) ? $form->receipt_notification_payload : [];
        $merged = $this->mergeFormPayloads($existing, $this->hydrateDefaultsFromBatch($batch));
        $form->update(['receipt_notification_payload' => $merged]);
    }

    /**
     * @param  array<string, mixed>  $form
     */
    public function saveBatchDraftFile(SampleHeader $batch, array $form): void
    {
        Storage::disk('public')->put(
            $this->batchDraftRelativePath($batch->id),
            json_encode(['form' => $form], JSON_PRETTY_PRINT)
        );
    }

    /**
     * @param  array<string, mixed>  $form
     */
    public function persistForBatchLinkedAcceptance(?AnalysisAcceptanceForm $acceptanceForm, SampleHeader $batch, array $form): void
    {
        $this->saveBatchDraftFile($batch, $form);

        if ($acceptanceForm !== null) {
            $acceptanceForm->update(['receipt_notification_payload' => $form]);
        }
    }

    /**
     * @param  array<string, mixed>  $formData
     * @param  callable(string, array): void|null  $onFailure  ($message, $errors)
     * @return string|null Public URL when PDF created
     */
    public function tryFinalizePdfAttachment(SampleHeader $batch, array $formData, ?string $userId, ?callable $onFailure = null): ?string
    {
        $validator = Validator::make(
            ['form' => $formData],
            $this->fullValidationRules(),
            $this->validationMessages()
        );

        if ($validator->fails()) {
            if ($onFailure) {
                $onFailure('Validation failed.', $validator->errors()->toArray());
            }

            return null;
        }

        return $this->generatePdfAndStoreAttachment($batch, $formData, $userId);
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public function generatePdfAndStoreAttachment(SampleHeader $batch, array $formData, ?string $userId): string
    {
        $this->saveBatchDraftFile($batch, $formData);

        $pdfFilename = 'sample-receipt-notification-batch-' . $batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('batch.attachments.sample-receipt-notification-pdf', [
            'batch' => $batch,
            'form' => $formData,
        ]);

        Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        $attachmentTypeId = $this->resolveAttachmentTypeId();
        $title = self::ATTACHMENT_TITLE;

        $attachment = BatchAttachment::where('batch_id', $batch->id)
            ->where('title', $title)
            ->orderByDesc('created_at')
            ->first();

        if (! $attachment) {
            $attachment = new BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = $userId ?? Auth::id();
            $attachment->title = $title;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
        }

        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $pdfPublicUrl;
        $attachment->save();

        return $pdfPublicUrl;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function fullValidationRules(): array
    {
        return [
            'form.client_or_authority_name' => 'required|string|max:255',
            'form.sample_description' => 'required|string',
            'form.submitter_name' => 'required|string|max:255',
            'form.submitter_designation' => 'required|string|max:255',
            'form.submitter_signature' => 'required|string',
            'form.laboratory_identification_number' => 'required|string|max:255',
            'form.number_of_samples' => 'required|integer|min:0',
            'form.receiver_name' => 'required|string|max:255',
            'form.receiver_designation' => 'required|string|max:255',
            'form.receiver_signature' => 'required|string',
            'form.sample_receiving_date' => 'required|date',
        ];
    }

    /**
     * Portal / customer partial: lab-only receiver fields optional.
     *
     * @return array<string, array<int, string>>
     */
    public function portalPartialValidationRules(): array
    {
        return [
            'receipt_notification' => ['nullable', 'array'],
            'receipt_notification.client_or_authority_name' => ['nullable', 'string', 'max:255'],
            'receipt_notification.sample_description' => ['nullable', 'string'],
            'receipt_notification.submitter_name' => ['nullable', 'string', 'max:255'],
            'receipt_notification.submitter_designation' => ['nullable', 'string', 'max:255'],
            'receipt_notification.submitter_signature' => ['nullable', 'string'],
            'receipt_notification.laboratory_identification_number' => ['nullable', 'string', 'max:255'],
            'receipt_notification.number_of_samples' => ['nullable', 'integer', 'min:0'],
            'receipt_notification.receiver_name' => ['nullable', 'string', 'max:255'],
            'receipt_notification.receiver_designation' => ['nullable', 'string', 'max:255'],
            'receipt_notification.receiver_signature' => ['nullable', 'string'],
            'receipt_notification.sample_receiving_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'form.submitter_signature.required' => 'Submitting person signature is required.',
            'form.receiver_signature.required' => 'Receiving person signature is required.',
        ];
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     * @return array<string, mixed>
     */
    public function mergeFormPayloads(array $a, array $b): array
    {
        $merged = array_merge(self::emptyForm(), $a, $b);
        foreach ($merged as $key => $value) {
            if ($value === null) {
                $merged[$key] = '';
            }
        }

        return $merged;
    }

    /**
     * Apply a saved draft on top of prefill without blank draft values wiping populated fields.
     *
     * @param  array<string, mixed>  $prefill
     * @param  array<string, mixed>  $draft
     * @return array<string, mixed>
     */
    public function mergePrefillWithUserDraft(array $prefill, array $draft): array
    {
        $merged = $this->mergeFormPayloads([], $prefill);

        foreach ($draft as $key => $value) {
            if (!array_key_exists($key, $merged)) {
                continue;
            }

            if ($this->isMeaningfulReceiptFieldValue($key, $value)) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    private function isMeaningfulReceiptFieldValue(string $key, mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if ($key === 'number_of_samples') {
            return is_numeric($value) && (int) $value > 0;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_int($value) || is_float($value)) {
            return true;
        }

        return !empty($value);
    }

    /**
     * @param  array<string, mixed>|null  $incoming
     * @return array<string, mixed>
     */
    public function sanitizePortalPatch(?array $incoming): array
    {
        if (! is_array($incoming)) {
            return [];
        }

        $patch = [];
        foreach (array_keys(self::emptyForm()) as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }
            $value = $incoming[$key];
            if ($value === null) {
                continue;
            }
            if (is_string($value) && trim($value) === '') {
                continue;
            }
            $patch[$key] = $value;
        }

        return $patch;
    }

    /**
     * @return array<string, mixed>
     */
    private function hydrateDefaultsFromBatch(SampleHeader $batch): array
    {
        $defaults = self::emptyForm();
        $defaults['laboratory_identification_number'] = (string) ($batch->batch_code ?? '');
        $defaults['number_of_samples'] = (int) $batch->samples()->count();
        $defaults['sample_receiving_date'] = (string) ($batch->receipt_date ?? now()->format('Y-m-d'));

        $user = Auth::user();
        if ($user !== null) {
            $defaults['receiver_name'] = (string) $user->name;
            $defaults['receiver_designation'] = (string) ($user->roles->first()?->name ?? '');
        }

        return $defaults;
    }

    private function resolveAttachmentTypeId(): ?int
    {
        $label = 'Sample Receipt Notification Form';

        $existingId = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', $label)
            ->value('id');

        if ($existingId !== null) {
            return (int) $existingId;
        }

        $typeConfig = SystemConfiguration::query()->where('key', 'attachment_type_config_id')->first();
        if (! $typeConfig) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $typeConfig->id;
        $newConfig->save();

        return (int) $newConfig->id;
    }
}
