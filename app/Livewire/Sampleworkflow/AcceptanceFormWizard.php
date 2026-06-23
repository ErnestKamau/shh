<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SubmissionFormInstance;
use App\Services\Commercial\CommercialEnquiryFromFormService;
use App\Services\Commercial\EnquiryReceptionReadinessService;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\CustomerContactVerificationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class AcceptanceFormWizard extends Component
{
    public bool $showModal = false;

    public ?string $submissionFormInstanceId = null;

    public ?string $submissionRequestId = null;

    public string $customerName = '';

    public ?string $requestDate = null;

    public int $numberOfSamples = 1;

    public string $modeOfWork = 'Normal';

    public ?string $dateOfSampling = null;

    /** @var list<array<string, mixed>> */
    public array $lines = [];

    /** @var list<array<string, mixed>> */
    public array $sampleConfigs = [];

    public ?string $crmCustomerId = null;

    public string $receivingPersonName = '';

    public string $receivingPersonSignature = '';

    public ?string $receivingPersonSignedAt = null;

    public string $selectedCustomerContactId = '';

    public string $customerSignerName = '';

    public string $customerSignature = '';

    public ?string $customerSignedAt = null;

    /** @var list<array{id: string, label: string}> */
    public array $customerContactOptions = [];

    public function mount(): void
    {
        $this->receivingPersonName = (string) (Auth::user()->name ?? '');
        $this->requestDate = now()->format('Y-m-d');
        $this->receivingPersonSignedAt = now()->format('Y-m-d');
        $this->customerSignedAt = now()->format('Y-m-d');
    }

    #[On('open-acceptance-wizard')]
    public function openWizard(?string $submissionFormInstanceId = null, ?string $submissionRequestId = null): void
    {
        $this->resetWizard();
        $this->submissionFormInstanceId = $submissionFormInstanceId ?: null;
        $this->submissionRequestId = $submissionRequestId ?: null;

        if (! $this->submissionFormInstanceId && ! $this->submissionRequestId) {
            $this->dispatch('notify', type: 'error', message: 'Select exactly one request or form row before accepting.');

            return;
        }

        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->with('sampleSubmissionRequest')->find($this->submissionFormInstanceId)
            : null;

        $enquiry = $instance?->sampleSubmissionRequest;
        if ($enquiry === null && $this->submissionRequestId) {
            $enquiry = SampleSubmissionRequest::query()->find($this->submissionRequestId);
        }

        if ($instance !== null && app(CommercialEnquiryFromFormService::class)->isCommercialTestRequestForm($instance)) {
            $readiness = app(EnquiryReceptionReadinessService::class);

            if ($enquiry === null) {
                $this->dispatch('notify', type: 'error', message: 'No commercial enquiry is linked to this test request.');

                return;
            }

            if (! $readiness->isEligibleForPhysicalReceive($enquiry)) {
                $message = match ((string) $enquiry->status) {
                    SampleSubmissionRequest::STATUS_REQUESTED,
                    SampleSubmissionRequest::STATUS_QUOTATION_IN_PROGRESS => 'Complete enquiry processing and send the quotation before accepting samples.',
                    SampleSubmissionRequest::STATUS_QUOTATION_SENT => 'Record customer acceptance on the request view page first.',
                    SampleSubmissionRequest::STATUS_QUOTATION_UNDER_REVIEW => 'Quotation is under review with the customer.',
                    SampleSubmissionRequest::STATUS_QUOTATION_ACCEPTED => 'Record the customer PO on the request view page before physical reception.',
                    default => 'This commercial request is not ready for physical reception yet.',
                };
                $this->dispatch('notify', type: 'error', message: $message);

                return;
            }
        }

        $prefill = app(AcceptanceFormPricingService::class)->buildPrefillFromSelection(
            $this->submissionRequestId,
            $this->submissionFormInstanceId
        );

        $this->crmCustomerId = $prefill['customer_id'];
        $this->customerName = $prefill['customer_name'];
        $this->requestDate = $prefill['request_date'] ?? now()->format('Y-m-d');
        $this->numberOfSamples = (int) ($prefill['number_of_samples'] ?? 1);
        $this->modeOfWork = (string) ($prefill['mode_of_work'] ?? 'Normal');
        $this->dateOfSampling = $prefill['date_of_sampling'];
        $pricingService = app(AcceptanceFormPricingService::class);

        $prefillLines = collect($pricingService->deduplicateRedundantAnalysisTypeLines($prefill['lines']))
            ->map(function (array $line, int $index) {
                return [
                    'line_no' => $index + 1,
                    'sample_type_id' => $line['sample_type_id'] ?? null,
                    'sample_type_name' => $line['sample_type_id'] ? optional(\App\SampleType::find($line['sample_type_id']))->name : '',
                    'analysis_type_id' => $line['analysis_type_id'] ?? null,
                    'analysis_type_name' => $line['analysis_type_id'] ? optional(\App\AnalysisType::find($line['analysis_type_id']))->name : '',
                    'analysis_element_id' => $line['analysis_element_id'] ?? null,
                    'parameter_label' => $line['parameter_label'] ?? '',
                    'unit_amount' => (float) ($line['unit_amount'] ?? 0),
                    'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                    'is_approved' => (bool) ($line['is_approved'] ?? true),
                    'sort_order' => $index,
                ];
            })->values()->all();

        $instance = $this->submissionFormInstanceId
            ? SubmissionFormInstance::query()->find($this->submissionFormInstanceId)
            : null;

        $configService = app(AcceptanceFormSampleConfigService::class);
        $storedConfig = is_array($enquiry?->enquiry_sample_configuration) ? $enquiry->enquiry_sample_configuration : [];

        if ($storedConfig !== []) {
            $this->sampleConfigs = $configService->normalizeConfigsForStorage($storedConfig);
        } else {
            $this->sampleConfigs = $configService->buildConfigsFromPrefill($prefillLines, $instance);
            $defaultZoneId = $configService->resolveZoneIdFromInstance($instance);
            if ($defaultZoneId !== null) {
                foreach ($this->sampleConfigs as $index => $config) {
                    if (empty($config['zone_id'])) {
                        $this->sampleConfigs[$index]['zone_id'] = $defaultZoneId;
                    }
                }
            }
        }

        $this->lines = collect($prefillLines)->map(function (array $line, int $index): array {
            return [
                'line_no' => $index + 1,
                'sample_type_id' => $line['sample_type_id'] ?? null,
                'sample_type_name' => $line['sample_type_name'] ?? '',
                'analysis_type_id' => $line['analysis_type_id'] ?? null,
                'analysis_type_name' => $line['analysis_type_name'] ?? '',
                'analysis_element_id' => $line['analysis_element_id'] ?? null,
                'parameter_label' => $line['parameter_label'] ?? '',
                'unit_amount' => (float) ($line['unit_amount'] ?? 0),
                'number_of_samples' => (int) ($line['number_of_samples'] ?? 1),
                'is_approved' => true,
                'sort_order' => $index,
            ];
        })->values()->all();

        $this->loadCustomerContactOptions($enquiry);

        if ($this->customerContactOptions === []) {
            $this->dispatch('notify', type: 'error', message: 'No active customer contacts found. Add a contact for this customer before accepting samples.');

            return;
        }

        $this->showModal = true;
        $this->dispatch('acceptance-wizard-opened');
    }

    public function updatedSelectedCustomerContactId(): void
    {
        $this->syncCustomerSignerNameFromContact();
    }

    public function closeWizard(): void
    {
        $this->showModal = false;
        $this->resetWizard();
    }

    public function submitDualAccept(): void
    {
        $this->validate([
            'receivingPersonName' => ['required', 'string', 'max:255'],
            'receivingPersonSignature' => ['required', 'string'],
            'receivingPersonSignedAt' => ['required', 'date'],
            'selectedCustomerContactId' => ['required', 'string'],
            'customerSignerName' => ['required', 'string', 'max:255'],
            'customerSignature' => ['required', 'string'],
            'customerSignedAt' => ['required', 'date'],
            'lines' => ['required', 'array', 'min:1'],
        ], [
            'receivingPersonName.required' => 'Enter the receiving personnel name.',
            'receivingPersonSignature.required' => 'Provide the receiving personnel signature.',
            'selectedCustomerContactId.required' => 'Select the customer contact who is signing.',
            'customerSignerName.required' => 'Enter the customer contact name.',
            'customerSignature.required' => 'Provide the customer contact signature.',
        ]);

        $configService = app(AcceptanceFormSampleConfigService::class);
        $normalizedConfigs = $configService->normalizeConfigsForStorage($this->sampleConfigs);

        $header = [
            'crm_customer_id' => $this->crmCustomerId,
            'customer_name' => $this->customerName,
            'request_date' => $this->requestDate,
            'number_of_samples' => $this->numberOfSamples,
            'mode_of_work' => $this->modeOfWork,
            'date_of_sampling' => $this->dateOfSampling,
            'sample_configuration_payload' => $normalizedConfigs,
        ];

        try {
            $form = app(AcceptanceFormService::class)->acceptWithDualSignatures(
                $this->submissionFormInstanceId,
                $this->submissionRequestId,
                $header,
                $this->lines,
                $this->receivingPersonName,
                $this->receivingPersonSignature,
                $this->receivingPersonSignedAt,
                $this->selectedCustomerContactId,
                $this->customerSignerName,
                $this->customerSignature,
                $this->customerSignedAt,
                auth()->id() ? (string) auth()->id() : null,
            );
        } catch (\Throwable $exception) {
            $this->dispatch('notify', type: 'error', message: $exception->getMessage());

            return;
        }

        $batchId = (string) ($form->sample_header_id ?? '');
        $batchCode = $batchId !== ''
            ? (string) (\App\SampleHeader::query()->where('id', $batchId)->value('batch_code') ?? '')
            : '';

        $this->closeWizard();

        if ($batchId === '') {
            $this->dispatch('notify', type: 'error', message: 'Samples were accepted but the job number could not be created. Check the acceptance form processing error.');

            return;
        }

        $redirectUrl = route('view-batch-details', [
            'batch' => $batchId,
            'client' => 0,
            'portal' => 0,
            'status' => 'Samples In Lab',
        ]) . '#samples';

        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl, batchCode: $batchCode);
        session()->flash('success', "Samples accepted. Job number {$batchCode} created and moved to Samples In Lab.");
    }

    private function loadCustomerContactOptions(?SampleSubmissionRequest $enquiry): void
    {
        if (! $this->crmCustomerId) {
            $this->customerContactOptions = [];

            return;
        }

        $this->customerContactOptions = app(CustomerContactVerificationService::class)
            ->activeContactsForCustomer((string) $this->crmCustomerId);

        $defaultContactId = (string) ($enquiry?->crm_customer_contact_id ?? '');

        if ($defaultContactId !== '' && collect($this->customerContactOptions)->contains('id', $defaultContactId)) {
            $this->selectedCustomerContactId = $defaultContactId;
        } elseif ($this->customerContactOptions !== []) {
            $this->selectedCustomerContactId = $this->customerContactOptions[0]['id'];
        }

        $this->syncCustomerSignerNameFromContact();
    }

    private function syncCustomerSignerNameFromContact(): void
    {
        $match = collect($this->customerContactOptions)
            ->firstWhere('id', $this->selectedCustomerContactId);

        if ($match !== null) {
            $this->customerSignerName = (string) ($match['label'] ?? '');
        }
    }

    private function resetWizard(): void
    {
        $this->submissionFormInstanceId = null;
        $this->submissionRequestId = null;
        $this->lines = [];
        $this->sampleConfigs = [];
        $this->crmCustomerId = null;
        $this->customerName = '';
        $this->numberOfSamples = 1;
        $this->modeOfWork = 'Normal';
        $this->dateOfSampling = null;
        $this->requestDate = now()->format('Y-m-d');
        $this->receivingPersonName = (string) (Auth::user()->name ?? '');
        $this->receivingPersonSignature = '';
        $this->receivingPersonSignedAt = now()->format('Y-m-d');
        $this->selectedCustomerContactId = '';
        $this->customerSignerName = '';
        $this->customerSignature = '';
        $this->customerSignedAt = now()->format('Y-m-d');
        $this->customerContactOptions = [];
    }

    public function render()
    {
        return view('livewire.sampleworkflow.acceptance-form-wizard');
    }
}
