<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback; // specific import
use App\Models\CRM\FeedbackRequest;  // specific import
use App\Livewire\Crm\BaseCrmComponent;
use Illuminate\Support\Facades\DB;   // Added for transaction
use Livewire\Attributes\On;
use App\Mail\FeedbackCampaignMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Company;

class SendFeedbackCampaign extends BaseCrmComponent
{

    public $selectedCustomers = []; // Array of customer IDs
    public $customerSearch = '';
    public $showCustomerDropdown = false;
    public $customers = [];
    public $recipients = []; // Array of contact objects
    public $selectedRecipients = []; // Array of checked contact IDs

    // PROGRESS TRACKING
    public $sending = false;
    public $batchFeedbackIds = [];
    public $feedbackProgress = []; // Stores ['id', 'name', 'email', 'status']
    public $progressStats = [
        'pending' => 0,
        'processing' => 0,
        'sent' => 0,
        'failed' => 0,
        'total' => 0
    ];
    public $completed = false;

    public function mount()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Exception $e) {
            // Ignore migration issues if any
        }
    }

    #[On('open-send-campaign-modal')]
    public function openModal()
    {
        $this->reset(['selectedCustomers', 'customerSearch', 'showCustomerDropdown', 'recipients', 'selectedRecipients', 'sending', 'batchFeedbackIds', 'completed', 'progressStats', 'feedbackProgress']);
        
        // Small delay to ensure modal DOM is ready before dispatching event
        $this->dispatch('show-campaign-modal');
    }

    public function getSelectedCustomersListProperty()
    {
        if (empty($this->selectedCustomers)) {
            return collect();
        }

        return CRMCustomer::whereIn('id', $this->selectedCustomers)->orderBy('name')->get();
    }

    public function getFilteredCustomerOptionsProperty()
    {
        $search = strtolower(trim($this->customerSearch));
        $selectedIds = $this->selectedCustomers;

        $query = CRMCustomer::where('active', 1);

        if (!empty($selectedIds)) {
            $query->whereNotIn('id', $selectedIds);
        }

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        return $query->orderBy('name')->take(50)->get();
    }

    public function toggleCustomer($customerId)
    {
        $customerId = (string) $customerId;

        if (in_array($customerId, $this->selectedCustomers, true)) {
            $this->selectedCustomers = array_values(array_filter(
                $this->selectedCustomers,
                fn($id) => (string) $id !== $customerId
            ));
        } else {
            $this->selectedCustomers[] = $customerId;
            $this->selectedCustomers = array_values(array_unique(array_map('strval', $this->selectedCustomers)));
        }

        $this->customerSearch = '';
        $this->showCustomerDropdown = true;
        $this->updateRecipients();
    }

    public function removeCustomer($customerId)
    {
        $customerId = (string) $customerId;
        $this->selectedCustomers = array_values(array_filter(
            $this->selectedCustomers,
            fn($id) => (string) $id !== $customerId
        ));
        $this->updateRecipients();
    }

    public function clearCustomers()
    {
        $this->selectedCustomers = [];
        $this->customerSearch = '';
        $this->showCustomerDropdown = false;
        $this->updateRecipients();
    }

    // ... (keep existing methods) ...

    public function send()
    {
        $this->validate([
            'selectedCustomers' => 'required|array|min:1',
            'selectedRecipients' => 'required|array|min:1', 
        ], [
            'selectedRecipients.required' => 'Please select at least one recipient to send feedback to.'
        ]);

        if (empty($this->recipients)) {
            $this->addError('recipients', 'No opted-in contacts found for the selected customers.');
            return;
        }
        
        // Filter recipients based on selection
        $contactsToSend = $this->recipients->whereIn('id', $this->selectedRecipients);
        
        if ($contactsToSend->isEmpty()) {
             $this->addError('recipients', 'Please select at least one recipient.');
             return;
        }

        $this->sending = false;
        $this->completed = true;

        // 1. Create Pending Records synchronously
        foreach ($contactsToSend as $contact) {
            if ($contact->email) {
                DB::transaction(function () use ($contact) {
                    $freshContact = CustomerContact::find($contact->id);
                    if (!$freshContact || !$freshContact->crm_customer_id) return;

                    // Create Feedback (Pending)
                    $feedback = CustomerFeedback::create([
                        'customer_id'     => $freshContact->crm_customer_id,
                        'contact_id'      => $freshContact->id,
                        'status'          => CustomerFeedback::STATUS_PENDING,
                        'delivery_status' => 'pending', 
                        'is_submitted'    => false,
                        'has_issues'      => false,
                        'consent_contact' => false,
                        'received_from'   => $freshContact->customer->name ?? 'Unknown Customer',
                        'registered_by'   => auth()->user() ? auth()->user()->name : 'System',
                        'date'            => now(), 
                        'feedback'        => 'Pending Feedback Campaign',
                        'user_type'       => 'Customer',
                    ]);

                    if (empty($feedback->code)) {
                        $feedback->code = CustomerFeedback::generateUniqueCode();
                        $feedback->save();
                    }

                    // Create Token Request
                    $token = FeedbackRequest::generateUniqueToken();
                    $expiresAt = now()->addDays(7);
                    
                    $request = FeedbackRequest::create([
                        'company_id'  => $this->getUserCompany(),
                        'customer_id' => $freshContact->crm_customer_id,
                        'contact_id'  => $freshContact->id,
                        'feedback_id' => $feedback->id, 
                        'token'       => $token,
                        'email'       => $freshContact->email,
                        'status'      => FeedbackRequest::STATUS_PENDING,
                        'sent_at'     => now(),
                        'expires_at'  => $expiresAt,
                    ]);

                    // Dispatch Job (Fire and Forget)
                    \App\Jobs\SendFeedbackEmail::dispatch($feedback->id, $request->id, $this->getUserCompany());
                });
            }
        }

        // Tell parent list to refresh in the background
        $this->dispatch('feedback-requests-sent');
    }

    public function updatedSelectedCustomers()
    {
        $this->updateRecipients();
    }


    public function updateRecipients()
    {
        if (empty($this->selectedCustomers)) {
            $this->recipients = [];
            $this->selectedRecipients = [];
            return;
        }

        $this->recipients = CustomerContact::whereIn('crm_customer_id', $this->selectedCustomers)
            ->where('active', 1)
            ->with('customer') 
            ->get();
            
        // Default: Select ALL found recipients
        $this->selectedRecipients = $this->recipients->pluck('id')->map(fn($id) => (string)$id)->toArray();
    }

    public function close()
    {
        $this->dispatch('campaign-modal-closed', ['cleanup' => true]);
    }

    public function render()
    {
        return view('livewire.crm.feedback.send-feedback-campaign');
    }

}