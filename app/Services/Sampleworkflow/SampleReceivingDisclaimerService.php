<?php

namespace App\Services\Sampleworkflow;

use App\BatchAttachment;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\SampleHeader;
use App\Services\System\AttachmentTypeResolver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SampleReceivingDisclaimerService
{
    public const ATTACHMENT_TITLE = 'Sample Receiving Disclaimer Form';

    public const INTEGRITY_STATEMENT_PREFIX = 'The above sample has not met the criteria of sample integrity for the test(s) requested. Analysis of such sample will yield unreliable results. I/We,';

    public const INTEGRITY_STATEMENT_SUFFIX = '(DISCLAIMANT), in accordance with the Government Chemist Laboratory Authority Act No 8 of 2016 Section 16 Subsection 1 and its regulation, I/We agree to accept and receive the Laboratory Analytical Report (Certificate of analysis) after analysis performed by Government Chemist Laboratory Authority.';

    public const COA_FOOTER_NOTE = 'Note: Copy of sample receiving disclaimer form will be attached to Certificate of analysis report after writing report';

    /**
     * @return array<string, mixed>
     */
    public static function emptyForm(): array
    {
        return [
            'client_name' => '',
            'lab_no' => '',
            'date' => '',
            'time' => '',
            'sample_types' => '',
            'number_of_samples' => 0,
            'disclaimant_name' => '',
            'claimant_name' => '',
            'claimant_signature' => '',
            'claimant_signed_at' => '',
            'claimant_signature_source' => '',
            'analyst_name' => '',
            'analyst_signature' => '',
            'analyst_signed_at' => '',
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
                $merged[$key] = $key === 'number_of_samples' ? 0 : '';
            }
        }

        return $merged;
    }

    /**
     * @param  array<string, mixed>  $prefill
     * @return array<string, mixed>
     */
    public function hydrateDefaultsForWizard(array $prefill): array
    {
        $now = now();

        return $this->mergeFormPayloads(self::emptyForm(), [
            'client_name' => (string) ($prefill['customer_name'] ?? ''),
            'lab_no' => (string) ($prefill['lab_no'] ?? ''),
            'date' => $now->format('Y-m-d'),
            'time' => $now->format('H:i'),
            'sample_types' => (string) ($prefill['type_of_sample'] ?? ''),
            'number_of_samples' => (int) ($prefill['number_of_samples'] ?? 1),
            'disclaimant_name' => (string) ($prefill['customer_name'] ?? ''),
            'claimant_name' => (string) ($prefill['customer_name'] ?? ''),
            'analyst_name' => (string) (Auth::user()->name ?? ''),
            'analyst_signed_at' => $now->format('Y-m-d'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function hydrateDefaultsFromAcceptanceForm(AnalysisAcceptanceForm $form): array
    {
        $existing = is_array($form->sample_disclaimer_payload) ? $form->sample_disclaimer_payload : [];

        return $this->mergeFormPayloads($existing, [
            'client_name' => (string) ($form->customer_name ?? ''),
            'lab_no' => (string) ($form->sampleHeader?->batch_code ?? ''),
            'number_of_samples' => (int) ($form->number_of_samples ?? 0),
            'disclaimant_name' => (string) ($existing['disclaimant_name'] ?? $form->customer_name ?? ''),
            'claimant_name' => (string) ($existing['claimant_name'] ?? $form->customer_signer_name ?? $form->customer_name ?? ''),
        ]);
    }

    public function mergePayloadIntoAcceptanceForm(AnalysisAcceptanceForm $form, array $patch): void
    {
        $existing = is_array($form->sample_disclaimer_payload) ? $form->sample_disclaimer_payload : [];
        $form->update([
            'sample_disclaimer_payload' => $this->mergeFormPayloads($existing, $patch),
        ]);
    }

    public function resolveFormStateForAcceptanceForm(AnalysisAcceptanceForm $form): array
    {
        if (! $form->raises_sample_disclaimer) {
            return self::emptyForm();
        }

        return $this->hydrateDefaultsFromAcceptanceForm($form);
    }

    public function applyBatchDefaultsAfterCustomerSign(AnalysisAcceptanceForm $form): void
    {
        if (! $form->raises_sample_disclaimer) {
            return;
        }

        $form->refresh();
        if (! $form->sample_header_id) {
            return;
        }

        $batch = SampleHeader::query()->find((string) $form->sample_header_id);
        if (! $batch) {
            return;
        }

        $existing = is_array($form->sample_disclaimer_payload) ? $form->sample_disclaimer_payload : [];
        $merged = $this->mergeFormPayloads($existing, [
            'lab_no' => (string) ($batch->batch_code ?? ''),
        ]);
        $form->update(['sample_disclaimer_payload' => $merged]);
    }

    public function claimantSignatureMissing(array $form): bool
    {
        return trim((string) ($form['claimant_signature'] ?? '')) === '';
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function wizardValidationRules(bool $requireClaimantSignature = false): array
    {
        $rules = [
            'disclaimerForm.client_name' => ['required', 'string', 'max:255'],
            'disclaimerForm.lab_no' => ['nullable', 'string', 'max:255'],
            'disclaimerForm.date' => ['required', 'date'],
            'disclaimerForm.time' => ['required', 'string', 'max:20'],
            'disclaimerForm.sample_types' => ['required', 'string', 'max:500'],
            'disclaimerForm.number_of_samples' => ['required', 'integer', 'min:1'],
            'disclaimerForm.disclaimant_name' => ['required', 'string', 'max:255'],
            'disclaimerForm.analyst_name' => ['required', 'string', 'max:255'],
            'disclaimerForm.analyst_signature' => ['required', 'string'],
            'disclaimerForm.analyst_signed_at' => ['required', 'date'],
        ];

        if ($requireClaimantSignature) {
            $rules['disclaimerForm.claimant_name'] = ['required', 'string', 'max:255'];
            $rules['disclaimerForm.claimant_signature'] = ['required', 'string'];
            $rules['disclaimerForm.claimant_signed_at'] = ['required', 'date'];
        } else {
            $rules['disclaimerForm.claimant_name'] = ['nullable', 'string', 'max:255'];
            $rules['disclaimerForm.claimant_signature'] = ['nullable', 'string'];
            $rules['disclaimerForm.claimant_signed_at'] = ['nullable', 'date'];
        }

        return $rules;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function portalClaimantValidationRules(): array
    {
        return [
            'sample_disclaimer' => ['nullable', 'array'],
            'sample_disclaimer.claimant_name' => ['nullable', 'string', 'max:255'],
            'sample_disclaimer.claimant_signature' => ['nullable', 'string'],
            'sample_disclaimer.claimant_signed_at' => ['nullable', 'date'],
        ];
    }

    /**
     * @param  array<string, mixed>|null  $incoming
     * @return array<string, mixed>
     */
    public function sanitizePortalClaimantPatch(?array $incoming): array
    {
        if (! is_array($incoming)) {
            return [];
        }

        $patch = [];
        foreach (['claimant_name', 'claimant_signature', 'claimant_signed_at'] as $key) {
            if (! array_key_exists($key, $incoming)) {
                continue;
            }
            $value = $incoming[$key];
            if ($value === null || (is_string($value) && trim($value) === '')) {
                continue;
            }
            $patch[$key] = $value;
        }

        if (isset($patch['claimant_signature'])) {
            $patch['claimant_signature_source'] = 'portal';
        }

        return $patch;
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public function tryFinalizePdfAttachment(AnalysisAcceptanceForm $acceptanceForm, SampleHeader $batch, array $formData, ?string $userId): ?string
    {
        if (! $acceptanceForm->raises_sample_disclaimer) {
            return null;
        }

        $validator = Validator::make(
            ['form' => $formData],
            $this->pdfValidationRules(),
            $this->validationMessages()
        );

        if ($validator->fails()) {
            return null;
        }

        return $this->generatePdfAndStoreAttachment($batch, $formData, $userId);
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    public function generatePdfAndStoreAttachment(SampleHeader $batch, array $formData, ?string $userId): string
    {
        $pdfFilename = 'sample-receiving-disclaimer-batch-' . $batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('batch.attachments.sample-receiving-disclaimer-pdf', [
            'batch' => $batch,
            'form' => $formData,
        ]);

        \Illuminate\Support\Facades\Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        $attachmentTypeId = app(AttachmentTypeResolver::class)->resolveOrCreateAttachmentTypeId(self::ATTACHMENT_TITLE);

        $attachment = BatchAttachment::query()
            ->where('batch_id', $batch->id)
            ->where('title', self::ATTACHMENT_TITLE)
            ->orderByDesc('created_at')
            ->first();

        if (! $attachment) {
            $attachment = new BatchAttachment();
            $attachment->batch_id = $batch->id;
            $attachment->uploaded_by = $userId ?? Auth::id();
            $attachment->title = self::ATTACHMENT_TITLE;
            $attachment->is_internal = 0;
        }

        $attachment->show_on_coa = 1;
        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $pdfPublicUrl;
        $attachment->save();

        return $pdfPublicUrl;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function pdfValidationRules(): array
    {
        return [
            'form.client_name' => 'required|string|max:255',
            'form.date' => 'required|date',
            'form.sample_types' => 'required|string',
            'form.disclaimant_name' => 'required|string|max:255',
            'form.analyst_name' => 'required|string|max:255',
            'form.analyst_signature' => 'required|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function validationMessages(): array
    {
        return [
            'form.analyst_signature.required' => 'Laboratory analyst signature is required.',
            'disclaimerForm.analyst_signature.required' => 'Laboratory analyst signature is required.',
        ];
    }

    private function resolveAttachmentTypeId(): ?string
    {
        $label = self::ATTACHMENT_TITLE;

        $existingId = SystemConfiguration::query()->where('key', 'attachment_type')
            ->where('value', $label)
            ->value('id');

        if ($existingId !== null) {
            return $existingId;
        }

        $existingType = SystemConfiguration::query()->where('key', 'attachment_type')->whereNotNull('configuration_type_id')->first();
        if (! $existingType) {
            return null;
        }

        $newConfig = new SystemConfiguration();
        $newConfig->key = 'attachment_type';
        $newConfig->value = $label;
        $newConfig->configuration_type_id = $existingType->configuration_type_id;
        $newConfig->save();

        return $newConfig->id;
    }
}
