<?php

namespace App\Livewire\Batch;

use App\Models\CRM\CustomerContact;
use App\Models\SubmissionFormInstance;
use App\SampleHeader;
use App\Services\Sampleworkflow\TrfSampleFieldMapper;
use App\Services\SubmissionForm\SubmissionFormValueNormalizer;
use Illuminate\Support\Collection;
use Livewire\Component;

class Info extends Component
{
    public SampleHeader $batch;
    public $batchID;
    public $clients = [];
    public $sample_types = [];
    public $labsections = [];
    public $labs = [];
    public $samplingmethods = [];
    public $recieving_users = [];
    public $qc_schemes = [];
    public $qc_types = [];
    public $active_company;
    public ?string $selectedModeOfPayment = null;
    public $defaultClient;
    public $clientPageSize;
    public $maxDate;

    /** @var Collection<int, CustomerContact> */
    public Collection $customerContacts;

    protected $listeners = ['batchUpdated' => 'onBatchUpdated'];

    public function onBatchUpdated(): void
    {
        $this->batch->refresh();
        $this->selectedModeOfPayment = $this->resolveModeOfPaymentForCustomer(
            $this->batch->crm_customer_id ? (string) $this->batch->crm_customer_id : null
        );
        $this->customerContacts = $this->loadCustomerContacts();
    }

    public function mount(
        SampleHeader $batch,
        $batchID,
        $clients,
        $sample_types,
        $labsections,
        $labs,
        $samplingmethods,
        $recieving_users,
        $qc_schemes,
        $qc_types,
        $active_company,
        $defaultClient = false,
        $clientPageSize = 50
    ) {
        $this->batch = $batch;
        $this->batchID = $batchID;
        $this->clients = $clients;
        $this->sample_types = $sample_types;
        $this->labsections = $labsections;
        $this->labs = $labs;
        $this->samplingmethods = $samplingmethods;
        $this->recieving_users = $recieving_users;
        $this->qc_schemes = $qc_schemes;
        $this->qc_types = $qc_types;
        $this->active_company = $active_company;
        $this->defaultClient = $defaultClient;
        $this->clientPageSize = $clientPageSize;
        $this->maxDate = getTodayDate();
        $this->selectedModeOfPayment = $this->resolveModeOfPaymentForCustomer($batch->crm_customer_id ?? null);

        $this->backfillContactFieldsFromSubmissionForm();
        $this->customerContacts = $this->loadCustomerContacts();
        app(\App\Services\Sampleworkflow\SampleAnalysisSetupService::class)
            ->syncBatchSampleTypeIdFromSamples($this->batch);
        $this->batch->refresh();
    }

    private function loadCustomerContacts(): Collection
    {
        $customerId = trim((string) ($this->batch->crm_customer_id ?? ''));
        if ($customerId === '') {
            return collect();
        }

        return CustomerContact::query()
            ->where('crm_customer_id', $customerId)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    private function resolveModeOfPaymentForCustomer(?string $customerId): ?string
    {
        if ($customerId === null || $customerId === '') {
            return null;
        }

        $customer = \App\Models\CRM\CRMCustomer::query()->find($customerId);
        if ($customer === null) {
            return null;
        }

        return (int) ($customer->credit_days ?? 0) > 0 ? 'Post-Paid' : 'Pre-Paid';
    }

    private function backfillContactFieldsFromSubmissionForm(): void
    {
        $updates = [];

        if ($this->batch->submission_form_instance_id !== null) {
            $instance = SubmissionFormInstance::query()
                ->with(['values.element', 'crmCustomer'])
                ->find($this->batch->submission_form_instance_id);

            if ($instance !== null) {
                $formData = app(SubmissionFormValueNormalizer::class)->valuesMapFromInstance($instance);
                $mapped = app(TrfSampleFieldMapper::class)->mapToSampleHeader($formData, [
                    'crm_customer_id' => $this->batch->crm_customer_id,
                ]);

                if (trim((string) ($this->batch->crm_contact_id ?? '')) === '' && ! empty($mapped['crm_contact_id'])) {
                    $updates['crm_contact_id'] = $mapped['crm_contact_id'];
                }
                if (trim((string) ($this->batch->schedule_customer_email ?? '')) === '' && ! empty($mapped['schedule_customer_email'])) {
                    $updates['schedule_customer_email'] = $mapped['schedule_customer_email'];
                }
            }
        }

        $contactId = trim((string) ($updates['crm_contact_id'] ?? $this->batch->crm_contact_id ?? ''));
        if (trim((string) ($this->batch->schedule_customer_email ?? '')) === ''
            && trim((string) ($updates['schedule_customer_email'] ?? '')) === ''
            && $contactId !== '') {
            $contactEmail = $this->resolveContactEmail($contactId);
            if ($contactEmail !== '') {
                $updates['schedule_customer_email'] = $contactEmail;
            }
        }

        if ($updates === []) {
            return;
        }

        $this->batch->update($updates);
        $this->batch->refresh();
    }

    private function resolveContactEmail(string $contactId): string
    {
        $contact = CustomerContact::query()->find($contactId);
        if ($contact === null) {
            return '';
        }

        return trim((string) ($contact->email ?? ''));
    }

    public function render(): \Illuminate\View\View
    {
        $exportationSampleInfo = app(\App\Services\Sampleworkflow\TestRequestReportDataService::class)
            ->exportationSampleInfoForBatch($this->batch);

        $batchSampleTypes = app(\App\Services\Sampleworkflow\SampleAnalysisSetupService::class)
            ->sampleTypeLabelsFromSamples($this->batch);

        return view('livewire.batch.info', [
            'exportationSampleInfo' => $exportationSampleInfo,
            'batchSampleTypes' => $batchSampleTypes,
        ]);
    }
}
