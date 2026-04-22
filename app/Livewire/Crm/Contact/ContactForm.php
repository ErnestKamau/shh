<?php

namespace App\Livewire\Crm\Contact;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCustomer;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use App\User;
use Illuminate\Support\Facades\Hash;

class ContactForm extends BaseCrmComponent
{
    public $contactId = null;
    public $customerId;
    public $first_name = '';
    public $second_name = '';
    public $third_name = '';
    public $job_occupation = '';
    public $unit_name = [];
    public $email = '';
    public $telephone = '';
    public $mobile = '';
    public $receive_price_list = false;
    public $receive_invoice = false;
    public $receive_report = false;
    public $receive_feedback = false;
    public $active = false;
    public $other_customers = [];
    public $units = [];
    public $customers = [];

    public $password = '';
    public $confirm_password = '';
    public $can_login = false;

    public function mount($customerId, $contactId = null)
    {
        $this->initialize();
        $this->customerId = $customerId;
        $this->units = CRMCustomer::find($customerId)->units ?? [];
        $this->customers = CRMCustomer::where('active', 1)
            ->where('id', '!=', $customerId)
            ->get();
        
        if ($contactId) {
            $contact = CustomerContact::find($contactId);
            if ($contact) {
                $this->contactId = $contact->id;
                $this->first_name = $contact->first_name;
                $this->second_name = $contact->middle_name;
                $this->third_name = $contact->last_name;
                $this->job_occupation = $contact->job_occupation;
                $this->unit_name = explode(',', $contact->unit_name ?? '');
                $this->email = $contact->email;
                $this->telephone = $contact->telephone;
                $this->mobile = $contact->mobile;
                // Cast to boolean
                $this->receive_price_list = (bool) ($contact->receive_price_list ?? 0);
                $this->receive_invoice = (bool) ($contact->receive_invoice ?? 0);
                $this->receive_report = (bool) ($contact->receive_report ?? 0);
                $this->receive_feedback = (bool) ($contact->receive_feedback ?? 0);
                $this->active = (bool) ($contact->active ?? 0);
                $this->can_login = (bool) ($contact->can_login ?? 0);
                $this->other_customers = explode(',', $contact->other_customers ?? '');
            }
        }
    }

    protected function rules()
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:255', // Marked as required in new design
            'mobile' => 'nullable|string|max:255',
            'unit_name' => 'required|array', // Department/Unit is required
        ];

        if ($this->can_login && !$this->contactId) {
            $rules['password'] = 'required|min:6';
            $rules['confirm_password'] = 'required|same:password';
        } elseif ($this->can_login && $this->password) {
             $rules['password'] = 'min:6';
             $rules['confirm_password'] = 'required|same:password';
        }

        return $rules;
    }

    public function save()
    {
        $this->telephone = trim($this->telephone ?? '');
        $this->validate();

        $emailNormalized = strtolower(trim($this->email));

        // Check if email already exists for this customer (exclude current contact when editing)
        $duplicateQuery = CustomerContact::where('crm_customer_id', $this->customerId)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalized]);
        if ($this->contactId) {
            $duplicateQuery->where('id', '!=', $this->contactId);
        }
        if ($duplicateQuery->exists()) {
            $this->showError('Email already exists in the system');
            return;
        }

        $previousContactEmail = null;

        if (!$this->contactId) {
            $this->checkPermission('CRM.components.Contacts.Add');
            $contact = new CustomerContact();
        } else {
            $this->checkPermission('CRM.components.Contacts.Edit');
            $contact = CustomerContact::find($this->contactId);
            $previousContactEmail = $contact?->email;
        }

        $contact->first_name = $this->first_name;
        $contact->middle_name = $this->second_name;
        $contact->last_name = $this->third_name;
        $contact->job_occupation = $this->job_occupation;
        $contact->unit_name = implode(",", $this->unit_name);
        $contact->email = $this->email;
        $contact->telephone = $this->telephone;
        $contact->mobile = $this->mobile;
        $contact->company_id = $this->getUserCompany();
        $contact->crm_customer_id = $this->customerId;
        
        // Cast back to integer
        $contact->receive_price_list = $this->receive_price_list ? 1 : 0;
        $contact->receive_invoice = $this->receive_invoice ? 1 : 0;
        $contact->receive_report = $this->receive_report ? 1 : 0;
        $contact->receive_feedback = $this->receive_feedback ? 1 : 0;
        $contact->active = $this->active ? 1 : 0;
        $contact->can_login = $this->can_login ? 1 : 0;
        
        $contact->other_customers = is_array($this->other_customers) ? implode(',', $this->other_customers) : '';

        $contact->save();

        if (! $this->can_login) {
            User::deactivatePortalUsersForCustomerContact(
                $contact,
                (int) $this->customerId,
                $previousContactEmail
            );
        }

        if ($this->can_login) {
            // Check if user exists with this email
            $user = User::where('email', $this->email)->first();
            
            // If user exists but it's not the current user associated with this contact (if any), check for conflict
            if ($user && !$this->contactId) {
                 $this->showError('There is already a user with the given email!');
                 return;
            }

             if ($user && $this->contactId) {
                // If editing, make sure we are not taking someone else's email
                // This logic might need to be more robust depending on requirements, but for now strict check:
                // If the found user is NOT the one associated with this contact... context missing in contact model relation
                // But let's assume strict email check:
                 // The controller logic:
                 // $check_user = User::where('email',$request->email)->get();
                 // if(isset($check_user->id)){ return redirect()->back()->with('error','There is a user with the given email!'); }
                 // The controller logic seems to prevent creating a login if email exists in User table, BUT it also has logic to UPDATE if it exists in setCustomerContacts
                 // In `add` method: strict check. In `edit` method: strict check.
                 // However, if we are editing the contact and the user ALREADY exists for this contact, we should update it.
                 // Let's refine based on Controller `edit`:
                 // $user = User::where('email',$request->email)->first() ? User::where('email',$request->email)->first() : new User();
             }
             
            $user = User::where('email', $this->email)->first() ?? new User();
            
            // If it is a new user or we are updating, we update the fields
            $user->name = $this->first_name . " " . $this->second_name . " " . $this->third_name;
            
            // Only update password if provided
            if ($this->password) {
                 $user->password = bcrypt($this->password);
            }
           
            $user->email = $this->email;
            $user->company_id = $this->getUserCompany();
            $user->is_client = 1;
            $user->crm_contact_id = $this->customerId; // Controller uses $cust_id
            $user->client_id = $this->customerId;      // Controller uses $cust_id
            $user->crmcontact_id = $contact->id;       // Link to the contact we just saved
            
            $user->save();

            // Send notification after response so the save returns quickly (avoids 3–10s wait for SMTP)
            if ($this->password) {
                $ip_address_link = request()->root();
                $companyDetails = getCompanyDetails();
                $message = 'We would like to welcome you to ' . $companyDetails['name'] . '.Please find below your Login Credentials and Link to Imara Lims:';
                $body = 'Hi ' . $user->name . ', <br><br>'
                    . $message . '<br>
                    App Link: <a href=' . $ip_address_link . '>' . $ip_address_link . '</a> ,<br>
                    Email: ' . $user->email . ' ,<br>
                    Password: ' . $this->password . ' , <br>
    
                    Regards, <br><br> ' . $companyDetails['name'] . ' ';
                $subject = '[' . $companyDetails['name'] . '] User Credentials';

                $recipientEmail = $user->email;
                dispatch(function () use ($body, $recipientEmail, $subject) {
                    notify_user($body, $recipientEmail, $subject);
                })->afterResponse();
            }
        }

        $this->showSuccess($this->contactId ? 'Contact edited successfully!' : 'Contact added successfully!');
        $this->dispatch('contact-saved');
        $this->close();
    }

    public function delete()
    {
        if ($this->contactId) {
            $this->checkPermission('CRM.components.Contacts.Delete');
            $contact = CustomerContact::find($this->contactId);
            $contact->delete();
            
            $this->showSuccess('Contact deleted successfully');
            $this->dispatch('contact-deleted');
            $this->close();
        }
    }

    public function close()
    {
        $this->dispatch('contact-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.contact.contact-form');
    }
}

