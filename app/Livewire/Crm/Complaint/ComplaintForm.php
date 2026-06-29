<?php

namespace App\Livewire\Crm\Complaint;

use App\Models\CRM\Complaint;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\Chain_of_Custody_Complaint;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use App\SampleType;
use App\SampleHeader;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use Illuminate\Support\Facades\DB;

class ComplaintForm extends BaseCrmComponent
{
    public $complaintId = null;
    public $customerId = null;
    public $feedback_id = null;
    public $description = '';
    public $priority = '';
    public $type = '';
    public $received_from = '';
    public $date = '';
    public $received_from_type = 'Customer';
    public $is_lab_related = false;
    public $mode_of_delivery = [];
    public $nature_of_complaint = '';
    public $test_item = '';
    public $report_serial_no = '';
    public $title_position = '';
    public $organization_name = '';
    public $contact_name = '';
    public $customers = [];
    public $complaint_types = [];
    public $contacts = [];
    public $samples = [];
    public $serial_nos = [];
    public $isStandalone = false;

    public function mount($complaintId = null, $customerId = null)
    {
        $this->initialize();
        $this->isStandalone = \Illuminate\Support\Facades\Route::currentRouteName() === 'complaint-create';
        $this->customerId = $customerId;
        $this->customers = CRMCustomer::where('company_id', $this->getUserCompany())->orderBy('name')->get();
        $this->complaint_types = Complaint_Type::all();
        $this->date = date('Y-m-d');

        // Check for feedback_id to link complaint and auto-fill form data
        $feedbackId = request()->query('feedback_id');
        if ($feedbackId) {
            $this->feedback_id = $feedbackId;
            $feedback = CustomerFeedback::with(['customer', 'contact'])->find($feedbackId);
            if ($feedback) {
                // 1. Customer / Organization
                if ($feedback->customer) {
                    $this->customerId        = $feedback->customer->id;
                    $this->organization_name = $feedback->customer->name;
                }

                // 2. Contact (store ID so the Select2 dropdown pre-selects it)
                if ($feedback->contact_id) {
                    $this->contact_name   = $feedback->contact_id;
                    $this->title_position = optional($feedback->contact)->job_occupation ?? '';
                }

                // 3. Mode of delivery — always "Customer Feedback Survey" when raised from feedback
                $this->mode_of_delivery = ['Customer Feedback Survey'];

                // 4. Description — use the reported issue text as the starting complaint detail
                if (!empty($feedback->issue_description)) {
                    $this->description = $feedback->issue_description;
                }

                // 5. Keep received_from_type as Customer
                $this->received_from_type = 'Customer';

                // 6. Populate contacts & samples dropdowns for the pre-selected customer
                $this->loadDynamicData();
            }
        }

        if ($complaintId) {
            $this->loadComplaint($complaintId);
        } elseif ($customerId) {
             $customer = CRMCustomer::find($customerId);
             if ($customer) {
                $this->organization_name = $customer->name;
                $this->received_from = $customer->name;
                $this->loadDynamicData();
             }
        }
    }

    public function loadDynamicData()
    {
        if ($this->received_from_type !== 'Customer') {
            $this->contacts = [];
            $this->samples = [];
            return;
        }

        $customer = null;
        if ($this->customerId) {
            $customer = CRMCustomer::find($this->customerId);
        } else {
            $customer = CRMCustomer::where('name', $this->organization_name)->first();
        }

        if ($customer) {
            $this->customerId = $customer->id;
            $this->contacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $customer->id)
                ->where('active', 1)
                ->get();

            // Auto-select if ONLY one contact exists
            if (count($this->contacts) === 1) {
                $contact = $this->contacts instanceof \Illuminate\Support\Collection ? $this->contacts->first() : $this->contacts[0];
                $this->contact_name = $contact->id;
                $this->title_position = $contact->job_occupation;
            }
            
            // Fetch unique SampleTypes for this customer from their orders
            $sampleTypeIds = SampleHeader::query()
                ->where('crm_customer_id', $customer->id)
                ->whereNotNull('sample_type_id')
                ->where('sample_type_id', '!=', '')
                ->distinct()
                ->pluck('sample_type_id')
                ->toArray();

            $this->samples = SampleType::query()
                ->whereIn('id', $sampleTypeIds)
                ->select('id', 'name')
                ->get();
        }
    }

    public function updated($name, $value)
    {
        if ($name === 'organization_name') {
            $this->customerId = null;
            $this->contact_name = '';
            $this->title_position = '';
            if ($this->received_from_type === 'Customer') {
                $this->loadDynamicData();
            }
        }

        if ($name === 'contact_name') {
            $this->syncContactTitle($value);
        }
    }

    public function updatedContactName($value)
    {
        $this->syncContactTitle($value);
    }

    protected function syncContactTitle($value)
    {
        if ($this->received_from_type === 'Customer' && $value) {
            $contact = null;
            if (is_numeric($value) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
                $contact = \App\Models\CRM\CustomerContact::find($value);
            } else {
                // Defensive: resolve customerId if it's missing
                if (!$this->customerId && $this->organization_name) {
                    $customer = CRMCustomer::where('name', $this->organization_name)->first();
                    if ($customer) {
                        $this->customerId = $customer->id;
                    }
                }

                if ($this->customerId) {
                    // Try to find by full name if it's a string (backwards compatibility)
                    $contact = \App\Models\CRM\CustomerContact::where('crm_customer_id', $this->customerId)
                        ->where(DB::raw("TRIM(CONCAT_WS(' ', first_name, middle_name, last_name))"), $value)
                        ->first();
                }
            }

            if ($contact) {
                $this->title_position = $contact->job_occupation;
                // If we matched a string name to an ID, update the model to use the ID
                if (!is_numeric($value) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
                    $this->contact_name = $contact->id;
                }
            }
        } else {
            $this->title_position = '';
        }
    }

    public function updatedReceivedFromType($value)
    {
        if ($value !== 'Customer') {
            $this->is_lab_related = false;
            $this->test_item = '';
            $this->report_serial_no = '';
            $this->contacts = [];
            $this->samples = [];
        } else {
            $this->loadDynamicData();
        }
    }

    public function updatedIsLabRelated($value)
    {
        if (!$value) {
            $this->test_item = '';
            $this->report_serial_no = '';
        }
    }

    public function updatedTestItem($value)
    {
        if ($this->received_from_type === 'Customer' && $value) {
            $this->serial_nos = SampleHeader::where('crm_customer_id', $this->customerId)
                ->where('sample_type_id', $value)
                ->select('batch_code', 'id')
                ->distinct()
                ->orderBy('id', 'desc')
                ->pluck('batch_code', 'batch_code');
        }
    }

    #[On('add-complaint')]
    public function resetForm()
    {
        $this->reset(['complaintId', 'description', 'priority', 'type', 'received_from', 'received_from_type', 'is_lab_related', 'mode_of_delivery', 'nature_of_complaint', 'test_item', 'report_serial_no', 'title_position', 'organization_name', 'contact_name', 'contacts', 'samples', 'serial_nos']);
        $this->date = date('Y-m-d');
        $this->received_from_type = 'Customer';
        $this->mode_of_delivery = [];
        $this->resetValidation();
    }

    #[On('edit-complaint')]
    public function loadComplaint($complaintId)
    {
        $complaint = Complaint::find($complaintId);
        if ($complaint) {
            $this->complaintId = $complaint->id;
            $this->customerId = $complaint->client_id;
            $this->description = $complaint->description;
            $this->priority = $complaint->priority;
            $this->type = $complaint->type;
            $this->received_from = $complaint->received_from;
            $this->date = $complaint->date;
            
            // New intake fields
            $this->received_from_type = $complaint->received_from_type ?? 'Customer';
            $this->is_lab_related = $complaint->is_lab_related;
            $this->mode_of_delivery = $complaint->mode_of_delivery ? explode(',', $complaint->mode_of_delivery) : [];
            $this->nature_of_complaint = $complaint->nature_of_complaint;
            $this->test_item = $complaint->test_item;
            $this->report_serial_no = $complaint->report_serial_no;
            $this->title_position = $complaint->title_position;
            $this->organization_name = $complaint->organization_name ?? $complaint->received_from;
            $this->contact_name = $complaint->contact_name;

            if ($this->received_from_type === 'Customer') {
                $this->loadDynamicData();
                
                // Try to resolve contact ID from name
                $contact = \App\Models\CRM\CustomerContact::where('crm_customer_id', $this->customerId)
                    ->where(DB::raw("TRIM(CONCAT_WS(' ', first_name, middle_name, last_name))"), $this->contact_name)
                    ->first();
                if ($contact) {
                    $this->contact_name = $contact->id;
                }

                // Load serial nos for existing test item
                if ($this->test_item) {
                     // We need to resolve SampleType ID from name if test_item stores name
                     $sampleType = SampleType::where('name', $this->test_item)->first();
                     if ($sampleType) {
                        $this->test_item = $sampleType->id; // Set to ID for dropdown compatibility
                        $this->updatedTestItem($this->test_item);
                     }
                }
            }
        }
    }

    protected function rules()
    {
        return [
            'description' => 'required|string',
            'priority' => 'required|string',
            'type' => 'required|string',
            'date' => 'required|date',
            'received_from_type' => 'required|string',
            'is_lab_related' => 'boolean',
            'mode_of_delivery' => 'required|array',
            'nature_of_complaint' => 'nullable|string',
            'test_item' => ($this->is_lab_related ? 'required' : 'nullable'),
            'report_serial_no' => ($this->is_lab_related ? 'required' : 'nullable') . '|string',
            'title_position' => 'nullable|string',
            'organization_name' => 'required|string',
            'contact_name' => 'required',
        ];
    }

    public function save()
    {
        $this->validate();

        if ($this->complaintId) {
            $this->checkPermission('CRM.components.Complaints.Edit');
            $complaint = Complaint::find($this->complaintId);
            $complaint->edited_by = auth()->user()->name;
        } else {
            $this->checkPermission('CRM.components.Open Complaint.Add');
            $complaint = new Complaint();
            
            // Generate complaint ID
            $complaint_total = Complaint::count() + 1;
            $complaint->complaint_id = "COMP/" . $complaint_total;
            
            $complaint->registered_by = auth()->user()->name;
            $complaint->complaint_workflow = 1;
        }

        $complaint->description = $this->description;
        $complaint->priority = $this->priority;
        $complaint->type = $this->type;
        $complaint->received_from = $this->organization_name;
        $complaint->date = $this->date;
        
        $complaint->received_from_type = $this->received_from_type;
        $complaint->is_lab_related = $this->received_from_type === 'Customer' ? $this->is_lab_related : false;
        $complaint->mode_of_delivery = is_array($this->mode_of_delivery) ? implode(',', $this->mode_of_delivery) : $this->mode_of_delivery;
        $complaint->nature_of_complaint = $this->nature_of_complaint;
        
        // Handle Test Item conversion from ID to Name for storage
        if ($this->received_from_type === 'Customer' && (is_numeric($this->test_item) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->test_item))) {
            $sampleType = SampleType::find($this->test_item);
            $complaint->test_item = $sampleType ? $sampleType->name : $this->test_item;
        } else {
            $complaint->test_item = $this->test_item;
        }
        
        $complaint->report_serial_no = $this->report_serial_no;
        $complaint->title_position = $this->title_position;
        $complaint->organization_name = $this->organization_name;
        
        // Handle Contact Name conversion from ID to Full Name for storage
        if ($this->received_from_type === 'Customer' && (is_numeric($this->contact_name) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->contact_name))) {
            $contact = \App\Models\CRM\CustomerContact::find($this->contact_name);
            $complaint->contact_name = $contact ? ($contact->first_name . ' ' . ($contact->middle_name ? $contact->middle_name . ' ' : '') . $contact->last_name) : $this->contact_name;
        } else {
            $complaint->contact_name = $this->contact_name;
        }
        
        if ($this->customerId) {
            $complaint->client_id = $this->customerId;
        } else {
             $customer = CRMCustomer::where('name', $this->organization_name)->first();
             if ($customer) {
                $complaint->client_id = $customer->id;
             }
        }

        if ($this->feedback_id) {
            $complaint->feedback_id = $this->feedback_id;
            $complaint->is_feedback_related = true;
            $complaint->origin = 'Customer Feedback Survey';
        }

        $complaint->save();

        // Create chain of custody entry
        $chain_custody = new Chain_of_Custody_Complaint();
        $chain_custody->complaint_id = $complaint->id;
        $chain_custody->action = $this->complaintId ? "Edit complaint" : "Create complaint";
        $chain_custody->action_taker_id = auth()->user()->id;
        $chain_custody->workflow_stage = getComplaintWorkflow()[$complaint->complaint_workflow] ?? $complaint->complaint_workflow;
        $chain_custody->save();

        // Send email notifications
        $config = SystemConfigurationsType::where('configuration_type', 'Personnel to Recieve Feedback and Complaint Notification')->first();
        if ($config && !$this->complaintId) {
            $config_users = SystemConfiguration::where('configuration_type_id', $config->id)->get();
            $company = getCompanyDetails();
            foreach($config_users as $user){
                $subject = '['.$company['name'].'] Complaint Notification - '.$complaint->complaint_id;
                $body = 'Hi '.$user->key.', <br> We hereby inform you that there is a complaint of ID <b>'.$complaint->complaint_id.'</b> that needs your attention.<br>Kindly review it.<br>Regards '.$company['name'];
                notify_user($body, $user->value, $subject);
            }
        }

        $this->showSuccess($this->complaintId ? 'Complaint edited successfully!' : 'Complaint added successfully!');
        $this->dispatch('complaint-saved');
        
        // If loaded as a standalone page route, redirect back to the Open Complaint list
        if ($this->isStandalone) {
             return redirect()->route('complaint-workflow', ['stage' => 'Open Complaint']);
        }
        
        $this->close();
    }

    public function close()
    {
        if ($this->isStandalone) {
            // If the form was opened from a feedback record, return to the feedback list
            if ($this->feedback_id) {
                return redirect()->route('feedback-home');
            }
            return redirect()->route('complaint-workflow', ['stage' => 'Open Complaint']);
        }

        $this->dispatch('complaint-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.complaint.complaint-form')
            ->extends('layouts.crm.layout.app', ['dataTable' => false, 'select2' => true])
            ->section('content2');
    }
}

