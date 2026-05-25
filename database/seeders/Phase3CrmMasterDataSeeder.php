<?php

namespace Database\Seeders;

use App\Company;
use App\Country;
use App\Models\CRM\CRMCompanyUnit;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
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

            $company = Company::query()->first();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $country = Country::query()->where('name', 'like', '%Tanzania%')->first()
                ?? Country::query()->where('name', 'like', '%Kenya%')->first()
                ?? Country::query()->first();

            $customers = [
                ['name' => 'Tanzania Police Force', 'code' => 'INT-POL-001', 'type' => 'Police', 'internal' => true],
                ['name' => 'Directorate of Criminal Investigations', 'code' => 'INT-DCI-002', 'type' => 'DCI', 'internal' => true],
                ['name' => 'High Court of Tanzania', 'code' => 'INT-CRT-003', 'type' => 'Courts', 'internal' => true],
                ['name' => 'Drug Control and Enforcement Authority', 'code' => 'INT-DCEA-004', 'type' => 'Drug Control', 'internal' => true],
                ['name' => 'Civilian Evidence Submission Desk', 'code' => 'EXT-CIV-001', 'type' => 'Civilians', 'internal' => false],
                ['name' => 'Tanzania Private Industries Consortium', 'code' => 'EXT-IND-002', 'type' => 'Private', 'internal' => false],
                ['name' => 'Dar es Salaam Port Health Authority', 'code' => 'EXT-PRT-003', 'type' => 'Ports', 'internal' => false],
                ['name' => 'Tanzania Advocates Forensic Liaison Group', 'code' => 'EXT-ADV-004', 'type' => 'Advocates', 'internal' => false],
                ['name' => 'National Research Institutions Forum', 'code' => 'EXT-RES-005', 'type' => 'Research', 'internal' => false],
            ];

            foreach ($customers as $index => $data) {
                $customer = CRMCustomer::query()->updateOrCreate(
                    ['name' => $data['name']],
                    [
                        'code' => $data['code'],
                        'postal_address' => 'P.O. Box '.(1000 + $index).', Dar es Salaam',
                        'physical_address' => $data['type'].' Liaison Office',
                        'email' => strtolower(str_replace([' ', '&'], ['', 'and'], $data['name'])).'@example.test',
                        'telephone1' => '+2557'.random_int(10000000, 99999999),
                        'telephone2' => '+2557'.random_int(10000000, 99999999),
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

                for ($contactNo = 1; $contactNo <= 2; $contactNo++) {
                    CustomerContact::query()->updateOrCreate(
                        [
                            'crm_customer_id' => $customer->id,
                            'email' => 'contact'.$contactNo.'.'.strtolower($data['code']).'@example.test',
                        ],
                        [
                            'crm_company_unit_id' => $unit->id,
                            'company_id' => $company->id,
                            'first_name' => $contactNo === 1 ? 'Primary' : 'Alternate',
                            'last_name' => str_replace(' ', '', $data['type']),
                            'telephone' => '+2557'.random_int(10000000, 99999999),
                            'mobile' => '+2557'.random_int(10000000, 99999999),
                            'job_occupation' => $contactNo === 1 ? 'Submitting Officer' : 'Evidence Liaison Officer',
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
}
