<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Models\CRM\CustomerContact;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;
use Livewire\WithPagination;

class CustomerContactsTab extends BaseCrmComponent
{
    use WithPagination;

    public $customer;
    public $search = '';
    public $perPage = 10;
    public $showForm = false;
    public $editingContact = null;

    protected $paginationTheme = 'bootstrap';

    public function mount($customer)
    {
        $this->customer = $customer;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function getContactsProperty()
    {
        return CustomerContact::where('crm_customer_id', $this->customer->id)
            ->where(function($query) {
                $query->where('first_name', 'like', '%'.$this->search.'%')
                      ->orWhere('last_name', 'like', '%'.$this->search.'%')
                      ->orWhere('email', 'like', '%'.$this->search.'%')
                      ->orWhere('telephone', 'like', '%'.$this->search.'%');
            })
            ->paginate($this->perPage);
    }

    public function openContactForm($contactId = null)
    {
        $this->editingContact = $contactId ? CustomerContact::find($contactId) : null;
        $this->showForm = true;
    }

    #[On('contact-saved')]
    #[On('contact-deleted')]
    #[On('contact-form-closed')]
    public function refreshContacts()
    {
        $this->resetPage();
        $this->showForm = false;
        $this->editingContact = null;
    }

    public function deleteContact($contactId)
    {
        $contact = CustomerContact::find($contactId);
        
        if ($contact && $contact->crm_customer_id == $this->customer->id) {
            $contact->delete();
            $this->showSuccess('Contact deleted successfully.');
            $this->resetPage();
        } else {
            $this->showError('Contact not found or unauthorized.');
        }
    }

    public function exportToExcel()
    {
        $this->checkPermission('CRM.permission');
        return (new \App\Exports\CRM\CustomerRegistryTabExport($this->customer->id, 'contacts', $this->search))
            ->download('customer_contacts_' . $this->customer->id . '_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-contacts-tab', [
            'contacts' => $this->contacts,
        ]);
    }
}
