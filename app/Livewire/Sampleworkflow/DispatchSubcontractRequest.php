<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DispatchSubcontractRequest extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    public string $barcode = '';

    public ?string $labelUrl = null;

    public ?string $selectedEnquiryId = null;

    public ?string $selectedFormInstanceId = null;

    public function handleSubcontractDispatchModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->barcode = '';
        $this->labelUrl = null;
        $this->selectedEnquiryId = null;
        $this->selectedFormInstanceId = null;
        $this->resetValidation();

        if (count($this->selectedFormInstanceIds) === 1) {
            $instance = SubmissionFormInstance::query()
                ->with('sampleSubmissionRequest')
                ->find($this->selectedFormInstanceIds[0]);

            if ($instance !== null) {
                $this->selectedFormInstanceId = (string) $instance->id;
                $this->selectedEnquiryId = (string) ($instance->sampleSubmissionRequest?->id ?? '');
                $this->labelUrl = route('submission-forms.instances.sample-collection-label', ['instance' => $instance->id]);
            }
        }

        $this->dispatch('show-subcontract-dispatch-modal');
    }

    protected function getListeners(): array
    {
        return [
            'subcontract-dispatch-modal-open' => 'handleSubcontractDispatchModalOpen',
        ];
    }

    public function openLabelInNewTab(): void
    {
        if ($this->labelUrl === null || $this->labelUrl === '') {
            $this->addError('barcode', 'A label could not be prepared for this request.');

            return;
        }

        $this->dispatch('open-subcontract-label-tab', url: $this->labelUrl);
    }

    public function confirmDispatch(): void
    {
        if (count($this->selectedFormInstanceIds) !== 1 || empty($this->selectedFormInstanceId) || empty($this->selectedEnquiryId)) {
            $this->addError('selection', 'Select exactly one subcontracting request before dispatching.');

            return;
        }

        $this->validate([
            'barcode' => ['required', 'string', 'max:255'],
        ], [
            'barcode.required' => 'Scan or enter the request barcode before dispatching.',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to dispatch a subcontracting request.');

            return;
        }

        $instance = SubmissionFormInstance::query()
            ->with('sampleSubmissionRequest')
            ->find($this->selectedFormInstanceId);

        if ($instance === null || $instance->sampleSubmissionRequest === null) {
            $this->addError('selection', 'The selected subcontracting request could not be found.');

            return;
        }

        $expectedCodes = array_values(array_unique(array_filter([
            trim((string) ($instance->id ?? '')),
            trim((string) ($instance->form_number ?? '')),
            trim((string) ($instance->sampleSubmissionRequest->unique_identification ?? '')),
            trim((string) ($instance->sampleSubmissionRequest->id ?? '')),
        ])));

        $inputBarcode = trim($this->barcode);
        if (! in_array($inputBarcode, $expectedCodes, true)) {
            $this->addError('barcode', 'The scanned barcode does not match the selected request label. Generate/print the request label and scan it again.');

            return;
        }

        DB::transaction(function () use ($instance, $user): void {
            $enquiry = $instance->sampleSubmissionRequest;
            $enquiry->subcontracting_dispatch_status = SampleSubmissionRequest::SUBCONTRACT_DISPATCH_DISPATCHED;
            $enquiry->subcontracting_dispatch_date = now();
            $enquiry->save();

            if ($instance->status !== 'approved') {
                $instance->update([
                    'status' => 'approved',
                    'reviewed_at' => $instance->reviewed_at ?? now(),
                    'reviewed_by' => $user->id,
                    'review_notes' => 'Subcontracting dispatch confirmed from Samples Receiving queue.',
                ]);

                $instance->logAction('subcontract_dispatched', $user, [
                    'status' => ['from' => $instance->getOriginal('status') ?: $instance->status, 'to' => 'approved'],
                ], 'Subcontracting dispatch confirmed from Samples Receiving queue.');
            }

            $hasExistingJob = ! empty($enquiry->sample_header_id)
                || ! empty($instance->analysisAcceptanceForms()->value('sample_header_id'));

            if (! $hasExistingJob) {
                $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
                    (string) $enquiry->id,
                    (string) $instance->id,
                );

                $lines = $prefill['lines'] ?? [];
                if (is_array($lines) && $lines !== []) {
                    app(AcceptanceFormService::class)->acceptWithStaffSignature(
                        (string) $instance->id,
                        (string) $enquiry->id,
                        [
                            'crm_customer_id' => $prefill['customer_id'] ?? $enquiry->crm_customer_id,
                            'customer_name' => $prefill['customer_name'] ?? ($instance->crmCustomer?->name ?? ''),
                            'request_date' => $prefill['request_date'] ?? now()->format('Y-m-d'),
                            'number_of_samples' => (int) ($prefill['number_of_samples'] ?? max(1, (int) ($enquiry->number_of_samples ?? 1))),
                            'mode_of_work' => $prefill['mode_of_work'] ?? 'Normal',
                            'date_of_sampling' => $prefill['date_of_sampling'] ?? null,
                        ],
                        $lines,
                        (string) ($user->name ?? 'System Dispatch'),
                        'subcontract-dispatch-staff-signature',
                        now()->toDateString(),
                        (string) $user->id,
                    );
                }
            }
        });

        session()->flash('success', 'Subcontracting request dispatched successfully.');
        $this->dispatch('subcontract-dispatch-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.dispatch-subcontract-request');
    }
}