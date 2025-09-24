<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCompanyUnit;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ContactsManager extends Component
{
    // Customer Data
    public $customerId;
    public $customer;
    
    // Contact Management
    public $editingContact = null;
    public $showContactModal = false;
    
    // Contact Form
    public $contactForm = [
        'title_id' => null,
        'first_name' => '',
        'middle_name' => '',
        'last_name' => '',
        'job_occupation' => '',
        'unit_name' => [],
        'email' => '',
        'telephone' => '',
        'mobile' => '',
        'receive_price_list' => false,
        'receive_invoice' => false,
        'receive_report' => false,
        'active' => true,
        'can_login' => false,
        'main_password' => '',
        'confirm_password' => ''
    ];

    // Supporting Data
    public $units = [];
    public $titles = [];

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'contactForm.title_id' => 'required|exists:module_pre_configs,id',
        'contactForm.first_name' => 'required|string|max:255',
        'contactForm.email' => 'required|email|max:255',
        'contactForm.telephone' => 'required|string|max:50',
        'contactForm.unit_name' => 'required|array|min:1',
        'contactForm.main_password' => 'required_if:contactForm.can_login,true|min:8',
        'contactForm.confirm_password' => 'required_if:contactForm.can_login,true|same:contactForm.main_password',
    ];

    protected $messages = [
        'contactForm.title_id.required' => 'Title selection is required.',
        'contactForm.first_name.required' => 'First name is required.',
        'contactForm.email.required' => 'Email address is required.',
        'contactForm.email.email' => 'Please enter a valid email address.',
        'contactForm.telephone.required' => 'Telephone number is required.',
        'contactForm.unit_name.required' => 'At least one unit must be selected.',
        'contactForm.main_password.required_if' => 'Password is required when creating user account.',
        'contactForm.confirm_password.required_if' => 'Password confirmation is required when creating user account.',
        'contactForm.confirm_password.same' => 'Password confirmation does not match.',
    ];

    public function mount($customerId)
    {
        $this->customerId = $customerId;
        $this->customer = \App\Models\CRM\CRMCustomer::findOrFail($customerId);
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->units = CRMCompanyUnit::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $this->titles = getModulePreconfig("Designation", "Personnel-Management");
    }

    public function getContactsProperty()
    {
        return CustomerContact::where('crm_customer_id', $this->customerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->get();
    }

    public function showCreateContactModal()
    {
        $this->resetContactForm();
        $this->showContactModal = true;
    }

    public function showEditContactModal($id)
    {
        $contact = CustomerContact::findOrFail($id);
        
        $this->contactForm = [
            'title_id' => $contact->title_id,
            'first_name' => $contact->first_name,
            'middle_name' => $contact->middle_name ?? '',
            'last_name' => $contact->last_name ?? '',
            'job_occupation' => $contact->job_occupation ?? '',
            'unit_name' => explode(',', $contact->unit_name ?? ''),
            'email' => $contact->email,
            'telephone' => $contact->telephone,
            'mobile' => $contact->mobile ?? '',
            'receive_price_list' => $contact->receive_price_list == 1,
            'receive_invoice' => $contact->receive_invoice == 1,
            'receive_report' => $contact->receive_report == 1,
            'active' => $contact->active == 1,
            'can_login' => $contact->can_login == 1,
            'main_password' => '',
            'confirm_password' => ''
        ];
        
        $this->editingContact = $contact;
        $this->showContactModal = true;
    }

    public function saveContact()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            if ($this->editingContact) {
                // Update existing contact
                $contact = $this->editingContact;
            } else {
                // Create new contact
                $contact = new CustomerContact();
                $contact->crm_customer_id = $this->customerId;
            }

            $contact->title_id = $this->contactForm['title_id'];
            $contact->first_name = $this->contactForm['first_name'];
            $contact->middle_name = $this->contactForm['middle_name'];
            $contact->last_name = $this->contactForm['last_name'];
            $contact->job_occupation = $this->contactForm['job_occupation'];
            $contact->unit_name = implode(',', $this->contactForm['unit_name']);
            $contact->email = $this->contactForm['email'];
            $contact->telephone = $this->contactForm['telephone'];
            $contact->mobile = $this->contactForm['mobile'];
            $contact->receive_price_list = $this->contactForm['receive_price_list'] ? 1 : 0;
            $contact->receive_invoice = $this->contactForm['receive_invoice'] ? 1 : 0;
            $contact->receive_report = $this->contactForm['receive_report'] ? 1 : 0;
            $contact->active = $this->contactForm['active'] ? 1 : 0;
            $contact->can_login = $this->contactForm['can_login'] ? 1 : 0;

            $contact->save();

            // Handle user creation/update
            if ($this->contactForm['can_login']) {
                $this->createOrUpdateUser($contact);
            }

            DB::commit();
            
            $this->closeContactModal();
            $this->message = $this->editingContact ? 'Contact updated successfully!' : 'Contact created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    protected function createOrUpdateUser($contact)
    {
        $user = User::where('email', $contact->email)->first();
        
        if (!$user) {
            $user = new User();
            $user->email = $contact->email;
        }

        $user->name = trim($contact->first_name . ' ' . $contact->middle_name . ' ' . $contact->last_name);
        $user->password = Hash::make($this->contactForm['main_password']);
        $user->company_id = getUserCompany();
        $user->is_client = 1;
        $user->client_id = $this->customerId;
        $user->active = 1;
        $user->save();
    }

    public function deleteContact($id)
    {
        try {
            DB::beginTransaction();

            $contact = CustomerContact::findOrFail($id);
            
            // Soft delete - set active to 0
            $contact->active = 0;
            $contact->save();

            // Update related user if exists
            $user = User::where('email', $contact->email)->first();
            if ($user) {
                $user->active = 0;
                $user->save();
            }

            DB::commit();
            
            $this->message = 'Contact deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeContactModal()
    {
        $this->showContactModal = false;
        $this->resetContactForm();
    }

    public function resetContactForm()
    {
        $this->contactForm = [
            'title_id' => null,
            'first_name' => '',
            'middle_name' => '',
            'last_name' => '',
            'job_occupation' => '',
            'unit_name' => [],
            'email' => '',
            'telephone' => '',
            'mobile' => '',
            'receive_price_list' => false,
            'receive_invoice' => false,
            'receive_report' => false,
            'active' => true,
            'can_login' => false,
            'main_password' => '',
            'confirm_password' => ''
        ];
        $this->editingContact = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.crm.contacts-manager');
    }
}

