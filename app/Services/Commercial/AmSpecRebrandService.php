<?php

namespace App\Services\Commercial;

use App\Company;
use App\Country;
use App\InventoryLocation;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AmSpecRebrandService
{
    public function rebrandCompanies(?Command $command = null): void
    {
        $uae = $this->resolveOrCreateUaeCountry();
        $brazil = $this->resolveOrCreateBrazilCountry();

        Company::query()
            ->whereNotIn('id', [AmSpecSeedData::DUBAI_COMPANY_ID, AmSpecSeedData::BRAZIL_COMPANY_ID])
            ->update(['active' => false]);

        $dubai = Company::query()->updateOrCreate(
            ['id' => AmSpecSeedData::DUBAI_COMPANY_ID],
            AmSpecSeedData::dubaiCompanyAttributes($uae->id)
        );

        $brazilCompany = Company::query()->updateOrCreate(
            ['id' => AmSpecSeedData::BRAZIL_COMPANY_ID],
            AmSpecSeedData::brazilCompanyAttributes($brazil->id)
        );

        User::query()
            ->where(function ($query): void {
                $query->whereNotIn('company_id', [AmSpecSeedData::DUBAI_COMPANY_ID, AmSpecSeedData::BRAZIL_COMPANY_ID])
                    ->orWhereNull('company_id');
            })
            ->update(['company_id' => AmSpecSeedData::DUBAI_COMPANY_ID]);

        $this->removeExtraCompanies($command);

        InventoryLocation::query()
            ->where('name', 'GCLA HQ')
            ->update(['name' => 'AmSpec Dubai HQ', 'company_id' => $dubai->id]);

        InventoryLocation::query()->updateOrCreate(
            ['id' => AmSpecSeedData::DUBAI_HQ_LOCATION_ID],
            [
                'name' => 'AmSpec Dubai HQ',
                'company_id' => $dubai->id,
                'level' => 1,
                'active' => 1,
            ]
        );

        $command?->info("Dubai company: {$dubai->name} (active: ".($dubai->active ? 'yes' : 'no').')');
        $command?->info("Brazil company: {$brazilCompany->name} (active: ".($brazilCompany->active ? 'yes' : 'no').')');
    }

    public function purgeLegacyCrm(string $companyId, ?Command $command = null): void
    {
        $legacyCustomerIds = CRMCustomer::query()
            ->where('company_id', $companyId)
            ->where(function ($query): void {
                $query->whereIn('name', AmSpecSeedData::legacyCrmCustomerNames())
                    ->orWhereIn('code', AmSpecSeedData::legacyCrmCustomerCodes());
            })
            ->pluck('id');

        if ($legacyCustomerIds->isEmpty()) {
            $command?->info('No legacy CRM customers to purge.');

            return;
        }

        CustomerContact::query()->whereIn('crm_customer_id', $legacyCustomerIds)->delete();
        CRMCompanyUnit::query()->whereIn('crm_customer_id', $legacyCustomerIds)->delete();
        CRMCustomer::query()->whereIn('id', $legacyCustomerIds)->delete();

        $command?->info('Purged '.$legacyCustomerIds->count().' legacy CRM customer(s).');
    }

    private function resolveOrCreateUaeCountry(): Country
    {
        return AmSpecSeedData::resolveUaeCountry() ?? Country::query()->firstOrCreate(
            ['name' => 'United Arab Emirates'],
            [
                'iso_code_2' => 'AE',
                'iso_code_3' => 'ARE',
                'address_format' => '{firstname} {lastname}',
                'postcode_required' => 0,
                'status' => 1,
            ]
        );
    }

    private function resolveOrCreateBrazilCountry(): Country
    {
        return AmSpecSeedData::resolveBrazilCountry() ?? Country::query()->firstOrCreate(
            ['name' => 'Brazil'],
            [
                'iso_code_2' => 'BR',
                'iso_code_3' => 'BRA',
                'address_format' => '{firstname} {lastname}',
                'postcode_required' => 0,
                'status' => 1,
            ]
        );
    }

    private function removeExtraCompanies(?Command $command): void
    {
        $extras = Company::query()
            ->whereNotIn('id', [AmSpecSeedData::DUBAI_COMPANY_ID, AmSpecSeedData::BRAZIL_COMPANY_ID])
            ->get();

        foreach ($extras as $company) {
            try {
                DB::connection('pgsql')->transaction(function () use ($company): void {
                    $company->delete();
                });
                $command?->info("Deleted extra company: {$company->name}");
            } catch (\Throwable $exception) {
                $company->update(['active' => false, 'show_on_reports' => false]);
                $command?->warn("Could not delete company {$company->id}; deactivated instead. ({$exception->getMessage()})");
            }
        }
    }
}
