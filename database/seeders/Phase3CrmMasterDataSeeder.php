<?php

namespace Database\Seeders;

use App\Company;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\User;
use Database\Seeders\Concerns\AmSpecSeedData;
use Database\Seeders\Concerns\ClearsAmSpecCrmData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Phase3CrmMasterDataSeeder extends Seeder
{
    use ClearsAmSpecCrmData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 3 SEEDING: CRM Master Data');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $this->clearAmSpecCrmData($company);

            $country = AmSpecSeedData::resolveUaeCountry()
                ?? AmSpecSeedData::resolveBrazilCountry()
                ?? \App\Country::query()->first();

            $portalTestCustomer = null;

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

                if ($data['code'] === 'EXT-ENRG-001') {
                    $portalTestCustomer = $customer;
                }
            }

            $this->seedPortalTestContact($company, $portalTestCustomer);

            $this->seedPortalContact(
                company: $company,
                customer: $portalTestCustomer,
                email: 'karokin35@gmail.com',
                firstName: 'Karokin',
                lastName: 'Portal',
                password: 'test1234',
            );

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 3 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedPortalTestContact(Company $company, ?CRMCustomer $customer): void
    {
        $this->seedPortalContact(
            company: $company,
            customer: $customer,
            email: AmSpecSeedData::seedUserEmail('staff2'),
            firstName: 'Ernest',
            lastName: 'Kamau',
            password: 'password1234',
        );
    }

    private function seedPortalContact(
        Company $company,
        ?CRMCustomer $customer,
        string $email,
        string $firstName,
        string $lastName,
        string $password,
    ): void {
        if (! $customer) {
            $this->command?->warn("Portal contact skipped ({$email}): EXT-ENRG-001 customer not found.");

            return;
        }

        $unit = CRMCompanyUnit::query()
            ->where('crm_customer_id', $customer->id)
            ->first();

        $fullName = trim("{$firstName} {$lastName}");

        $contact = CustomerContact::query()->updateOrCreate(
            [
                'crm_customer_id' => $customer->id,
                'email' => $email,
            ],
            [
                'crm_company_unit_id' => $unit?->id,
                'company_id' => $company->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'telephone' => AmSpecSeedData::dubaiPhone(),
                'mobile' => AmSpecSeedData::dubaiMobile(),
                'job_occupation' => 'Portal Test Contact',
                'unit_name' => $unit?->name,
                'receive_price_list' => true,
                'receive_invoice' => true,
                'receive_report' => true,
                'active' => true,
                'can_login' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $fullName,
                'password' => Hash::make($password),
                'company_id' => $company->id,
                'is_client' => 1,
                'client_id' => $customer->id,
                'crm_contact_id' => $contact->id,
                'active' => 1,
            ]
        );

        $this->command?->info("Portal contact seeded for {$customer->name}: {$email}");
    }
}
