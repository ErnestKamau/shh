<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
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
        });

        session()->flash('success', 'Subcontracting request dispatched successfully.');
        $this->dispatch('subcontract-dispatch-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.dispatch-subcontract-request');
    }
}