<?php

namespace Database\Seeders\Concerns;

use App\Company;
use App\Invoice;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAmSpecCrmData
{
    protected function clearAmSpecCrmData(Company $company): void
    {
        $customerIds = CRMCustomer::query()
            ->where('company_id', $company->id)
            ->pluck('id');

        if ($customerIds->isEmpty()) {
            $this->command?->info("No CRM customers to clear for {$company->name}.");

            return;
        }

        $unitIds = CRMCompanyUnit::query()
            ->whereIn('crm_customer_id', $customerIds)
            ->pluck('id');

        $deletedSamplePoints = 0;
        if (Schema::connection('pgsql')->hasTable('sample_points') && $unitIds->isNotEmpty()) {
            $deletedSamplePoints = DB::connection('pgsql')
                ->table('sample_points')
                ->whereIn('crm_company_unit_id', $unitIds)
                ->delete();
        }

        $deletedInvoices = 0;
        if (Schema::connection('pgsql')->hasTable('customer_invoice')) {
            $deletedInvoices = Invoice::query()
                ->whereIn('customer_id', $customerIds)
                ->delete();
        }

        $deletedContacts = CustomerContact::query()
            ->whereIn('crm_customer_id', $customerIds)
            ->delete();

        $deletedUnits = CRMCompanyUnit::query()
            ->whereIn('crm_customer_id', $customerIds)
            ->delete();

        $deletedCustomers = CRMCustomer::query()
            ->whereIn('id', $customerIds)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared CRM data for %s: %d customers, %d units, %d contacts, %d sample points, %d invoices.',
            $company->name,
            $deletedCustomers,
            $deletedUnits,
            $deletedContacts,
            $deletedSamplePoints,
            $deletedInvoices,
        ));
    }
}
