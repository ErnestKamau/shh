<?php

namespace App\Livewire\Batch;

use App\SampleHeader;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

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

    protected $listeners = ['batchUpdated' => '$refresh'];

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

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.info');
    }
}
