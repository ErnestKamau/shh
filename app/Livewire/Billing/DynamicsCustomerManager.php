<?php

namespace App\Livewire\Billing;

use Livewire\Component;
use Livewire\WithPagination;
use App\ZohoCustomers;
use App\Services\DynamicsCustomerSyncService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class DynamicsCustomerManager extends Component
{
    use WithPagination;

    // Customer Management
    public $editingCustomer = null;
    public $showCustomerModal = false;
    
    // Customer Form
    public $customerForm = [
        'name' => '',
        'email' => '',
        'status' => 'active',
        'currency_id' => '',
        'currency_code' => '',
        'zoho_contact_id' => '',
    ];

    // Search and Filter
    public $search = '';
    public $statusFilter = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // UI State
    public $message = '';
    public $messageType = '';

    // Dynamics sync properties
    public $showSyncModal = false;
    public $isSyncing = false;
    public $syncProgress = '';
    public $syncResult = null;
    
    // Sync to Imara properties
    public $showSyncToImaraModalFlag = false;
    public $isSyncingToImara = false;
    public $syncToImaraProgress = '';
    public $syncToImaraResult = null;
    public $syncToImaraCurrentBatch = 0;
    public $syncToImaraTotalCustomers = 0;
    public $syncToImaraProcessedCount = 0;
    public $syncToImaraCreatedCount = 0;
    public $syncToImaraLinkedCount = 0;

    protected $rules = [
        'customerForm.name' => 'required|string|max:255',
        'customerForm.email' => 'nullable|email|max:100',
        'customerForm.status' => 'required|string|max:255',
        'customerForm.currency_id' => 'required|string|max:255',
        'customerForm.currency_code' => 'required|string|max:255',
        'customerForm.zoho_contact_id' => 'required|string|max:255',
    ];

    protected $messages = [
        'customerForm.name.required' => 'Customer name is required.',
        'customerForm.currency_id.required' => 'Currency ID is required.',
        'customerForm.currency_code.required' => 'Currency code is required.',
        'customerForm.zoho_contact_id.required' => 'Zoho Contact ID is required.',
    ];

    public function getCustomersProperty()
    {
        $query = ZohoCustomers::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('zoho_contact_id', 'like', '%' . $this->search . '%')
                  ->orWhere('currency_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->orderBy('name', 'asc')->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function showCreateCustomerModal()
    {
        $this->resetCustomerForm();
        $this->showCustomerModal = true;
    }

    public function showEditCustomerModal($id)
    {
        $customer = ZohoCustomers::findOrFail($id);
        $this->customerForm = [
            'name' => $customer->name,
            'email' => $customer->email,
            'status' => $customer->status,
            'currency_id' => $customer->currency_id,
            'currency_code' => $customer->currency_code,
            'zoho_contact_id' => $customer->zoho_contact_id,
        ];
        $this->editingCustomer = $id;
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate([
            'customerForm.name' => 'required|string|max:255',
            'customerForm.email' => 'nullable|email|max:100',
            'customerForm.status' => 'required|string|max:255',
            'customerForm.currency_id' => 'required|string|max:255',
            'customerForm.currency_code' => 'required|string|max:255',
            'customerForm.zoho_contact_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('zoho_customers', 'zoho_contact_id')->ignore($this->editingCustomer)
            ],
        ]);

        try {
            DB::beginTransaction();

            if ($this->editingCustomer) {
                $customer = ZohoCustomers::findOrFail($this->editingCustomer);
                $customer->update([
                    'name' => $this->customerForm['name'],
                    'email' => $this->customerForm['email'],
                    'status' => $this->customerForm['status'],
                    'currency_id' => $this->customerForm['currency_id'],
                    'currency_code' => $this->customerForm['currency_code'],
                    'zoho_contact_id' => $this->customerForm['zoho_contact_id'],
                ]);
                $this->message = 'Customer updated successfully!';
            } else {
                ZohoCustomers::create([
                    'name' => $this->customerForm['name'],
                    'email' => $this->customerForm['email'],
                    'status' => $this->customerForm['status'],
                    'currency_id' => $this->customerForm['currency_id'],
                    'currency_code' => $this->customerForm['currency_code'],
                    'zoho_contact_id' => $this->customerForm['zoho_contact_id'],
                ]);
                $this->message = 'Customer created successfully!';
            }

            DB::commit();
            $this->closeCustomerModal();
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function deleteCustomer($id)
    {
        try {
            DB::beginTransaction();

            $customer = ZohoCustomers::findOrFail($id);
            $customer->delete();

            DB::commit();
            $this->message = 'Customer deleted successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerForm();
    }

    public function resetCustomerForm()
    {
        $this->customerForm = [
            'name' => '',
            'email' => '',
            'status' => 'active',
            'currency_id' => '',
            'currency_code' => '',
            'zoho_contact_id' => '',
        ];
        $this->editingCustomer = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Dynamics Sync Methods
    public function showSyncConfirmationModal()
    {
        $this->showSyncModal = true;
        $this->syncResult = null;
        $this->syncProgress = '';
    }

    public function closeSyncModal()
    {
        $this->showSyncModal = false;
        $this->syncResult = null;
        $this->syncProgress = '';
        $this->isSyncing = false;
    }

    public function pullCustomersFromDynamics()
    {
        $this->isSyncing = true;
        $this->syncProgress = 'Connecting to Dynamics 365...';
        
        // Dispatch browser event to update UI
        $this->dispatch('sync-progress-update', progress: $this->syncProgress);

        try {
            $this->syncProgress = 'Fetching customers from Dynamics...';
            $this->dispatch('sync-progress-update', progress: $this->syncProgress);

            $syncService = new DynamicsCustomerSyncService();
            $result = $syncService->syncCustomers();

            $this->syncResult = $result;
            $this->isSyncing = false;

            if ($result['success']) {
                $this->syncProgress = 'Sync completed successfully!';
                $this->message = $result['message'];
                $this->messageType = 'success';
            } else {
                $this->syncProgress = 'Sync failed!';
                $this->message = $result['message'];
                $this->messageType = 'error';
            }

        } catch (\Exception $e) {
            $this->isSyncing = false;
            $this->syncProgress = 'Sync failed!';
            $this->syncResult = [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'new_count' => 0,
                'updated_count' => 0,
                'total_fetched' => 0,
            ];
            $this->message = 'Sync failed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Sync to Imara Methods
    public function showSyncToImaraModal()
    {
        $this->showSyncToImaraModalFlag = true;
        $this->syncToImaraResult = null;
        $this->syncToImaraProgress = '';
    }

    public function closeSyncToImaraModal()
    {
        $this->showSyncToImaraModalFlag = false;
        $this->syncToImaraResult = null;
        $this->syncToImaraProgress = '';
        $this->isSyncingToImara = false;
        $this->syncToImaraCurrentBatch = 0;
        $this->syncToImaraTotalCustomers = 0;
        $this->syncToImaraProcessedCount = 0;
        $this->syncToImaraCreatedCount = 0;
        $this->syncToImaraLinkedCount = 0;
        
        // Clear cache
        Cache::forget('sync_to_imara_unlinked_ids');
    }

    public function syncDynamicsToImara()
    {
        try {
            // Initialize sync
            set_time_limit(300); // 5 minutes
            
            Log::info('Sync to Imara - Initialization started');
            
            $this->isSyncingToImara = true;
            $this->syncToImaraProgress = 'Initializing sync...';
            
            // Get all linked Zoho customer IDs from CRM customers
            $linkedZohoIds = \App\Models\CRM\CRMCustomer::whereNotNull('zoho_customer_id')
                ->get()
                ->pluck('zoho_customer_id')
                ->flatten()
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            
            Log::info('Linked Zoho IDs found', ['count' => count($linkedZohoIds)]);
            
            // Get IDs of unlinked Zoho customers only
            $unlinkedZohoCustomerIds = ZohoCustomers::where('status', 'Active')
                ->whereNotIn('id', $linkedZohoIds)
                ->pluck('id')
                ->toArray();
            
            $totalCount = count($unlinkedZohoCustomerIds);
            
            Log::info('Unlinked Zoho customer IDs found', ['count' => $totalCount]);
            
            if ($totalCount === 0) {
                $this->syncToImaraResult = [
                    'success' => true,
                    'message' => "All Dynamics customers are already synced to Imara.",
                    'total_processed' => 0,
                    'created_count' => 0,
                    'linked_count' => 0,
                ];
                $this->isSyncingToImara = false;
                $this->message = "All Dynamics customers are already synced.";
                $this->messageType = 'success';
                return;
            }
            
            // Store unlinked IDs in cache for batch processing
            Cache::put('sync_to_imara_unlinked_ids', $unlinkedZohoCustomerIds, now()->addHours(1));
            
            $this->syncToImaraTotalCustomers = $totalCount;
            $this->syncToImaraCurrentBatch = 0;
            $this->syncToImaraProcessedCount = 0;
            $this->syncToImaraCreatedCount = 0;
            $this->syncToImaraLinkedCount = 0;
            
            $this->syncToImaraProgress = "Ready to process {$totalCount} customers. Starting...";
            
            // Don't process first batch here - let wire:poll handle all batches
            // This allows the UI to update immediately with the progress screen
            
        } catch (\Exception $e) {
            Log::error('Sync to Imara initialization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->isSyncingToImara = false;
            $this->syncToImaraProgress = 'Sync failed!';
            $this->syncToImaraResult = [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'total_processed' => 0,
                'created_count' => 0,
                'linked_count' => 0,
            ];
            $this->message = 'Sync failed: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }
    
    public function processSyncToImaraBatch()
    {
        // Only process if sync is active
        if (!$this->isSyncingToImara) {
            return;
        }
        
        try {
            $batchSize = 500;
            
            // Get unlinked IDs from cache
            $unlinkedIds = Cache::get('sync_to_imara_unlinked_ids', []);
            
            if (empty($unlinkedIds)) {
                // No more customers to process
                $this->completeSyncToImara();
                return;
            }
            
            // Get batch of IDs to process
            $batchIds = array_slice($unlinkedIds, 0, $batchSize);
            $remainingIds = array_slice($unlinkedIds, $batchSize);
            
            $this->syncToImaraCurrentBatch++;
            
            Log::info("Processing batch {$this->syncToImaraCurrentBatch}", [
                'batch_size' => count($batchIds),
                'remaining' => count($remainingIds),
                'processed_so_far' => $this->syncToImaraProcessedCount
            ]);
            
            // Update progress BEFORE processing
            $this->syncToImaraProgress = "Processing batch {$this->syncToImaraCurrentBatch}...";
            
            // Fetch this batch of customers
            $zohoCustomers = ZohoCustomers::whereIn('id', $batchIds)->get();
            
            DB::beginTransaction();
            
            foreach ($zohoCustomers as $zohoCustomer) {
                try {
                    $this->syncToImaraProcessedCount++;
                    
                    // Check if an Imara customer with the same name exists
                    $existingCrmCustomer = \App\Models\CRM\CRMCustomer::where('name', $zohoCustomer->name)
                        ->first();
                    
                    if ($existingCrmCustomer) {
                        // Link to existing customer
                        $existingCrmCustomer->addZohoCustomerId($zohoCustomer->id);
                        $this->syncToImaraLinkedCount++;
                    } else {
                        // Create new Imara customer from Zoho data
                        $newCrmCustomer = \App\Models\CRM\CRMCustomer::create([
                            'name' => $zohoCustomer->name,
                            'code' => $this->generateCustomerCode($zohoCustomer->name),
                            'email' => $zohoCustomer->email ?? '',
                            'telephone1' => $zohoCustomer->phone_no ?? '',
                            'telephone2' => '',
                            'postal_address' => $zohoCustomer->name,
                            'physical_address' => $zohoCustomer->name,
                            'website' => '',
                            'fax' => '',
                            'vat_no' => '',
                            'country_id' => 110, // Default to Kenya
                            'account_status' => 12, // Default to Pay Upfront
                            'credit_days' => 0,
                            'active' => 1,
                            'lpos_required' => 0,
                            'company_id' => 1,
                            'currency_id' => $zohoCustomer->currency_id,
                            'zoho_customer_id' => [$zohoCustomer->id],
                        ]);
                        $this->syncToImaraCreatedCount++;
                    }
                    
                } catch (\Exception $e) {
                    Log::error("Failed to sync customer in batch", [
                        'customer' => $zohoCustomer->name ?? 'Unknown',
                        'error' => $e->getMessage()
                    ]);
                    // Continue with next customer
                }
            }
            
            DB::commit();
            
            Log::info("Batch {$this->syncToImaraCurrentBatch} completed", [
                'processed_in_batch' => count($zohoCustomers),
                'total_processed' => $this->syncToImaraProcessedCount,
                'created' => $this->syncToImaraCreatedCount,
                'linked' => $this->syncToImaraLinkedCount
            ]);
            
            // Update cache with remaining IDs
            if (count($remainingIds) > 0) {
                Cache::put('sync_to_imara_unlinked_ids', $remainingIds, now()->addHours(1));
                // Don't recursively call - let polling handle it
            } else {
                // All batches complete
                $this->completeSyncToImara();
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Batch processing failed', [
                'batch' => $this->syncToImaraCurrentBatch,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->isSyncingToImara = false;
            $this->syncToImaraProgress = 'Sync failed!';
            $this->syncToImaraResult = [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'total_processed' => $this->syncToImaraProcessedCount,
                'created_count' => $this->syncToImaraCreatedCount,
                'linked_count' => $this->syncToImaraLinkedCount,
            ];
            $this->message = 'Sync failed: ' . $e->getMessage();
            $this->messageType = 'error';
            
            Cache::forget('sync_to_imara_unlinked_ids');
        }
    }
    
    protected function completeSyncToImara(): void
    {
        Log::info('Sync to Imara completed', [
            'total_processed' => $this->syncToImaraProcessedCount,
            'created' => $this->syncToImaraCreatedCount,
            'linked' => $this->syncToImaraLinkedCount
        ]);
        
        $this->syncToImaraProgress = 'Sync completed!';
        
        $this->syncToImaraResult = [
            'success' => true,
            'message' => "Successfully synced {$this->syncToImaraProcessedCount} Dynamics customers to Imara.",
            'total_processed' => $this->syncToImaraProcessedCount,
            'created_count' => $this->syncToImaraCreatedCount,
            'linked_count' => $this->syncToImaraLinkedCount,
        ];
        
        $this->isSyncingToImara = false;
        $this->message = "Sync completed: {$this->syncToImaraCreatedCount} created, {$this->syncToImaraLinkedCount} linked.";
        $this->messageType = 'success';
        
        // Clear cache
        Cache::forget('sync_to_imara_unlinked_ids');
    }
    
    /**
     * Generate a unique customer code from name.
     */
    protected function generateCustomerCode(string $name): string
    {
        // Take first 3 letters of name and add a number
        $prefix = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
        if (strlen($prefix) < 3) {
            $prefix = str_pad($prefix, 3, 'X');
        }
        
        // Find the next available number
        $lastCode = \App\Models\CRM\CRMCustomer::where('code', 'LIKE', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();
        
        if ($lastCode) {
            $number = intval(substr($lastCode->code, 3)) + 1;
        } else {
            $number = 1;
        }
        
        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    public function render()
    {
        return view('livewire.billing.dynamics-customer-manager');
    }
}
