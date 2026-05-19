<?php

namespace Database\Seeders;

use App\Company;
use App\Lab;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCompanyUnit;
use App\SampleType;
use App\AnalysisType;
use App\Models\Formulars\Formula;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Phase1FoundationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Enforce the correct pgsql connection.
        config(['database.default' => 'pgsql']);

        // Allow mass assignment on all models.
        Model::unguard();

        DB::connection('pgsql')->transaction(function () {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 1 SEEDING: Core Foundation & Metadata');
            $this->command?->info('====================================================');

            // ----------------------------------------------------------------
            // 1. Retrieve or Create Base Company
            // ----------------------------------------------------------------
            $company = Company::where('name', 'Imara')->first();
            if (!$company) {
                $kenya = \App\Country::where('name', 'like', '%Kenya%')->first();
                $company = Company::create([
                    'name'            => 'Imara',
                    'logo'            => '/images/no-logo.png',
                    'location'        => 'Nairobi, Kenya',
                    'address'         => 'P.O Box 00100, Nairobi',
                    'country_id'      => $kenya ? $kenya->id : null,
                    'website'         => 'https://imara.co.ke',
                    'email'           => 'info@imara.co.ke',
                    'cell_phone'      => '+254700000000',
                    'telephone'       => '+254200000000',
                    'street'          => 'Mombasa Road',
                    'active'          => true,
                    'show_on_reports' => true,
                ]);
                $this->command?->info('Created base company: Imara');
            } else {
                $this->command?->info('Retrieved existing base company: Imara');
            }

            // ----------------------------------------------------------------
            // 2. Retrieve Existing Users for Lab Assignment
            // ----------------------------------------------------------------
            $activeUser = User::where('active', 1)->first() ?? User::first();
            $activeUserId = $activeUser ? $activeUser->id : null;

            if ($activeUser) {
                $this->command?->info("Dynamically binding employee references to active user: {$activeUser->email}");
            } else {
                $this->command?->warning("No users found in database! Creating dynamic assignments may face null constraint failures.");
            }

            // ----------------------------------------------------------------
            // 3. Seed 3 Labs (Microbiology, Chemistry, Toxicology)
            // ----------------------------------------------------------------
            $labsData = [
                ['code' => 'LAB-MCB', 'name' => 'Microbiology Department'],
                ['code' => 'LAB-CHM', 'name' => 'Chemistry Department'],
                ['code' => 'LAB-Tox', 'name' => 'Toxicology Department'],
            ];

            $labs = [];
            foreach ($labsData as $item) {
                $lab = Lab::updateOrCreate(
                    ['code' => $item['code'], 'company_id' => $company->id],
                    [
                        'name'                 => $item['name'],
                        'address'              => 'Nairobi HQ Building',
                        'location'             => 'Block B, Ground Floor',
                        'email'                => strtolower(str_replace(' ', '', $item['name'])) . '@imara.co.ke',
                        'active'               => true,
                        'is_external'          => false,
                        'phone1'               => '+254700000000',
                        'manager_id'           => $activeUserId,
                        'section_head_user_id' => $activeUserId,
                    ]
                );
                $labs[] = $lab;
                $this->command?->info("Seeded Lab Department: {$lab->name} (Code: {$lab->code})");
            }

            // ----------------------------------------------------------------
            // 4. Seed 10 CRM Customers
            // ----------------------------------------------------------------
            $customersData = [
                'National Health Authority',
                'Evergreen Environmental Solutions',
                'City Water Treatment Corp',
                'Standard Food Safety Agency',
                'Alpha Toxicology Labs',
                'Nairobi Medical Center',
                'East Africa Agri-Testing',
                'Coastal Marine Research',
                'Apex Industrial Quality',
                'Universal Reagents & Diagnostics',
            ];

            $kenya = \App\Country::where('name', 'like', '%Kenya%')->first();
            $countryId = $kenya ? $kenya->id : Country::first()->id;

            $crmCustomers = [];
            foreach ($customersData as $index => $custName) {
                $custCode = 'CUST-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                $customer = CRMCustomer::updateOrCreate(
                    ['name' => $custName],
                    [
                        'code'             => $custCode,
                        'postal_address'   => 'P.O. Box ' . rand(1000, 9999) . ', Nairobi',
                        'physical_address' => 'Industrial Area, Street ' . rand(1, 10),
                        'email'            => 'contact@' . strtolower(str_replace(' ', '', $custName)) . '.com',
                        'telephone1'       => '+2547' . rand(10000000, 99999999),
                        'active'           => 1,
                        'is_internal'      => false,
                        'country_id'       => $countryId,
                        'company_id'       => $company->id,
                    ]
                );
                $crmCustomers[] = $customer;
                $this->command?->info("Seeded Customer: {$customer->name} (Code: {$custCode})");

                // Seed 1 company unit for the first 5 customers
                $unit = null;
                if ($index < 5) {
                    $unit = CRMCompanyUnit::create([
                        'crm_customer_id' => $customer->id,
                        'name'            => 'Main Testing Site ' . ($index + 1),
                        'active'          => 1,
                        'company_id'      => $company->id,
                    ]);
                }

                // Seed 2 contacts per customer
                for ($c = 1; $c <= 2; $c++) {
                    CustomerContact::create([
                        'crm_customer_id'     => $customer->id,
                        'crm_company_unit_id' => $unit ? $unit->id : null,
                        'company_id'          => $company->id,
                        'first_name'          => 'ContactFirst' . $index . $c,
                        'last_name'           => 'ContactLast' . $index . $c,
                        'email'               => "contact{$index}{$c}@" . strtolower(str_replace(' ', '', $custName)) . '.com',
                        'telephone'           => '+2547' . rand(10000000, 99999999),
                        'mobile'              => '+2547' . rand(10000000, 99999999),
                        'job_occupation'      => $c === 1 ? 'Technical Director' : 'Quality Liaison Officer',
                        'unit_name'           => 'Quality Assurance Department',
                        'receive_price_list'  => false,
                        'receive_invoice'     => false,
                        'receive_report'      => false,
                        'active'              => true,
                    ]);
                }
            }
            $this->command?->info('Seeded 20 CRM Customer Contacts and 5 Company Units.');

            // ----------------------------------------------------------------
            // 5. Seed 5 Sample Types
            // ----------------------------------------------------------------
            $sampleTypesData = [
                ['name' => 'Drinking Water', 'code' => 'SMP-H2O', 'desc' => 'Potable, tap, or borehole liquid samples'],
                ['name' => 'Blood Plasma', 'code' => 'SMP-BLD', 'desc' => 'Human or animal biological fluid specimen'],
                ['name' => 'Agricultural Soil', 'code' => 'SMP-SOL', 'desc' => 'Soil, compost, or substrate earth core specimen'],
                ['name' => 'Processed Food', 'code' => 'SMP-FOD', 'desc' => 'Finished edible items or packaging materials'],
                ['name' => 'Volatile Gas', 'code' => 'SMP-GAS', 'desc' => 'Atmospheric or pressurized exhaust emission canister'],
            ];

            $sampleTypes = [];
            foreach ($sampleTypesData as $st) {
                $sampleType = SampleType::updateOrCreate(
                    ['code' => $st['code'], 'company_id' => $company->id],
                    [
                        'name'                  => $st['name'],
                        'description'           => $st['desc'],
                        'active'                => true,
                        'is_results_attachable' => true,
                    ]
                );
                $sampleTypes[] = $sampleType;
                $this->command?->info("Seeded Sample Type: {$sampleType->name} (Code: {$sampleType->code})");
            }

            // ----------------------------------------------------------------
            // 6. Seed 10 Analysis Types
            // ----------------------------------------------------------------
            $analysisTypesData = [
                ['name' => 'Bacteriology Culture', 'code' => 'ANA-BAC', 'st_index' => 0, 'lab_index' => 0], // Drink Water -> Micro
                ['name' => 'Heavy Metal Scan', 'code' => 'ANA-HM', 'st_index' => 0, 'lab_index' => 1],     // Drink Water -> Chem
                ['name' => 'Chemical Composition', 'code' => 'ANA-COMP', 'st_index' => 3, 'lab_index' => 1], // Food -> Chem
                ['name' => 'Toxicological Screen', 'code' => 'ANA-TOX', 'st_index' => 1, 'lab_index' => 2],  // Blood Plasma -> Tox
                ['name' => 'pH Level Measurement', 'code' => 'ANA-PH', 'st_index' => 0, 'lab_index' => 1],  // Drink Water -> Chem
                ['name' => 'Mineral Profiling', 'code' => 'ANA-MIN', 'st_index' => 2, 'lab_index' => 1],     // Soil -> Chem
                ['name' => 'Pesticide Trace Scan', 'code' => 'ANA-PEST', 'st_index' => 2, 'lab_index' => 2], // Soil -> Tox
                ['name' => 'Soil Nutrient Assay', 'code' => 'ANA-NUTR', 'st_index' => 2, 'lab_index' => 1], // Soil -> Chem
                ['name' => 'Mycotoxin Level Check', 'code' => 'ANA-MYCO', 'st_index' => 3, 'lab_index' => 0], // Food -> Micro
                ['name' => 'Volatile Organic Scan', 'code' => 'ANA-VOC', 'st_index' => 4, 'lab_index' => 1],  // Gas -> Chem
            ];

            foreach ($analysisTypesData as $at) {
                $targetSt = $sampleTypes[$at['st_index']];
                $targetLab = $labs[$at['lab_index']];

                $analysisType = AnalysisType::updateOrCreate(
                    ['code' => $at['code'], 'company_id' => $company->id],
                    [
                        'name'           => $at['name'],
                        'description'    => $at['name'] . ' procedure run',
                        'sample_type_id' => $targetSt->id,
                        'lab_id'         => $targetLab->id,
                        'active'         => true,
                        'level'          => 1,
                        'reporting_time' => 48, // 48 hours SLA default
                        'short_name'     => str_replace(' Department', '', $targetLab->name),
                    ]
                );

                // Build lab pivot relationship
                $analysisType->labs()->sync([$targetLab->id]);

                $this->command?->info("Seeded Analysis Type: {$analysisType->name} (Code: {$analysisType->code}) bound to Sample Type '{$targetSt->name}' & Lab '{$targetLab->name}'");
            }

            // ----------------------------------------------------------------
            // 7. Seed 5 Formulas
            // ----------------------------------------------------------------
            $formulasData = [
                ['name' => 'Water Purity Index', 'desc' => 'Calculates global contamination index from raw chemical factors'],
                ['name' => 'Hemoglobin Hematocrit Ratio', 'desc' => 'Calculates blood concentration indexes'],
                ['name' => 'Soil Organic Nitrogen Ratio', 'desc' => 'Assesses nutrient viability profile'],
                ['name' => 'Food Caloric Yield', 'desc' => 'Calculates calorie count from carbohydrate and fat percentages'],
                ['name' => 'Volatile PPM Index', 'desc' => 'Calculates raw parts per million from toxic canister fractions'],
            ];

            foreach ($formulasData as $f) {
                $formula = Formula::updateOrCreate(
                    ['name' => $f['name']],
                    [
                        'description' => $f['desc'],
                        'is_active'   => true,
                    ]
                );
                $this->command?->info("Seeded Formula: {$formula->name}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 1 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
