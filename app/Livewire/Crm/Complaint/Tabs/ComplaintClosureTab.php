<?php

namespace App\Livewire\Crm\Complaint\Tabs;

use App\Models\CRM\Complaintsresolutions;
use App\Models\CRM\Complaint;
use App\Models\CRM\CustomerContact;
use App\Livewire\Crm\BaseCrmComponent;

class ComplaintClosureTab extends BaseCrmComponent
{
    public $complaintId;
    public $complaint;
    public $resolution;
    public $closureContacts = [];

    // Closure settings
    public bool $send_to_customer = false;
    public array $selectedContactIds = [];
    public string $client_remarks = '';

    public function mount($complaintId)
    {
        $this->initialize();
        $this->complaintId = $complaintId;
        $this->complaint = Complaint::findOrFail($complaintId);
        $this->resolution = Complaintsresolutions::where('complaint_id', $this->complaintId)->first();

        if ($this->resolution) {
            $this->send_to_customer = (bool) $this->resolution->send_to_customer;
            $this->client_remarks = $this->resolution->client_remarks ?? '';
        }

        // Load eligible contacts
        if ($this->complaint->client_id) {
            $this->closureContacts = CustomerContact::where('crm_customer_id', $this->complaint->client_id)
                ->where('receive_report', 1)
                ->where('active', 1)
                ->orderBy('first_name', 'asc')
                ->get()
                ->toArray();
        }
    }

    /**
     * Save send_to_customer preference and client_remarks to the resolution record.
     * Called automatically via Livewire wire:model updates or an explicit Save Settings button.
     */
    public function saveSettings()
    {
        $this->checkPermission('crm.components.complaint pending closure.edit');

        if (!$this->resolution) {
            $this->showError('No investigation record found. Please complete the investigation first.');
            return;
        }

        $this->resetErrorBag(); // Clear previous errors to prevent duplication

        $this->resolution->send_to_customer = $this->send_to_customer;
        $this->resolution->client_remarks = $this->client_remarks;
        $this->resolution->save();

        if ($this->getErrorBag()->isEmpty()) {
            $this->showSuccess('Closure settings saved.');
        }
    }

    /**
     * Initiates the Close Complaint modal action via the workflow tab.
     * The actual state transition is handled by ComplaintWorkflowTab::closeComplaint().
     */
    public function initiateClose()
    {
        // First try to save settings, which will also handle validation
        $this->saveSettings();
        
        // If saveSettings added errors (e.g. empty recipients), stop here
        // Pass selected contact IDs to the workflow tab modal handler
        $this->dispatch('sync-closure-contacts', contactIds: $this->selectedContactIds);
        $this->dispatch('direct-close-complaint');
    }

    public function render()
    {
        return view('livewire.crm.complaint.tabs.complaint-closure-tab');
    }
}
