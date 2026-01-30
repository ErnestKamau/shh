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
    public $samplingmethods = [];
    public $recieving_users = [];
    public $qc_schemes = [];
    public $qc_types = [];
    public $batch_scope;
    public $customer_survey;
    public $active_company;
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
        $samplingmethods,
        $recieving_users,
        $qc_schemes,
        $qc_types,
        $batch_scope,
        $customer_survey,
        $active_company,
        $defaultClient = false,
        $clientPageSize = 50
    ) {
        $this->batch = $batch;
        $this->batchID = $batchID;
        $this->clients = $clients;
        $this->sample_types = $sample_types;
        $this->labsections = $labsections;
        $this->samplingmethods = $samplingmethods;
        $this->recieving_users = $recieving_users;
        $this->qc_schemes = $qc_schemes;
        $this->qc_types = $qc_types;
        $this->batch_scope = $batch_scope;
        $this->customer_survey = $customer_survey;
        $this->active_company = $active_company;
        $this->defaultClient = $defaultClient;
        $this->clientPageSize = $clientPageSize;
        $this->maxDate = getTodayDate();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.info');
    }
}
