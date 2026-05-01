<?php

namespace App\Livewire\Batch\Tabs;

use App\BatchAttachment;
use App\Models\System\SystemConfiguration;
use App\SampleHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class SampleReceiptNotification extends Component
{
    public SampleHeader $batch;

    public bool $readOnly = false;

    public array $form = [
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

    public ?string $attachmentUrl = null;

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        $this->hydrateDefaults();
        $this->loadExistingDraft();
        $this->loadExistingAttachment();

        if (Auth::user() && (int) Auth::user()->is_client === 1) {
            $this->readOnly = true;
        }
    }

    public function saveDraft(): void
    {
        if ($this->readOnly) {
            return;
        }

        Storage::disk('public')->put(
            $this->draftStoragePath(),
            json_encode($this->buildPayload(), JSON_PRETTY_PRINT)
        );

        session()->flash('success', 'Sample Receipt Notification draft saved.');
    }

    public function submitForm(): void
    {
        if ($this->readOnly) {
            return;
        }

        $this->validate($this->rules(), $this->messages());

        Storage::disk('public')->put(
            $this->draftStoragePath(),
            json_encode($this->buildPayload(), JSON_PRETTY_PRINT)
        );

        $pdfFilename = 'sample-receipt-notification-batch-' . $this->batch->id . '.pdf';
        $pdfStoragePath = 'batch-attachments/' . $pdfFilename;
        $pdfPublicUrl = '/storage/batch-attachments/' . urlencode($pdfFilename);

        $pdf = app('dompdf.wrapper');
        $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
        $pdf->loadView('batch.attachments.sample-receipt-notification-pdf', [
            'batch' => $this->batch,
            'form' => $this->form,
        ]);

        Storage::disk('public')->put($pdfStoragePath, $pdf->output());

        $attachmentTypeId = $this->resolveAttachmentTypeId();
        $title = 'Sample Receipt Notification (GCLA 01)';

        $attachment = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', $title)
            ->orderByDesc('created_at')
            ->first();

        if (! $attachment) {
            $attachment = new BatchAttachment();
            $attachment->batch_id = $this->batch->id;
            $attachment->uploaded_by = Auth::id();
            $attachment->title = $title;
            $attachment->is_internal = 0;
            $attachment->show_on_coa = 0;
        }

        $attachment->attachment_type = $attachmentTypeId;
        $attachment->attachment_url = $pdfPublicUrl;
        $attachment->save();

        $this->attachmentUrl = $pdfPublicUrl;
        $this->dispatch('attachmentsUpdated');

        session()->flash('success', 'Sample Receipt Notification submitted and attached to this batch.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.sample-receipt-notification');
    }

    /**
     * @return array<string, string>
     */
    private function rules(): array
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
     * @return array<string, string>
     */
    private function messages(): array
    {
        return [
            'form.submitter_signature.required' => 'Submitting person signature is required.',
            'form.receiver_signature.required' => 'Receiving person signature is required.',
        ];
    }

    private function hydrateDefaults(): void
    {
        $this->form['laboratory_identification_number'] = (string) ($this->batch->batch_code ?? '');
        $this->form['number_of_samples'] = (int) $this->batch->samples()->count();
    }

    private function loadExistingDraft(): void
    {
        $path = $this->draftStoragePath();
        if (! Storage::disk('public')->exists($path)) {
            return;
        }

        $decoded = json_decode((string) Storage::disk('public')->get($path), true);
        if (! is_array($decoded)) {
            return;
        }

        $savedForm = $decoded['form'] ?? null;
        if (! is_array($savedForm)) {
            return;
        }

        $this->form = array_merge($this->form, $savedForm);
    }

    private function loadExistingAttachment(): void
    {
        $existing = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', 'Sample Receipt Notification (GCLA 01)')
            ->orderByDesc('created_at')
            ->first();

        $this->attachmentUrl = $existing?->attachment_url;
    }

    private function draftStoragePath(): string
    {
        return 'batch-attachments/sample-receipt-notification-batch-' . $this->batch->id . '.json';
    }

    /**
     * @return array{form: array<string, mixed>}
     */
    private function buildPayload(): array
    {
        return [
            'form' => $this->form,
        ];
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
