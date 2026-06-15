<?php

namespace Database\Seeders;

use App\Company;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use Database\Seeders\Concerns\AmSpecSeedData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase3CrmMasterDataSeeder extends Seeder
{
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 3 SEEDING: CRM Master Data');
            $this->command?->info('====================================================');

            $company = Company::query()
                ->where('id', AmSpecSeedData::DUBAI_COMPANY_ID)
                ->orWhere('active', true)
                ->orderByDesc('active')
                ->first();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $this->purgeLegacyCrmData($company->id);

            $country = AmSpecSeedData::resolveUaeCountry()
                ?? AmSpecSeedData::resolveBrazilCountry()
                ?? \App\Country::query()->first();

            foreach (AmSpecSeedData::crmCustomers() as $data) {
                $customer = CRMCustomer::query()->updateOrCreate(
                    ['code' => $data['code']],
                    [
                        'name' => $data['name'],
                        'postal_address' => $data['postal_address'],
                        'physical_address' => $data['physical_address'],
                        'email' => strtolower(str_replace([' ', '(', ')', '&'], ['', '', '', 'and'], $data['name'])).'@example.test',
                        'telephone1' => AmSpecSeedData::dubaiPhone(),
                        'telephone2' => AmSpecSeedData::dubaiMobile(),
                        'active' => 1,
                        'is_internal' => $data['internal'],
                        'country_id' => $country?->id,
                        'company_id' => $company->id,
                    ]
                );

                $unit = CRMCompanyUnit::query()->updateOrCreate(
                    [
                        'crm_customer_id' => $customer->id,
                        'name' => $data['type'].' Intake Unit',
                    ],
                    [
                        'active' => 1,
                        'company_id' => $company->id,
                    ]
                );

                foreach ($data['contacts'] as $contactNo => $contact) {
                    $emailSlug = strtolower($data['code']).'.'.($contactNo + 1);

                    CustomerContact::query()->updateOrCreate(
                        [
                            'crm_customer_id' => $customer->id,
                            'email' => $emailSlug.'@example.test',
                        ],
                        [
                            'crm_company_unit_id' => $unit->id,
                            'company_id' => $company->id,
                            'first_name' => $contact['first_name'],
                            'last_name' => $contact['last_name'],
                            'telephone' => AmSpecSeedData::dubaiPhone(),
                            'mobile' => AmSpecSeedData::dubaiMobile(),
                            'job_occupation' => $contact['job'],
                            'unit_name' => $unit->name,
                            'receive_price_list' => false,
                            'receive_invoice' => false,
                            'receive_report' => true,
                            'active' => true,
                        ]
                    );
                }

                $this->command?->info(($data['internal'] ? 'Internal' : 'External')." CRM Customer: {$customer->name}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 3 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function purgeLegacyCrmData(string $companyId): void
    {
        $legacyCustomerIds = CRMCustomer::query()
            ->where('company_id', $companyId)
            ->where(function ($query): void {
                $query->whereIn('name', AmSpecSeedData::legacyCrmCustomerNames())
                    ->orWhereIn('code', AmSpecSeedData::legacyCrmCustomerCodes());
            })
            ->pluck('id');

        if ($legacyCustomerIds->isEmpty()) {
            return;
        }

        CustomerContact::query()
            ->whereIn('crm_customer_id', $legacyCustomerIds)
            ->delete();

        CRMCompanyUnit::query()
            ->whereIn('crm_customer_id', $legacyCustomerIds)
            ->delete();

        CRMCustomer::query()
            ->whereIn('id', $legacyCustomerIds)
            ->delete();

        $this->command?->info('Purged '.$legacyCustomerIds->count().' legacy CRM customer(s).');
    }
}
