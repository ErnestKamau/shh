<?php

namespace App\Livewire\Sampleworkflow;

use App\Services\Sampleworkflow\SampleRejectionPrefillService;
use App\Services\Sampleworkflow\SampleRejectionReasonService;
use App\Services\Sampleworkflow\SampleRejectionService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class SampleRejectionWizard extends Component
{
    public bool $showModal = false;

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionRequestId = null;

    public string $requestNo = '';

    public string $clientName = '';

    public ?string $dateSampleReceived = null;

    public string $typeOfSample = '';

    public int $numberOfSamples = 1;

    /** @var list<array{key: string, label: string}> */
    public array $availableReasons = [];

    /** @var array<string, bool> */
    public array $selectedReasonKeys = [];

    /** @var array<string, string> */
    public array $reasonExplanations = [];

    public bool $reasonsConfigMissing = false;

    #[On('open-rejection-wizard')]
    public function openWizard(?string $submissionFormInstanceId = null, ?string $submissionRequestId = null): void
    {
        $this->resetWizard();
        $this->submissionFormInstanceId = $submissionFormInstanceId ?: null;
        $this->submissionRequestId = $submissionRequestId ?: null;

        if (! $this->submissionFormInstanceId && ! $this->submissionRequestId) {
            $this->dispatch('notify', type: 'error', message: 'Select exactly one request or form row before rejecting.');

            return;
        }

        $prefill = app(SampleRejectionPrefillService::class)->buildFromSelection(
            $this->submissionRequestId,
            $this->submissionFormInstanceId
        );

        $this->requestNo = $prefill['request_no'];
        $this->clientName = $prefill['client_name'];
        $this->dateSampleReceived = $prefill['date_sample_received'];
        $this->typeOfSample = $prefill['type_of_sample'];
        $this->numberOfSamples = $prefill['number_of_samples'];
        $this->submissionFormInstanceId = $prefill['submission_form_instance_id'];
        $this->submissionRequestId = $prefill['sample_submission_request_id'];

        $this->availableReasons = app(SampleRejectionReasonService::class)->getReasons();
        $this->reasonsConfigMissing = $this->availableReasons === [];

        $this->showModal = true;
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->resetWizard();
    }

    public function submitRejection(): void
    {
        if ($this->reasonsConfigMissing) {
            $this->dispatch('notify', type: 'error', message: 'Rejection reasons are not configured. Contact your administrator.');

            return;
        }

        $selected = [];
        foreach ($this->availableReasons as $reason) {
            $key = (string) ($reason['key'] ?? '');
            if ($key === '' || empty($this->selectedReasonKeys[$key])) {
                continue;
            }

            $selected[] = [
                'key' => $key,
                'label' => (string) ($reason['label'] ?? $key),
                'explanation' => trim((string) ($this->reasonExplanations[$key] ?? '')),
            ];
        }

        try {
            app(SampleRejectionService::class)->rejectFromRequestReview(
                $this->submissionFormInstanceId,
                $this->submissionRequestId,
                $selected,
                (string) Auth::id(),
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        $this->closeWizard();
        $this->dispatch('notify', type: 'success', message: 'Sample request rejected successfully.');
        $this->dispatch('sample-rejection-completed');
    }

    private function resetWizard(): void
    {
        $this->reset([
            'submissionFormInstanceId',
            'submissionRequestId',
            'requestNo',
            'clientName',
            'dateSampleReceived',
            'typeOfSample',
            'numberOfSamples',
            'availableReasons',
            'selectedReasonKeys',
            'reasonExplanations',
            'reasonsConfigMissing',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.sampleworkflow.sample-rejection-wizard');
    }
}
