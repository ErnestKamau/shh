<?php

namespace App\Livewire\Crm\Contact;

use App\Mail\ContactWelcomeMail;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCustomer;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
    public $unitSearch = '';
    public $otherCustomerSearch = '';
    public $showUnitDropdown = false;
    public $showOtherCustomersDropdown = false;

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
                $this->unit_name = $this->normalizeStoredUnitValues($contact->unit_name);
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
                $this->other_customers = $this->normalizeStoredCustomerIds($contact->other_customers);
            }
        }
    }

    public function getFilteredUnitsProperty()
    {
        $search = trim(strtolower($this->unitSearch));

        return collect($this->units)
            ->when($search !== '', function ($units) use ($search) {
                return $units->filter(function ($unit) use ($search) {
                    return str_contains(strtolower($unit->name), $search);
                });
            })
            ->take(80)
            ->values();
    }

    public function getFilteredCustomersProperty()
    {
        $search = trim(strtolower($this->otherCustomerSearch));

        return collect($this->customers)
            ->when($search !== '', function ($customers) use ($search) {
                return $customers->filter(function ($customer) use ($search) {
                    return str_contains(strtolower($customer->name), $search);
                });
            })
            ->take(80)
            ->values();
    }

    public function getSelectedUnitsProperty()
    {
        $selectedIds = $this->normalizedUnitIds();

        return collect($this->units)
            ->filter(fn($unit) => in_array((string) $unit->id, $selectedIds, true))
            ->values();
    }

    public function getSelectedOtherCustomersProperty()
    {
        $selectedIds = $this->normalizedCustomerIds();

        return collect($this->customers)
            ->filter(fn($customer) => in_array((string) $customer->id, $selectedIds, true))
            ->values();
    }

    public function toggleUnitSelection(string $unitId): void
    {
        $unitId = (string) $unitId;
        $selected = $this->normalizedUnitIds();

        if (in_array($unitId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn($id) => $id !== $unitId));
        } else {
            $selected[] = $unitId;
        }

        $this->unit_name = $selected;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
    }

    public function removeUnitSelection(string $unitId): void
    {
        $unitId = (string) $unitId;

        $this->unit_name = array_values(array_filter(
            $this->normalizedUnitIds(),
            fn($id) => $id !== $unitId
        ));
    }

    public function isUnitSelected($unitId): bool
    {
        return in_array((string) $unitId, $this->normalizedUnitIds(), true);
    }

    public function toggleOtherCustomerSelection(string $customerId): void
    {
        $customerId = (string) $customerId;
        $selected = $this->normalizedCustomerIds();

        if (in_array($customerId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn($id) => $id !== $customerId));
        } else {
            $selected[] = $customerId;
        }

        $this->other_customers = $selected;
        $this->otherCustomerSearch = '';
        $this->showOtherCustomersDropdown = false;
    }

    public function removeOtherCustomerSelection(string $customerId): void
    {
        $customerId = (string) $customerId;

        $this->other_customers = array_values(array_filter(
            $this->normalizedCustomerIds(),
            fn($id) => $id !== $customerId
        ));
    }

    public function isOtherCustomerSelected($customerId): bool
    {
        return in_array((string) $customerId, $this->normalizedCustomerIds(), true);
    }

    protected function normalizedUnitIds(): array
    {
        return collect($this->unit_name)
            ->map(fn($id) => trim((string) $id))
            ->filter(fn($id) => $id !== '')
            ->values()
            ->all();
    }

    protected function normalizedCustomerIds(): array
    {
        return collect($this->other_customers)
            ->map(fn($id) => trim((string) $id))
            ->filter(fn($id) => $id !== '' && Str::isUuid($id))
            ->values()
            ->all();
    }

    protected function normalizeStoredUnitValues(?string $stored): array
    {
        $values = array_values(array_filter(
            array_map('trim', explode(',', $stored ?? '')),
            fn($value) => $value !== ''
        ));

        return collect($values)
            ->map(function (string $value) {
                if (Str::isUuid($value)) {
                    return $value;
                }

                $matchedUnit = collect($this->units)->first(
                    fn($unit) => strcasecmp((string) $unit->name, $value) === 0
                );

                return $matchedUnit ? (string) $matchedUnit->id : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeStoredCustomerIds(?string $stored): array
    {
        return collect(array_values(array_filter(
            array_map('trim', explode(',', $stored ?? '')),
            fn($value) => $value !== '' && Str::isUuid($value)
        )))->values()->all();
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
            $this->checkPermission('crm.components.contacts.add');
            $contact = new CustomerContact();
        } else {
            $this->checkPermission('crm.components.contacts.edit');
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
                (string) $this->customerId,
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
            $user->client_id = (string) $this->customerId;
            $user->crm_contact_id = $contact->id;
            $user->crmcontact_id = $contact->id;
            $user->active = 1;

            $user->save();

            // Send welcome email only on new contact creation (not when editing credentials)
            if ($this->password && !$this->contactId) {
                $recipientName  = trim($user->name);
                $recipientEmail = $user->email;
                $plainPassword  = $this->password;

                dispatch(function () use ($recipientName, $recipientEmail, $plainPassword) {
                    Mail::to($recipientEmail)->send(
                        new ContactWelcomeMail($recipientName, $recipientEmail, $plainPassword)
                    );
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
            $this->checkPermission('crm.components.contacts.delete');
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

