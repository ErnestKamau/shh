<?php

namespace App\Services\CRM;

use App\Country;
use App\ModulePreConfigs;
use App\QuotationHeader;
use App\RequestType;
use App\SampleHeader;
use App\User;
use App\ZohoCustomers;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\Complaint;
use App\Models\CRM\Complaint_Type;
use App\Models\CRM\CustomerCertification;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback;
use App\Models\CRMCustomerReportInfoColumn;
use App\Models\Lab\Qualification;
use App\Models\System\SystemConfiguration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CRMCustomerService
{
    /**
     * Data for the customer list (index) page.
     *
     * @return array{customers: Collection, countries: Collection, accounts: array|\Illuminate\Support\Collection, account_settings: \App\Models\System\SystemConfigurationsType|null, zoho_customers: Collection, currencies: Collection}
     */
    public function getListForIndex(): array
    {
        $companyId = getUserCompany();
        $customers = CRMCustomer::where('company_id', $companyId)
            ->with(['country', 'currencyinfo'])
            ->where('active', 1)
            ->orderBy('name')
            ->get();

        $countries = Country::orderBy('name')->get();
        $account_settings = getConfigTypeByName('Account Settings');
        $zoho_customers = ZohoCustomers::all();
        $currencies = ModulePreConfigs::where('type', 'Currency')->get();

        $accounts = isset($account_settings->id)
            ? getconfigByID($account_settings->id)
            : [];

        return compact('customers', 'countries', 'accounts', 'account_settings', 'zoho_customers', 'currencies');
    }

    /**
     * Get a paginated query builder for the customer list.
     * Selects only columns displayed in the table to reduce payload and improve performance.
     */
    public function getPaginatedQuery(?string $search = null, string $statusFilter = 'active'): \Illuminate\Database\Eloquent\Builder
    {
        $query = CRMCustomer::query()
            ->select([
                'id', 'code', 'name', 'postal_address', 'physical_address',
                'website', 'fax', 'vat_no', 'email', 'telephone1', 'telephone2',
                'country_id', 'active',
            ])
            ->where('company_id', getUserCompany())
            ->with(['country:id,name'])
            ->orderBy('name');

        if ($statusFilter === 'active') {
            $query->where('active', 1);
        } elseif ($statusFilter === 'inactive') {
            $query->where('active', 0);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Get countries and accounts for modal forms (lazy-loaded).
     *
     * @return array{countries: Collection, accounts: array|\Illuminate\Support\Collection}
     */
    public function getModalFormData(): array
    {
        $account_settings = getConfigTypeByName('Account Settings');
        $accounts = isset($account_settings->id) ? getconfigByID($account_settings->id) : [];

        return [
            'countries' => $this->getCachedCountries(),
            'accounts' => $accounts,
        ];
    }

    /**
     * Get countries list cached for 5 minutes to speed up modal load.
     */
    public function getCachedCountries(): Collection
    {
        return Cache::remember('crm_countries_list', 300, fn () => Country::orderBy('name')->get());
    }

    /**
     * Create a new customer. Throws if name already exists.
     *
     * @param  array<string, mixed>  $data
     * @throws \RuntimeException when customer name already exists
     */
    public function create(array $data): CRMCustomer
    {
        $existing = CRMCustomer::where('name', $data['name'])->first();
        if ($existing !== null) {
            throw new \RuntimeException('Customer already exists');
        }

        $customer = new CRMCustomer;
        $customer->code = getNamingConventionCode('Customers', $data['name']);
        $customer->name = $data['name'];
        $customer->physical_address = $data['physical_address'] ?? '';
        $customer->postal_address = $data['postal_address'] ?? '';
        $customer->company_id = getUserCompany();
        $customer->website = ($data['website'] ?? '') === '' ? '' : ($data['website'] ?? '');
        $customer->email = $data['email'] ?? '';
        $customer->fax = $data['fax'] ?? '';
        $customer->telephone1 = $data['phone1'] ?? '';
        $customer->telephone2 = $data['phone2'] ?? '';
        $customer->credit_days = $data['credit_day'] ?? null;
        $customer->country_id = $data['country_id'] ?? null;
        $customer->active = $data['active'] ?? 0;
        $customer->account_status = $data['account_id'] ?? null;
        $customer->vat_no = $data['vat_no'] ?? '';
        $customer->zoho_id = $data['zoho_code'] ?? null;
        $customer->currency_id = $data['currency_id'] ?? null;
        $customer->lpos_required = isset($data['lpos_required']) ? 1 : 0;

        $customer->save();

        return $customer;
    }

    /**
     * Update an existing customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data): void
    {
        $customer = CRMCustomer::findOrFail($id);
        $customer->name = $data['name'] ?? $customer->name;
        $customer->physical_address = $data['physical_address'] ?? '';
        $customer->postal_address = $data['postal_address'] ?? '';
        $customer->company_id = getUserCompany();
        $customer->website = $data['website'] ?? '';
        $customer->email = $data['email'] ?? '';
        $customer->fax = $data['fax'] ?? '';
        $customer->telephone1 = $data['phone1'] ?? '';
        $customer->telephone2 = $data['phone2'] ?? '';
        $customer->country_id = $data['country_id'] ?? null;
        $customer->credit_days = $data['credit_day'] ?? null;
        $customer->active = $data['active'] ?? 0;
        $customer->account_status = $data['account_id'] ?? null;
        $customer->vat_no = $data['vat_no'] ?? '';
        $customer->zoho_id = $data['zoho_code'] ?? null;
        $customer->currency_id = $data['currency_id'] ?? null;

        if (isset($data['lpos_required'])) {
            $customer->lpos_required = 1;
        } elseif (! isset($data['lpos_required']) && $customer->lpos_required == 1) {
            $customer->lpos_required = 0;
        }

        $reportConfig = [];
        foreach (['show_limits', 'show_lod', 'show_test_conformance', 'show_grade'] as $key) {
            $val = $data[$key] ?? null;
            if ($val !== null && $val !== '') {
                $reportConfig[$key] = (bool) (int) $val;
            }
        }
        if ($reportConfig !== []) {
            $customer->report_columns_config = $reportConfig;
        }
        $customer->save();
    }

    /**
     * Update customer report/configurations and info columns.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateConfigurations(int $id, array $data): void
    {
        $customer = CRMCustomer::findOrFail($id);

        $reportConfig = [];
        foreach (['show_limits', 'show_lod', 'show_test_conformance', 'show_grade'] as $key) {
            $val = $data[$key] ?? null;
            if ($val !== null && $val !== '') {
                $reportConfig[$key] = (bool) (int) $val;
            }
        }
        foreach (['label_limits', 'label_test_conformance'] as $key) {
            $val = $data[$key] ?? null;
            if ($val !== null && $val !== '') {
                $reportConfig[$key] = $val;
            }
        }
        $reportConfig['show_standards_below_limits'] = ! empty($data['show_standards_below_limits']);
        $standardsToShow = $data['standards_to_show'] ?? [];
        $reportConfig['standards_to_show'] = is_array($standardsToShow)
            ? array_map('intval', array_filter($standardsToShow))
            : [];
        $customer->report_columns_config = $reportConfig !== [] ? $reportConfig : null;
        $customer->save();

        $infoColumns = $data['info_columns'] ?? [];
        $parsed = [];
        foreach ($infoColumns as $raw) {
            $parts = explode(':', $raw, 3);
            if (count($parts) >= 2) {
                $parsed[] = [
                    'source' => $parts[0],
                    'source_key' => $parts[1],
                    'display_label' => $parts[2] ?? null,
                ];
            }
        }

        CRMCustomerReportInfoColumn::where('crm_customer_id', $customer->id)->delete();
        foreach ($parsed as $idx => $item) {
            CRMCustomerReportInfoColumn::create([
                'crm_customer_id' => $customer->id,
                'source' => $item['source'],
                'source_key' => $item['source_key'],
                'display_label' => $item['display_label'] ?: null,
                'display_order' => $idx,
            ]);
        }
    }

    /**
     * Update a configurable label (unit, sample point, or product name).
     *
     * @param  'unit_configurable_name'|'sample_point_configurable_name'|'product_configurable_name'  $column
     */
    public function updateLabel(int $id, string $column, string $name): void
    {
        $allowed = ['unit_configurable_name', 'sample_point_configurable_name', 'product_configurable_name'];
        if (! in_array($column, $allowed, true)) {
            return;
        }

        $customer = CRMCustomer::findOrFail($id);
        $customer->{$column} = $name;
        $customer->save();
    }

    /**
     * Soft-delete customer: deactivate customer and related users, complaints, contacts.
     *
     * @throws \RuntimeException when customer not found
     */
    public function softDelete(int $id): void
    {
        $customer = getCrmCustomerByID($id);
        if ($customer === null || ! isset($customer->id)) {
            throw new \RuntimeException('No Crm customer with the specified ID!');
        }

        $users = User::where('client_id', $customer->id)->get();
        foreach ($users as $user) {
            $user->active = 0;
            $user->save();
        }

        $complaints = Complaint::where('client_id', $customer->id)->get();
        foreach ($complaints as $complaint) {
            $complaint->rejected = 1;
            $complaint->save();
        }

        $contacts = getCrmCustomerContacts($customer->id);
        foreach ($contacts as $contact) {
            $contact->active = 0;
            $contact->save();
        }

        $customer->active = 0;
        $customer->save();
    }

    /**
     * Minimal data for the customer show page (tab bar, breadcrumb).
     * Tab content is loaded by individual Livewire tab components.
     *
     * @return array{customer: CRMCustomer, is_qplus: SystemConfiguration|null}
     */
    public function getForShowMinimal(int $id): array
    {
        $customer = CRMCustomer::select(['id', 'name', 'unit_configurable_name', 'sample_point_configurable_name'])
            ->findOrFail($id);
        $is_qplus = SystemConfiguration::where('key', 'is_qplus')->first();

        return compact('customer', 'is_qplus');
    }

    /**
     * All data needed for the customer show page (legacy).
     *
     * @return array{customer: CRMCustomer, countries: Collection, samples: array, complaints: Collection, feedbacks: Collection, complaint_types: Collection, ordersSel: Collection, certifications: Collection, qualification_list: Collection, accounts: array|\Illuminate\Support\Collection, quotes: Collection, zoho_customers: Collection, is_qplus: SystemConfiguration|null}
     */
    public function getForShow(int $id): array
    {
        $customer = CRMCustomer::with(['reportInfoColumns', 'sampleCustomFields'])->findOrFail($id);

        $countries = Country::orderBy('name')->get();
        $certifications = CustomerCertification::where('customer_id', $customer->id)->get();
        $qualification_list = Qualification::all();
        $complaints = Complaint::where('client_id', $customer->id)->orderBy('id', 'desc')->get();
        $feedbacks = CustomerFeedback::where('client_id', $customer->id)->orderBy('id', 'desc')->get();
        $quotes = QuotationHeader::where('crm_customer_id', $id)->get();
        $is_qplus = SystemConfiguration::where('key', 'is_qplus')->first();
        $complaint_types = Complaint_Type::all();

        $samplesSel = SampleHeader::join('sample_types as st', 'st.id', 'sample_type_id')
            ->selectRaw('sample_headers.*, st.name as sample_type')
            ->where('crm_customer_id', $id)
            ->where('sample_headers.status', ['Completed'])
            ->orderBy('id', 'desc')
            ->get();

        $ordersSel = SampleHeader::leftJoin('sample_details as sd', 'sample_headers.id', '=', 'sd.sample_header_id')
            ->join('sample_types as st', 'st.id', 'sample_headers.sample_type_id')
            ->selectRaw('sample_headers.id, sample_headers.batch_code, sample_headers.date_collected, sample_headers.reference_number, sample_headers.document_number, sample_headers.status, count(sd.id) as samples, st.name as sample_type')
            ->where('crm_customer_id', $id)
            ->groupBy('sample_headers.id', 'sample_headers.batch_code', 'sample_headers.date_collected', 'sample_headers.reference_number', 'sample_headers.document_number', 'sample_headers.status', 'st.name')
            ->whereNotIn('status', ['Completed'])
            ->get();

        $samples = [];
        foreach ($samplesSel as $s) {
            $reason_ids = $s->reason_for_submission ? explode(',', $s->reason_for_submission) : [];
            $reasons = RequestType::whereIn('id', $reason_ids)->get()->pluck('name')->toArray();
            $s->reasons = implode(', ', $reasons);
            $samples[] = $s;
        }

        $account_settings = getConfigTypeByName('Account Settings');
        $accounts = isset($account_settings->id) ? getconfigByID($account_settings->id) : [];

        $zoho_customers = ZohoCustomers::all();

        return compact(
            'customer',
            'countries',
            'samples',
            'complaints',
            'feedbacks',
            'complaint_types',
            'ordersSel',
            'certifications',
            'qualification_list',
            'accounts',
            'quotes',
            'zoho_customers',
            'is_qplus'
        );
    }
}
