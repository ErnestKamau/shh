<?php

namespace App\Livewire\Batch\Tabs;

use App\BatchAttachment;
use App\SampleHeader;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;

class SampleReceiptNotification extends Component
{
    public SampleHeader $batch;

    public bool $readOnly = false;

    /** @var array<string, mixed> */
    public array $form = [];

    public ?string $attachmentUrl = null;

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;

        $service = app(SampleReceiptNotificationService::class);
        $this->form = $service->resolveFormStateForBatch($this->batch);

        $existing = BatchAttachment::where('batch_id', $this->batch->id)
            ->where('title', SampleReceiptNotificationService::ATTACHMENT_TITLE)
            ->orderByDesc('created_at')
            ->first();

        $this->attachmentUrl = $existing?->attachment_url;

        if (Auth::user() && (int) Auth::user()->is_client === 1) {
            $this->readOnly = true;
        }
    }

    public function saveDraft(): void
    {
        if ($this->readOnly) {
            return;
        }

        $service = app(SampleReceiptNotificationService::class);
        $acceptance = $service->findAcceptanceFormForBatch($this->batch);
        $service->persistForBatchLinkedAcceptance($acceptance, $this->batch, $this->form);

        session()->flash('success', 'Sample Receipt Notification draft saved.');
    }

    public function submitForm(): void
    {
        if ($this->readOnly) {
            return;
        }

        $service = app(SampleReceiptNotificationService::class);

        $validator = Validator::make(
            ['form' => $this->form],
            $service->fullValidationRules(),
            $service->validationMessages()
        );

        $validator->validate();

        $acceptance = $service->findAcceptanceFormForBatch($this->batch);
        $service->persistForBatchLinkedAcceptance($acceptance, $this->batch, $this->form);

        $this->attachmentUrl = $service->generatePdfAndStoreAttachment(
            $this->batch,
            $this->form,
            Auth::id()
        );

        $this->dispatch('attachmentsUpdated');

        session()->flash('success', 'Sample Receipt Notification submitted and attached to this batch.');
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.sample-receipt-notification');
    }
}
