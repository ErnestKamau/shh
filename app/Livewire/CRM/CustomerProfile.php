<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\SamplePoint;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerCertification;
use App\Models\CRM\Complaint;
use App\Models\CRM\CustomerFeedback;
use App\SampleHeader;
use App\BatchAmmendment;
use App\Country;
use App\ModulePreConfigs;
use Illuminate\Support\Facades\DB;

class CustomerProfile extends Component
{
    // Customer Data
    public $customer;
    public $customerId;
    
    // Tab Management
    public $activeTab = 'details';
    
    // Label Configuration
    public $showLabelModal = false;
    public $labelForm = [
        'unit_configurable_name' => '',
        'sub_unit_configurable_name' => '',
        'area_configurable_name' => '',
        'sample_point_configurable_name' => '',
        'product_configurable_name' => ''
    ];
    
    // Customer Form
    public $customerForm = [
        'name' => '',
        'postal_address' => '',
        'physical_address' => '',
        'website' => '',
        'fax' => '',
        'email' => '',
        'telephone1' => '',
        'telephone2' => '',
        'country_id' => null,
        'credit_days' => null,
        'active' => true,
        'account_status' => null,
        'vat_no' => '',
        'lpos_required' => false
    ];

    // Supporting Data
    public $countries = [];
    public $accounts = [];
    public $certifications = [];
    public $complaints = [];
    public $feedbacks = [];
    public $reports = [];
    public $amendments = [];

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $editingCustomer = false;

    protected $rules = [
        'customerForm.name' => 'required|string|max:255',
        'customerForm.postal_address' => 'required|string|max:500',
        'customerForm.physical_address' => 'required|string|max:500',
        'customerForm.email' => 'required|email|max:255',
        'customerForm.telephone1' => 'required|string|max:50',
        'customerForm.country_id' => 'required|exists:countries,id',
        'customerForm.account_status' => 'required|exists:module_pre_configs,id',
    ];

    protected $messages = [
        'customerForm.name.required' => 'Customer name is required.',
        'customerForm.postal_address.required' => 'Postal address is required.',
        'customerForm.physical_address.required' => 'Physical address is required.',
        'customerForm.email.required' => 'Email address is required.',
        'customerForm.email.email' => 'Please enter a valid email address.',
        'customerForm.telephone1.required' => 'Primary phone number is required.',
        'customerForm.country_id.required' => 'Country selection is required.',
        'customerForm.account_status.required' => 'Account settings selection is required.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->loadCustomerData();
        $this->loadInitialData();
    }

    public function loadCustomerData()
    {
        $this->customer = CRMCustomer::with(['country', 'currencyinfo', 'units.sample_points', 'contacts'])
            ->findOrFail($this->customerId);
        
        $this->loadCustomerForm();
        $this->loadRelatedData();
    }

    public function loadCustomerForm()
    {
        $this->customerForm = [
            'name' => $this->customer->name,
            'postal_address' => $this->customer->postal_address,
            'physical_address' => $this->customer->physical_address,
            'website' => $this->customer->website ?? '',
            'fax' => $this->customer->fax ?? '',
            'email' => $this->customer->email,
            'telephone1' => $this->customer->telephone1,
            'telephone2' => $this->customer->telephone2 ?? '',
            'country_id' => $this->customer->country_id,
            'credit_days' => $this->customer->credit_days,
            'active' => $this->customer->active == 1,
            'account_status' => $this->customer->account_status,
            'vat_no' => $this->customer->vat_no ?? '',
            'lpos_required' => $this->customer->lpos_required == 1
        ];
    }

    public function loadInitialData()
    {
        $this->countries = Country::orderBy('name')->get();
        
        $account_settings = getConfigTypeByName('Account Settings');
        if (isset($account_settings->id)) {
            $this->accounts = getconfigByID($account_settings->id);
        } else {
            $this->accounts = [];
        }
    }

    public function loadRelatedData()
    {
        // Only load data for the active tab to improve performance
        $this->loadTabData($this->activeTab);
    }

    public function loadTabData($tab)
    {
        switch ($tab) {
            case 'details':
                // Details tab doesn't need additional data
                break;
                
            case 'units':
                // Load units data when needed
                break;
                
            case 'sub-units':
                // Load sub units data when needed
                break;
                
            case 'areas':
                // Load areas data when needed
                break;
                
            case 'sample-points':
                // Load sample points data when needed
                break;
                
            case 'contacts':
                // Load contacts data when needed
                break;
                
            case 'reports':
                // Load reports data when needed
                $this->reports = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
                    ->selectRaw('sample_headers.*, st.name as sample_type')
                    ->where('crm_customer_id', $this->customerId)
                    ->where('sample_headers.status', 'Completed')
                    ->orderBy('id', 'desc')
                    ->get();
                break;
                
            case 'amendments':
                // Load amendments data when needed
                $this->amendments = BatchAmmendment::whereHas('sampleHeader', function($query) {
                        $query->where('crm_customer_id', $this->customerId);
                    })
                    ->with(['sampleHeader'])
                    ->orderBy('created_at', 'desc')
                    ->get();
                break;
        }
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
        $this->loadTabData($tab);
    }

    public function startEditing()
    {
        $this->editingCustomer = true;
    }

    public function cancelEditing()
    {
        $this->editingCustomer = false;
        $this->loadCustomerForm();
    }

    public function saveCustomer()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->customer->name = $this->customerForm['name'];
            $this->customer->postal_address = $this->customerForm['postal_address'];
            $this->customer->physical_address = $this->customerForm['physical_address'];
            $this->customer->website = $this->customerForm['website'];
            $this->customer->fax = $this->customerForm['fax'];
            $this->customer->email = $this->customerForm['email'];
            $this->customer->telephone1 = $this->customerForm['telephone1'];
            $this->customer->telephone2 = $this->customerForm['telephone2'];
            $this->customer->country_id = $this->customerForm['country_id'];
            $this->customer->credit_days = $this->customerForm['credit_days'];
            $this->customer->active = $this->customerForm['active'] ? 1 : 0;
            $this->customer->account_status = $this->customerForm['account_status'];
            $this->customer->vat_no = $this->customerForm['vat_no'];
            $this->customer->lpos_required = $this->customerForm['lpos_required'] ? 1 : 0;

            $this->customer->save();

            DB::commit();
            
            $this->editingCustomer = false;
            $this->loadCustomerData();
            $this->message = 'Customer updated successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function downloadReport($batchId)
    {
        $sampleHeader = SampleHeader::findOrFail($batchId);
        
        if ($sampleHeader->batch_report_url) {
            return response()->download(storage_path('app/public' . $sampleHeader->batch_report_url));
        }
        
        $this->message = 'Report not available for download.';
        $this->messageType = 'error';
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Label Configuration Methods
    public function openLabelModal()
    {
        $this->labelForm = [
            'unit_configurable_name' => $this->customer->unit_configurable_name ?? '',
            'sub_unit_configurable_name' => $this->customer->sub_unit_configurable_name ?? '',
            'area_configurable_name' => $this->customer->area_configurable_name ?? '',
            'sample_point_configurable_name' => $this->customer->sample_point_configurable_name ?? '',
            'product_configurable_name' => $this->customer->product_configurable_name ?? ''
        ];
        $this->showLabelModal = true;
    }

    public function closeLabelModal()
    {
        $this->showLabelModal = false;
    }

    public function saveLabels()
    {
        $this->validate([
            'labelForm.unit_configurable_name' => 'nullable|string|max:100',
            'labelForm.sub_unit_configurable_name' => 'nullable|string|max:100',
            'labelForm.area_configurable_name' => 'nullable|string|max:100',
            'labelForm.sample_point_configurable_name' => 'nullable|string|max:100',
            'labelForm.product_configurable_name' => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $this->customer->unit_configurable_name = $this->labelForm['unit_configurable_name'];
            $this->customer->sub_unit_configurable_name = $this->labelForm['sub_unit_configurable_name'];
            $this->customer->area_configurable_name = $this->labelForm['area_configurable_name'];
            $this->customer->sample_point_configurable_name = $this->labelForm['sample_point_configurable_name'];
            $this->customer->product_configurable_name = $this->labelForm['product_configurable_name'];
            $this->customer->save();

            DB::commit();
            
            $this->closeLabelModal();
            $this->loadCustomerData();
            $this->message = 'Tab labels updated successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.crm.customer-profile');
    }
}

