<?php

namespace App\Services\CRM;

use App\Invoice;
use App\Models\Billing\PricelistCustomer;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class CustomerPurgeService
{
    /**
     * Delete all CRM customers and direct dependents for a company.
     *
     * @return array<string, int>
     */
    public function purgeForCompany(string $companyId): array
    {
        $customerIds = CRMCustomer::query()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->all();

        return $this->purgeCustomerIds($customerIds);
    }

    /**
     * Delete every CRM customer in the system (system-admin bulk replace).
     *
     * @return array<string, int>
     */
    public function purgeAll(): array
    {
        $customerIds = CRMCustomer::query()
            ->pluck('id')
            ->all();

        return $this->purgeCustomerIds($customerIds);
    }

    /**
     * @param  list<string>  $customerIds
     * @return array<string, int>
     */
    public function purgeByIds(array $customerIds): array
    {
        return $this->purgeCustomerIds($customerIds);
    }

    /**
     * @param  list<string>  $customerIds
     * @return array<string, int>
     */
    private function purgeCustomerIds(array $customerIds): array
    {
        $summary = [
            'customers' => 0,
            'units' => 0,
            'contacts' => 0,
            'sample_points' => 0,
            'invoices' => 0,
            'pricelist_assignments' => 0,
        ];

        if ($customerIds === []) {
            return $summary;
        }

        DB::transaction(function () use ($customerIds, &$summary): void {
            $summary['pricelist_assignments'] = PricelistCustomer::query()
                ->whereIn('customer_id', $customerIds)
                ->delete();

            $unitIds = CRMCompanyUnit::query()
                ->whereIn('crm_customer_id', $customerIds)
                ->pluck('id')
                ->all();

            if ($unitIds !== [] && Schema::hasTable('sample_points')) {
                $summary['sample_points'] = DB::table('sample_points')
                    ->whereIn('crm_company_unit_id', $unitIds)
                    ->delete();
            }

            if (Schema::hasTable('customer_invoice')) {
                $summary['invoices'] = Invoice::query()
                    ->whereIn('customer_id', $customerIds)
                    ->delete();
            }

            $summary['contacts'] = CustomerContact::query()
                ->whereIn('crm_customer_id', $customerIds)
                ->delete();

            $summary['units'] = CRMCompanyUnit::query()
                ->whereIn('crm_customer_id', $customerIds)
                ->delete();

            $summary['customers'] = CRMCustomer::query()
                ->whereIn('id', $customerIds)
                ->delete();
        });

        return $summary;
    }
}
