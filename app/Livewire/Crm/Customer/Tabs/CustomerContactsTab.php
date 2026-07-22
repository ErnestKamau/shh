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
    public string $flashMessage = '';
    public string $flashType = 'success';

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

    public function openContactForm(?string $contactId = null): void
    {
        $this->flashMessage = '';
        $this->editingContact = $contactId ? CustomerContact::find($contactId) : null;
        $this->showForm = true;
    }

    public function dismissFlash(): void
    {
        $this->flashMessage = '';
        $this->flashType = 'success';
    }

    #[On('contact-saved')]
    public function onContactSaved(mixed $message = null): void
    {
        $this->showForm = false;
        $this->editingContact = null;
        $this->resetPage();
        $resolved = $this->resolveEventMessage($message, 'Contact saved successfully!');
        $this->flashMessage = $resolved;
        $this->flashType = 'success';
        $this->showSuccess($resolved);
    }

    #[On('contact-deleted')]
    public function onContactDeleted(mixed $message = null): void
    {
        $this->showForm = false;
        $this->editingContact = null;
        $this->resetPage();
        $resolved = $this->resolveEventMessage($message, 'Contact deleted successfully.');
        $this->flashMessage = $resolved;
        $this->flashType = 'success';
        $this->showSuccess($resolved);
    }

    #[On('contact-form-closed')]
    public function onContactFormClosed(): void
    {
        $this->showForm = false;
        $this->editingContact = null;
    }

    private function resolveEventMessage(mixed $message, string $fallback): string
    {
        if (is_string($message) && trim($message) !== '') {
            return $message;
        }

        if (is_array($message)) {
            $nested = $message['message'] ?? $message[0] ?? null;
            if (is_string($nested) && trim($nested) !== '') {
                return $nested;
            }
        }

        return $fallback;
    }

    public function deleteContact(string $contactId): void
    {
        $contact = CustomerContact::find($contactId);
        
        if ($contact && $contact->crm_customer_id == $this->customer->id) {
            $contact->delete();
            $this->flashMessage = 'Contact deleted successfully.';
            $this->flashType = 'success';
            $this->showSuccess('Contact deleted successfully.');
            $this->resetPage();
        } else {
            $this->flashMessage = 'Contact not found or unauthorized.';
            $this->flashType = 'error';
            $this->showError('Contact not found or unauthorized.');
        }
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.permission');
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
