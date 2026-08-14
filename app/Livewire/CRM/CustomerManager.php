<?php

namespace App\Livewire\CRM;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CrmCustomerContract;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCompanySubUnit;
use App\Models\CRM\SamplePoint;
use App\Country;
use App\ModulePreConfigs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Services\Commercial\AccountPaymentTermsService;
use App\Services\CRM\CustomerPurgeService;

class CustomerManager extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Customer Management
    public $selectedCustomer = null;
    public $editingCustomer = null;
    public $showCustomerModal = false;
    
    // Customer Form
    public $customerForm = [
        'name' => '',
        'contact_person' => '',
        'designation' => '',
        'postal_address' => '',
        'physical_address' => '',
        'billing_address' => '',
        'website' => '',
        'email' => '',
        'telephone1' => '',
        'telephone2' => '',
        'country_id' => null,
        'city_id' => null,
        'active' => true,
        'account_status' => null,
        'credit_days' => null,
        'payment_terms_note' => '',
        'payment_method' => null,
        'vat_no' => '',
        'trade_license' => '',
        'lpos_required' => false,
        'zoho_customer_id' => null,
        'has_contract' => false,
        'contract_valid_from' => '',
        'contract_valid_to' => '',
    ];

    public $contractFile = null;

    public $logoFile = null;

    public $vatRegistrationCertificateFile = null;

    public ?string $existingLogoUrl = null;

    public ?string $existingVatRegistrationCertificateUrl = null;

    public bool $hasExistingContractAttachment = false;

    // Supporting Data
    public $countries = [];
    public $accounts = [];

    // Dropdown State
    public $countrySearch = '';
    public $citySearch = '';
    public $accountSearch = '';
    public $showCountryDropdown = false;
    public $showCityDropdown = false;
    public $showAccountDropdown = false;
    public $cities = [];

    // Search and Filter
    public $search = '';
    public $accountSettingsFilter = '';
    public $statusFilter = '';
    public $dateFrom = '';
    public $dateTo = '';

    // Bulk Operations
    public $selectedCustomers = [];
    public $selectAll = false;
    public $bulkAction = '';

    // Export
    public $exportFormat = 'csv';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [25, 50, 75, 100];

    // Clone Modal State
    public $showCloneModal = false;
    public $customerToClone = null;
    public $cloneCustomerName = '';

    protected function rules()
    {
        $allowedAccountIds = collect($this->accounts)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        $hasContract = (bool) ($this->customerForm['has_contract'] ?? false);
        $terms = app(AccountPaymentTermsService::class)
            ->resolveFromConfigId($this->customerForm['account_status'] ?? null);

        $rules = [
            'customerForm.name' => 'required|string|max:255',
            'customerForm.contact_person' => 'nullable|string|max:255',
            'customerForm.designation' => 'nullable|string|max:255',
            'customerForm.postal_address' => 'required|string|max:500',
            'customerForm.physical_address' => 'required|string|max:500',
            'customerForm.billing_address' => 'nullable|string|max:1000',
            'customerForm.email' => 'required|email|max:255',
            'customerForm.telephone1' => 'required|string|max:50',
            'customerForm.country_id' => 'required|exists:countries,id',
            'customerForm.city_id' => [
                'nullable',
                'string',
                Rule::exists('cities', 'id')->where(function ($query) {
                    $countryId = $this->customerForm['country_id'] ?? null;
                    if ($countryId) {
                        $query->where('country_id', $countryId);
                    }
                }),
            ],
            'customerForm.account_status' => ['required', Rule::in($allowedAccountIds)],
            'customerForm.credit_days' => $terms['billing_type'] === 'other'
                ? 'required|integer|min:0|max:3650'
                : 'nullable|integer|min:0|max:3650',
            'customerForm.payment_method' => $terms['billing_type'] === 'other'
                ? ['required', Rule::in(array_keys(AccountPaymentTermsService::paymentMethodOptions()))]
                : 'nullable|string|max:100',
            'customerForm.payment_terms_note' => 'nullable|string|max:500',
            'customerForm.vat_no' => 'nullable|string|max:100',
            'customerForm.trade_license' => 'nullable|string|max:255',
            'customerForm.zoho_customer_id' => 'nullable|exists:zoho_customers,id',
            'customerForm.has_contract' => 'boolean',
            'customerForm.contract_valid_from' => $hasContract ? 'nullable|date' : 'nullable',
            'customerForm.contract_valid_to' => $hasContract
                ? 'nullable|date|after_or_equal:customerForm.contract_valid_from'
                : 'nullable',
            'contractFile' => [
                'nullable',
                'file',
                'max:20480',
            ],
            'logoFile' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,gif,webp,svg',
                'max:5120',
            ],
            'vatRegistrationCertificateFile' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,gif,webp',
                'max:10240',
            ],
        ];

        return $rules;
    }

    protected $messages = [
        'customerForm.name.required' => 'Customer name is required.',
        'customerForm.postal_address.required' => 'Postal address is required.',
        'customerForm.physical_address.required' => 'Physical address is required.',
        'customerForm.email.required' => 'Email address is required.',
        'customerForm.email.email' => 'Please enter a valid email address.',
        'customerForm.telephone1.required' => 'Primary phone number is required.',
        'customerForm.country_id.required' => 'Country selection is required.',
        'customerForm.account_status.required' => 'Account settings selection is required.',
        'customerForm.account_status.in' => 'Please select a valid account setting from the list.',
        'customerForm.contract_valid_to.after_or_equal' => 'Contract validity end date must be on or after the start date.',
        'contractFile.max' => 'The contract file may not be greater than 20 MB.',
        'logoFile.mimes' => 'The logo must be a JPG, PNG, GIF, WEBP, or SVG file.',
        'logoFile.max' => 'The logo may not be greater than 5 MB.',
    ];

    public function mount()
    {
        $this->loadAccounts();
    }

    public function loadInitialData()
    {
        // Load countries with optimized caching and query
        $this->countries = Cache::remember('countries_list', 3600, function() {
            return Country::where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name']);
        });
        
        $this->loadAccounts();
    }

    protected function loadAccounts()
    {
        $account_settings = getConfigTypeByName('Account Settings');
        $id = data_get($account_settings, 'id');

        if ($id) {
            $rawAccounts = getconfigByID($id);
            $this->accounts = collect($rawAccounts)
                ->filter(fn ($account) => (bool) data_get($account, 'status', true))
                ->values()
                ->map(function ($account) {
                    return [
                        'id' => (string) data_get($account, 'id'),
                        'key' => (string) data_get($account, 'key', ''),
                        'value' => (string) data_get($account, 'value', ''),
                        'meta' => is_array(data_get($account, 'meta')) ? data_get($account, 'meta') : [],
                        'status' => (bool) data_get($account, 'status', true),
                        'label' => app(AccountPaymentTermsService::class)->displayLabel($account),
                    ];
                })
                ->toArray();
        } else {
            $this->accounts = [];
        }
    }
    
    public function getCustomersProperty()
    {
        $query = CRMCustomer::with(['country', 'currencyinfo'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('telephone1', 'like', '%' . $this->search . '%')
                  ->orWhere('physical_address', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->accountSettingsFilter) {
            $query->where('account_status', $this->accountSettingsFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('active', $this->statusFilter);
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        return $query->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedAccountSettingsFilter()
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
        $this->accountSettingsFilter = '';
        $this->statusFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    // Bulk Operations
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedCustomers = $this->customers->pluck('id')->toArray();
        } else {
            $this->selectedCustomers = [];
        }
    }

    public function updatedSelectedCustomers()
    {
        $this->selectAll = count($this->selectedCustomers) === $this->customers->count();
    }

    public function bulkDelete()
    {
        if (empty($this->selectedCustomers)) {
            $this->message = 'Please select customers to delete.';
            $this->messageType = 'error';
            return;
        }

        try {
            app(CustomerPurgeService::class)->purgeByIds(
                array_map(static fn ($id): string => (string) $id, $this->selectedCustomers)
            );

            $this->selectedCustomers = [];
            $this->selectAll = false;
            $this->message = 'Selected customers deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function bulkStatusUpdate($status)
    {
        if (empty($this->selectedCustomers)) {
            $this->message = 'Please select customers to update.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();
            
            CRMCustomer::whereIn('id', $this->selectedCustomers)
                ->update(['active' => $status]);
            
            DB::commit();
            
            $this->selectedCustomers = [];
            $this->selectAll = false;
            $this->message = 'Selected customers updated successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    // Export Functionality
    public function exportCustomers()
    {
        $customers = CRMCustomer::with(['country'])
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('telephone1', 'like', '%' . $this->search . '%')
                      ->orWhere('physical_address', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->accountSettingsFilter, function ($query) {
                $query->where('account_status', $this->accountSettingsFilter);
            })
            ->when($this->statusFilter !== '', function ($query) {
                $query->where('active', $this->statusFilter);
            })
            ->when($this->dateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->dateTo);
            })
            ->orderBy('name')
            ->get();

        if ($this->exportFormat === 'csv') {
            return $this->exportToCsv($customers);
        } elseif ($this->exportFormat === 'excel') {
            return $this->exportToExcel($customers);
        }

        $this->message = 'Invalid export format selected.';
        $this->messageType = 'error';
    }

    private function exportToCsv($customers)
    {
        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Code', 'Name', 'Email', 'Phone 1', 'Phone 2', 'Country', 
                'Postal Address', 'Physical Address', 'Website', 'Fax', 
                'VAT Number', 'Credit Days', 'Status', 'Created Date'
            ]);

            // CSV Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->code,
                    $customer->name,
                    $customer->email,
                    $customer->telephone1,
                    $customer->telephone2 ?? '',
                    $customer->country->name ?? '',
                    $customer->postal_address,
                    $customer->physical_address,
                    $customer->website ?? '',
                    $customer->fax ?? '',
                    $customer->vat_no ?? '',
                    $customer->credit_days ?? '',
                    $customer->active ? 'Active' : 'Inactive',
                    $customer->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToExcel($customers)
    {
        // This would require Laravel Excel package
        $this->message = 'Excel export requires Laravel Excel package. Please install it first.';
        $this->messageType = 'error';
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    // Customer Methods
    public function showCreateCustomerModal()
    {
        $this->resetCustomerForm();
        $this->resetDropdownStates();
        
        // Load data only when modal is opened to improve performance
        if (empty($this->countries)) {
            $this->loadInitialData();
        } else {
            $this->loadAccounts();
        }
        
        $this->showCustomerModal = true;
    }

    public function showEditCustomerModal($id)
    {
        $customer = CRMCustomer::findOrFail($id);
        
        // Extract first Zoho customer ID from JSON array for dropdown display
        $zohoCustomerId = null;
        if (is_array($customer->zoho_customer_id) && count($customer->zoho_customer_id) > 0) {
            $zohoCustomerId = $customer->zoho_customer_id[0];
        }
        
        $this->hasExistingContractAttachment = $customer->contracts()
            ->whereNotNull('file_path')
            ->exists()
            || $customer->contractAttachments()->exists();

        $currentContract = $customer->currentContract;

        $this->customerForm = [
            'name' => $customer->name,
            'contact_person' => $customer->contact_person ?? '',
            'designation' => $customer->designation ?? '',
            'postal_address' => $customer->postal_address,
            'physical_address' => $customer->physical_address,
            'billing_address' => $customer->billing_address ?? '',
            'website' => $customer->website ?? '',
            'email' => $customer->email,
            'telephone1' => $customer->telephone1,
            'telephone2' => $customer->telephone2 ?? '',
            'country_id' => $customer->country_id,
            'city_id' => $customer->city_id,
            'active' => $customer->active == 1,
            'account_status' => $customer->account_status,
            'credit_days' => $customer->credit_days,
            'payment_terms_note' => $customer->payment_terms_note ?? '',
            'payment_method' => $customer->payment_method,
            'vat_no' => $customer->vat_no ?? '',
            'trade_license' => $customer->trade_license ?? '',
            'lpos_required' => $customer->lpos_required == 1,
            'zoho_customer_id' => $zohoCustomerId,
            'has_contract' => (bool) $customer->has_contract
                || (bool) $currentContract
                || $this->hasExistingContractAttachment
                || filled($customer->contract_valid_from)
                || filled($customer->contract_valid_to),
            'contract_valid_from' => ($currentContract?->valid_from ?? $customer->contract_valid_from)
                ? substr((string) ($currentContract?->valid_from ?? $customer->contract_valid_from), 0, 10)
                : '',
            'contract_valid_to' => ($currentContract?->valid_to ?? $customer->contract_valid_to)
                ? substr((string) ($currentContract?->valid_to ?? $customer->contract_valid_to), 0, 10)
                : '',
        ];

        $this->contractFile = null;
        $this->logoFile = null;
        $this->vatRegistrationCertificateFile = null;
        $this->existingLogoUrl = $customer->logoUrl();
        $this->existingVatRegistrationCertificateUrl = $customer->vatRegistrationCertificateUrl();
        
        // Load data only when modal is opened to improve performance
        if (empty($this->countries)) {
            $this->loadInitialData();
        } else {
            $this->loadAccounts();
        }
        
        $this->resetDropdownStates();
        $this->loadCitiesForSelectedCountry();
        $this->editingCustomer = $customer;
        $this->showCustomerModal = true;
    }

    public function updatedCustomerFormHasContract($value): void
    {
        if (! $value) {
            $this->customerForm['contract_valid_from'] = '';
            $this->customerForm['contract_valid_to'] = '';
            $this->contractFile = null;
        }
    }

    public function saveCustomer()
    {
        $this->loadAccounts();

        if (empty($this->accounts)) {
            $this->addError(
                'customerForm.account_status',
                'Account settings are not configured. Ask an administrator to set up Account Settings under System Configuration.'
            );

            return;
        }

        $this->validate();

        $hasContract = (bool) ($this->customerForm['has_contract'] ?? false);

        try {
            DB::beginTransaction();

            $allowedAccountIds = collect($this->accounts)
                ->map(function ($account) {
                    return (string) (is_object($account) ? ($account->id ?? '') : ($account['id'] ?? ''));
                })
                ->filter()
                ->values();

            if ($this->editingCustomer) {
                // Update existing customer
                $customer = $this->editingCustomer;
            } else {
                if (!$allowedAccountIds->contains((string) $this->customerForm['account_status'])) {
                    $this->message = 'Invalid account settings selection.';
                    $this->messageType = 'error';
                    DB::rollBack();
                    return;
                }

                // Check for duplicate name
                $existingCustomer = CRMCustomer::where('name', $this->customerForm['name'])->first();
                if ($existingCustomer) {
                    $this->message = 'Customer with this name already exists.';
                    $this->messageType = 'error';
                    DB::rollBack();
                    return;
                }

                // Create new customer
                $customer = new CRMCustomer();
                $customer->code = getNamingConventionCode("Customers", $this->customerForm['name']);
            }

            $customer->name = $this->customerForm['name'];
            $customer->contact_person = $this->customerForm['contact_person'] ?? '';
            $customer->designation = $this->customerForm['designation'] ?? '';
            $customer->postal_address = $this->customerForm['postal_address'];
            $customer->physical_address = $this->customerForm['physical_address'];
            $customer->billing_address = $this->customerForm['billing_address'] ?? '';
            $customer->company_id = getUserCompany();
            $customer->website = $this->customerForm['website'];
            $customer->email = $this->customerForm['email'];
            $customer->telephone1 = $this->customerForm['telephone1'];
            $customer->telephone2 = $this->customerForm['telephone2'];
            $customer->country_id = $this->customerForm['country_id'];
            $customer->city_id = $this->customerForm['city_id'] ?: null;
            if ($this->editingCustomer) {
                $customer->active = $this->customerForm['active'] ? 1 : 0;
            } else {
                $customer->active = 1;
            }
            $customer->account_status = $this->customerForm['account_status'];
            app(AccountPaymentTermsService::class)->syncCustomerFromAccountSetting(
                $customer,
                $this->customerForm['account_status'] ?? null,
                is_numeric($this->customerForm['credit_days'] ?? null)
                    ? (int) $this->customerForm['credit_days']
                    : null,
                $this->customerForm['payment_terms_note'] ?? null,
                $this->customerForm['payment_method'] ?? null
            );
            $customer->vat_no = $this->customerForm['vat_no'];
            $customer->trade_license = $this->customerForm['trade_license'] ?? '';
            $customer->lpos_required = $this->customerForm['lpos_required'] ? 1 : 0;
            $customer->has_contract = $hasContract;
            $customer->contract_valid_from = $hasContract
                ? ($this->customerForm['contract_valid_from'] ?: null)
                : null;
            $customer->contract_valid_to = $hasContract
                ? ($this->customerForm['contract_valid_to'] ?: null)
                : null;
            
            // Handle zoho_customer_id as JSON array
            if ($this->customerForm['zoho_customer_id']) {
                $customer->zoho_customer_id = [$this->customerForm['zoho_customer_id']];
            } else {
                $customer->zoho_customer_id = null;
            }

            $customer->save();

            if ($this->logoFile) {
                $this->storeCustomerLogo($customer);
            }

            if ($this->vatRegistrationCertificateFile) {
                $this->storeVatRegistrationCertificate($customer);
            }

            if ($hasContract) {
                $this->storeCustomerContract($customer);
            } else {
                $customer->contracts()->where('is_current', true)->update(['is_current' => false]);
            }

            $wasEditing = (bool) $this->editingCustomer;

            DB::commit();
            
            $this->closeCustomerModal();
            $this->message = $wasEditing ? 'Customer updated successfully!' : 'Customer created successfully!';
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    protected function storeCustomerLogo(CRMCustomer $customer): void
    {
        $previousRelative = null;
        if (filled($customer->logo)) {
            $previousRelative = ltrim(preg_replace('#^/storage/#', '', (string) $customer->logo), '/');
        }

        $extension = strtolower((string) $this->logoFile->getClientOriginalExtension());
        $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '.png');
        $path = $this->logoFile->storeAs('crm-customer-logos', $storedName, 'public');

        $customer->logo = '/storage/' . $path;
        $customer->save();

        if ($previousRelative && $previousRelative !== $path && Storage::disk('public')->exists($previousRelative)) {
            Storage::disk('public')->delete($previousRelative);
        }
    }

    public function removeCustomerLogo(): void
    {
        if (! $this->editingCustomer) {
            $this->logoFile = null;
            $this->existingLogoUrl = null;

            return;
        }

        $customer = $this->editingCustomer;
        if (filled($customer->logo)) {
            $relative = ltrim(preg_replace('#^/storage/#', '', (string) $customer->logo), '/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            $customer->logo = null;
            $customer->save();
        }

        $this->logoFile = null;
        $this->existingLogoUrl = null;
        $this->editingCustomer = $customer->fresh();
    }

    protected function storeVatRegistrationCertificate(CRMCustomer $customer): void
    {
        $previousRelative = null;
        if (filled($customer->vat_registration_certificate)) {
            $previousRelative = ltrim(preg_replace('#^/storage/#', '', (string) $customer->vat_registration_certificate), '/');
        }

        $extension = strtolower((string) $this->vatRegistrationCertificateFile->getClientOriginalExtension());
        $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '.pdf');
        $path = $this->vatRegistrationCertificateFile->storeAs('crm-customer-vat-certificates', $storedName, 'public');

        $customer->vat_registration_certificate = '/storage/' . $path;
        $customer->save();

        if ($previousRelative && $previousRelative !== $path && Storage::disk('public')->exists($previousRelative)) {
            Storage::disk('public')->delete($previousRelative);
        }
    }

    public function removeVatRegistrationCertificate(): void
    {
        if (! $this->editingCustomer) {
            $this->vatRegistrationCertificateFile = null;
            $this->existingVatRegistrationCertificateUrl = null;

            return;
        }

        $customer = $this->editingCustomer;
        if (filled($customer->vat_registration_certificate)) {
            $relative = ltrim(preg_replace('#^/storage/#', '', (string) $customer->vat_registration_certificate), '/');
            if ($relative !== '' && Storage::disk('public')->exists($relative)) {
                Storage::disk('public')->delete($relative);
            }
            $customer->vat_registration_certificate = null;
            $customer->save();
        }

        $this->vatRegistrationCertificateFile = null;
        $this->existingVatRegistrationCertificateUrl = null;
        $this->editingCustomer = $customer->fresh();
    }

    protected function storeCustomerContract(CRMCustomer $customer): void
    {
        $current = $customer->contracts()->where('is_current', true)->latest('created_at')->first();
        $validFrom = $this->customerForm['contract_valid_from'] ?: null;
        $validTo = $this->customerForm['contract_valid_to'] ?: null;
        $postedBy = Auth::user()->name ?? 'System';

        if ($this->contractFile) {
            $customer->contracts()->where('is_current', true)->update(['is_current' => false]);

            $extension = strtolower((string) $this->contractFile->getClientOriginalExtension());
            $storedName = (string) Str::uuid() . ($extension !== '' ? '.' . $extension : '');
            $path = $this->contractFile->storeAs('crm-customer-contracts', $storedName, 'public');

            $contract = new CrmCustomerContract();
            $contract->crm_customer_id = $customer->id;
            $contract->valid_from = $validFrom;
            $contract->valid_to = $validTo;
            $contract->posted_by = $postedBy;
            $contract->is_current = true;
            $contract->original_name = $this->contractFile->getClientOriginalName();
            $contract->file_path = '/storage/' . $path;
            $contract->file_extension = $extension !== '' ? $extension : null;
            $contract->mime_type = $this->contractFile->getMimeType();
            $contract->file_size = $this->contractFile->getSize();
            $contract->save();

            return;
        }

        if ($current) {
            $current->valid_from = $validFrom;
            $current->valid_to = $validTo;
            $current->posted_by = $postedBy;
            $current->save();

            return;
        }

        $contract = new CrmCustomerContract();
        $contract->crm_customer_id = $customer->id;
        $contract->valid_from = $validFrom;
        $contract->valid_to = $validTo;
        $contract->posted_by = $postedBy;
        $contract->is_current = true;
        $contract->save();
    }

    public function deleteCustomer($id)
    {
        try {
            CRMCustomer::findOrFail($id);

            app(CustomerPurgeService::class)->purgeByIds([(string) $id]);

            $this->message = 'Customer deleted successfully!';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'Error: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function viewCustomer($id)
    {
        return redirect()->route('crm.customer.show', $id);
    }

    public function closeCustomerModal()
    {
        $this->showCustomerModal = false;
        $this->resetCustomerForm();
        $this->resetDropdownStates();
    }

    public function resetCustomerForm()
    {
        $this->customerForm = [
            'name' => '',
            'contact_person' => '',
            'designation' => '',
            'postal_address' => '',
            'physical_address' => '',
            'billing_address' => '',
            'website' => '',
            'email' => '',
            'telephone1' => '',
            'telephone2' => '',
            'country_id' => null,
            'city_id' => null,
            'active' => true,
            'account_status' => null,
            'credit_days' => null,
            'payment_terms_note' => '',
            'payment_method' => null,
            'vat_no' => '',
            'trade_license' => '',
            'lpos_required' => false,
            'zoho_customer_id' => null,
            'has_contract' => false,
            'contract_valid_from' => '',
            'contract_valid_to' => '',
        ];
        $this->contractFile = null;
        $this->logoFile = null;
        $this->vatRegistrationCertificateFile = null;
        $this->existingLogoUrl = null;
        $this->existingVatRegistrationCertificateUrl = null;
        $this->hasExistingContractAttachment = false;
        $this->editingCustomer = null;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    // Dropdown Methods
    public function toggleCountryDropdown()
    {
        $this->showCountryDropdown = !$this->showCountryDropdown;
        if ($this->showCountryDropdown) {
            $this->showAccountDropdown = false;
        }
    }

    public function toggleAccountDropdown()
    {
        $this->showAccountDropdown = !$this->showAccountDropdown;
        if ($this->showAccountDropdown) {
            $this->showCountryDropdown = false;
        }
    }

    public function selectCountry($countryId)
    {
        $this->customerForm['country_id'] = $countryId;
        $this->customerForm['city_id'] = null;
        $this->citySearch = '';
        $this->showCityDropdown = false;
        $this->showCountryDropdown = false;
        $this->countrySearch = '';
        $this->loadCitiesForSelectedCountry();
    }

    public function clearCountry(): void
    {
        $this->customerForm['country_id'] = null;
        $this->customerForm['city_id'] = null;
        $this->countrySearch = '';
        $this->citySearch = '';
        $this->showCountryDropdown = false;
        $this->showCityDropdown = false;
        $this->cities = [];
    }

    public function selectCity($cityId): void
    {
        $this->customerForm['city_id'] = $cityId ?: null;
        $this->showCityDropdown = false;
        $this->citySearch = '';
    }

    public function clearCity(): void
    {
        $this->customerForm['city_id'] = null;
        $this->citySearch = '';
        $this->showCityDropdown = false;
    }

    public function loadCitiesForSelectedCountry(): void
    {
        $countryId = $this->customerForm['country_id'] ?? null;
        if (! $countryId) {
            $this->cities = [];

            return;
        }

        $this->cities = \App\City::query()
            ->where('country_id', $countryId)
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'country_id']);
    }

    public function getSelectedCityNameProperty(): string
    {
        $cityId = $this->customerForm['city_id'] ?? null;
        if (! $cityId) {
            return '';
        }

        $city = collect($this->cities)->first(
            fn ($c) => (string) data_get($c, 'id') === (string) $cityId
        );

        return (string) data_get($city, 'name', '');
    }

    public function getFilteredCitiesProperty()
    {
        $cities = collect($this->cities);
        $search = trim(strtolower($this->citySearch));
        if ($search === '') {
            return $cities->values();
        }

        return $cities
            ->filter(fn ($city) => str_contains(strtolower((string) data_get($city, 'name', '')), $search))
            ->values();
    }

    public function selectAccount($accountId)
    {
        $this->customerForm['account_status'] = $accountId ? (string) $accountId : null;
        $this->showAccountDropdown = false;
        $this->accountSearch = '';
        $this->resetValidation('customerForm.account_status');

        if (! $accountId) {
            return;
        }

        $terms = app(AccountPaymentTermsService::class)
            ->resolveFromConfigId((string) $accountId);

        if (! $terms['allows_custom_days']) {
            $this->customerForm['credit_days'] = $terms['days'];
            $this->customerForm['payment_terms_note'] = '';
            $this->customerForm['payment_method'] = $terms['payment_method'];
        } elseif ($terms['billing_type'] !== 'other') {
            $this->customerForm['payment_terms_note'] = '';
            $this->customerForm['payment_method'] = null;
        }
    }

    public function getSelectedAccountTermsProperty(): array
    {
        return app(AccountPaymentTermsService::class)
            ->resolveFromConfigId($this->customerForm['account_status'] ?? null);
    }

    public function getPaymentMethodOptionsProperty(): array
    {
        return AccountPaymentTermsService::paymentMethodOptions();
    }

    public function getFilteredCountriesProperty()
    {
        $countries = collect($this->countries);
        
        if (empty($this->countrySearch)) {
            return $countries;
        }
        
        return $countries->filter(function($country) {
            return stripos($country->name ?? '', $this->countrySearch) !== false;
        });
    }

    public function getFilteredAccountsProperty()
    {
        $accounts = collect($this->accounts);
        $search = trim($this->accountSearch);

        return $accounts
            ->filter(function ($account) use ($search) {
                if ((string) data_get($account, 'id') === (string) ($this->customerForm['account_status'] ?? '')) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return stripos((string) data_get($account, 'key', ''), $search) !== false
                    || stripos((string) data_get($account, 'value', ''), $search) !== false
                    || stripos((string) data_get($account, 'label', ''), $search) !== false;
            })
            ->values();
    }

    public function getSelectedCountryNameProperty()
    {
        if ($this->customerForm['country_id']) {
            $country = collect($this->countries)->firstWhere('id', $this->customerForm['country_id']);
            return $country ? data_get($country, 'name', '') : '';
        }
        return '';
    }

    public function getSelectedAccountNameProperty()
    {
        if ($this->customerForm['account_status']) {
            $account = collect($this->accounts)->first(function ($account) {
                return (string) data_get($account, 'id') === (string) $this->customerForm['account_status'];
            });
            if ($account) {
                return data_get($account, 'label')
                    ?: data_get($account, 'value')
                    ?: data_get($account, 'key', '');
            }
        }
        return '';
    }

    public function resetDropdownStates()
    {
        $this->countrySearch = '';
        $this->citySearch = '';
        $this->accountSearch = '';
        $this->showCountryDropdown = false;
        $this->showCityDropdown = false;
        $this->showAccountDropdown = false;
    }

    // Clone Methods
    public function showCloneModal($customerId)
    {
        $this->customerToClone = CRMCustomer::with([
            'units.subUnits.areas.samplePoints',
            'units.subUnits',
            'units'
        ])->findOrFail($customerId);
        
        $this->cloneCustomerName = '';
        $this->showCloneModal = true;
    }

    public function closeCloneModal()
    {
        $this->showCloneModal = false;
        $this->customerToClone = null;
        $this->cloneCustomerName = '';
    }

    public function getCloneSummaryProperty()
    {
        if (!$this->customerToClone) {
            return [
                'company_units' => 0,
                'company_sub_units' => 0,
                'sample_areas' => 0,
                'sample_points' => 0,
            ];
        }

        $units = $this->customerToClone->units;
        $unitsCount = $units->count();
        
        $subUnitsCount = 0;
        $samplePointsCount = SamplePoint::where('crm_customer_id', $this->customerToClone->id)->count();

        foreach ($units as $unit) {
            $subUnits = $unit->subUnits;
            $subUnitsCount += $subUnits->count();
        }

        return [
            'company_units' => $unitsCount,
            'company_sub_units' => $subUnitsCount,
            'sample_areas' => 0,
            'sample_points' => $samplePointsCount,
        ];
    }

    public function cloneCustomer()
    {
        $this->validate([
            'cloneCustomerName' => 'required|string|max:255|unique:crm_customers,name',
        ], [
            'cloneCustomerName.required' => 'New customer name is required.',
            'cloneCustomerName.unique' => 'A customer with this name already exists.',
        ]);

        if (!$this->customerToClone) {
            $this->message = 'No customer selected for cloning.';
            $this->messageType = 'error';
            return;
        }

        try {
            DB::beginTransaction();

            // Clone customer
            $newCustomer = new CRMCustomer();
            $newCustomer->name = $this->cloneCustomerName;
            $newCustomer->code = getNamingConventionCode("Customers", $this->cloneCustomerName);
            $newCustomer->contact_person = $this->customerToClone->contact_person;
            $newCustomer->designation = $this->customerToClone->designation;
            $newCustomer->postal_address = $this->customerToClone->postal_address;
            $newCustomer->physical_address = $this->customerToClone->physical_address;
            $newCustomer->billing_address = $this->customerToClone->billing_address;
            $newCustomer->website = $this->customerToClone->website;
            $newCustomer->fax = $this->customerToClone->fax;
            $newCustomer->email = $this->customerToClone->email;
            $newCustomer->telephone1 = $this->customerToClone->telephone1;
            $newCustomer->telephone2 = $this->customerToClone->telephone2;
            $newCustomer->country_id = $this->customerToClone->country_id;
            $newCustomer->city_id = $this->customerToClone->city_id;
            $newCustomer->credit_days = $this->customerToClone->credit_days;
            $newCustomer->payment_terms_note = $this->customerToClone->payment_terms_note;
            $newCustomer->payment_method = $this->customerToClone->payment_method;
            $newCustomer->active = $this->customerToClone->active;
            $newCustomer->account_status = $this->customerToClone->account_status;
            $newCustomer->vat_no = $this->customerToClone->vat_no;
            $newCustomer->trade_license = $this->customerToClone->trade_license;
            $newCustomer->lpos_required = $this->customerToClone->lpos_required;
            $newCustomer->contract_valid_from = $this->customerToClone->contract_valid_from;
            $newCustomer->contract_valid_to = $this->customerToClone->contract_valid_to;
            $newCustomer->zoho_customer_id = null; // Don't clone zoho mapping
            $newCustomer->company_id = $this->customerToClone->company_id;
            $newCustomer->unit_configurable_name = $this->customerToClone->unit_configurable_name;
            $newCustomer->sub_unit_configurable_name = $this->customerToClone->sub_unit_configurable_name;
            $newCustomer->area_configurable_name = $this->customerToClone->area_configurable_name;
            $newCustomer->sample_point_configurable_name = $this->customerToClone->sample_point_configurable_name;
            $newCustomer->product_configurable_name = $this->customerToClone->product_configurable_name;
            $newCustomer->currency_id = $this->customerToClone->currency_id;
            $newCustomer->lab_id = $this->customerToClone->lab_id;
            $newCustomer->save();

            // Mapping arrays to maintain relationships
            $unitMapping = []; // oldUnitId => newUnitId
            $subUnitMapping = []; // oldSubUnitId => newSubUnitId

            // Clone company units
            $originalUnits = $this->customerToClone->units;
            foreach ($originalUnits as $originalUnit) {
                $newUnit = new CRMCompanyUnit();
                $newUnit->name = $originalUnit->name;
                $newUnit->crm_customer_id = $newCustomer->id;
                $newUnit->company_id = $originalUnit->company_id;
                $newUnit->active = $originalUnit->active;
                $newUnit->save();
                
                $unitMapping[$originalUnit->id] = $newUnit->id;

                // Clone company sub units
                $originalSubUnits = $originalUnit->subUnits;
                foreach ($originalSubUnits as $originalSubUnit) {
                    $newSubUnit = new CRMCompanySubUnit();
                    $newSubUnit->name = $originalSubUnit->name;
                    $newSubUnit->code = $originalSubUnit->code;
                    $newSubUnit->crm_customer_id = $newCustomer->id;
                    $newSubUnit->crm_company_unit_id = $newUnit->id;
                    $newSubUnit->active = $originalSubUnit->active;
                    $newSubUnit->save();
                    
                    $subUnitMapping[$originalSubUnit->id] = $newSubUnit->id;
                }

                                    // Clone sample points directly under unit (area-less model).
                                    $originalSamplePoints = SamplePoint::where('crm_company_unit_id', $originalUnit->id)
                                        ->where('crm_customer_id', $this->customerToClone->id)
                                        ->get();

                                    foreach ($originalSamplePoints as $originalPoint) {
                                        $newPoint = new SamplePoint();
                                        $newPoint->crm_company_unit_id = $newUnit->id;
                                        $newPoint->crm_customer_id = $newCustomer->id;
                                        $newPoint->name = $originalPoint->name;
                                        $newPoint->code = $originalPoint->code;
                                        $newPoint->description = $originalPoint->description;
                                        $newPoint->active = $originalPoint->active;
                                        $newPoint->gps = $originalPoint->gps ?? '';
                                        $newPoint->crm_company_sub_unit_id = null;
                                        $newPoint->sample_point_area_id = null;
                                        $newPoint->crm_area_id = null;
                                        $newPoint->crm_sample_point_id = null;
                                        $newPoint->save();
                                    }
            }

            DB::commit();

            // Calculate summary before closing modal
            $summary = [
                'company_units' => count($unitMapping),
                'company_sub_units' => count($subUnitMapping),
                'sample_areas' => 0,
                'sample_points' => SamplePoint::where('crm_customer_id', $newCustomer->id)->count(),
            ];
            
            $this->closeCloneModal();
            $this->message = "Customer cloned successfully! Cloned: {$summary['company_units']} company unit(s), {$summary['company_sub_units']} sub unit(s), {$summary['sample_areas']} area(s), {$summary['sample_points']} sampling location(s).";
            $this->messageType = 'success';

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error cloning customer: ' . $e->getMessage();
            $this->messageType = 'error';
        }
    }

    public function render()
    {
        return view('livewire.crm.customer-manager', [
            'customers' => $this->customers
        ]);
    }
}

