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
            $company = Company::first();
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
            // 3. Seed 3 Labs (Forensic Chemistry, Forensic DNA, Forensic Toxicology)
            // ----------------------------------------------------------------
            $labsData = [
                ['code' => 'LAB-CHM', 'name' => 'Forensic Chemistry Department'],
                ['code' => 'LAB-DNA', 'name' => 'Forensic DNA Department'],
                ['code' => 'LAB-TOX', 'name' => 'Forensic Toxicology Department'],
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
                'Directorate of Criminal Investigations (DCI)',
                'National Police Service (NPS)',
                'Kenya Wildlife Service (KWS)',
                'Anti-Narcotics Unit (ANU)',
                'Office of the Director of Public Prosecutions (ODPP)',
                'National Transport and Safety Authority (NTSA)',
                'State Coroner\'s Office',
                'Independent Policing Oversight Authority (IPOA)',
                'Special Crimes Unit (SCU)',
                'Military Intelligence Division',
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
                        'telephone2'       => '+2547' . rand(10000000, 99999999),
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
            // 5. Seed 29 Sample Types (Forensic Evidence Categories)
            // ----------------------------------------------------------------
            $sampleTypesData = [
                // Forensic Chemistry
                ['name' => 'Cannabis', 'code' => 'SMP-CAN', 'desc' => 'Cannabis sativa plant material, hash oil, resin'],
                ['name' => 'Catha edulis', 'code' => 'SMP-CAT', 'desc' => 'Catha edulis fresh twigs, leaves, active alkaloids'],
                ['name' => 'Cocaine', 'code' => 'SMP-COC', 'desc' => 'Hydrochloride powder, crack isolates, adulterants'],
                ['name' => 'Heroin', 'code' => 'SMP-HER', 'desc' => 'Brown/white powder, acetylated morphine derivatives'],
                ['name' => 'Amphetamine', 'code' => 'SMP-AMP', 'desc' => 'Synthetic psychostimulants, tablets'],
                ['name' => 'Methamphetamine', 'code' => 'SMP-MET', 'desc' => 'Crystal methamphetamine, ice'],
                ['name' => 'Fentanyl', 'code' => 'SMP-FEN', 'desc' => 'Synthetic opioids, patches, trace precursors'],
                ['name' => 'Foods containing drugs', 'code' => 'SMP-FDD', 'desc' => 'Edibles, candies, spiked food specimens'],
                ['name' => 'Drinks containing drugs', 'code' => 'SMP-DKD', 'desc' => 'Spiked drinks, alcoholic cocktails with sedatives'],
                ['name' => 'Forensic Blood', 'code' => 'SMP-FBL', 'desc' => 'Whole blood from crime scenes / drivers'],
                ['name' => 'Forensic Urine', 'code' => 'SMP-FUR', 'desc' => 'Urine specimens from biological screening'],
                ['name' => 'Miscellaneous Chemistry', 'code' => 'SMP-MCH', 'desc' => 'Unknown chemical powders, fire debris, trace items'],
                ['name' => 'Forensic Chem Others', 'code' => 'SMP-FCO', 'desc' => 'Other chemical investigations'],

                // Forensic DNA (Human & Non-Human)
                ['name' => 'Rape Cases (Human DNA)', 'code' => 'SMP-RAP', 'desc' => 'Sexual assault kits, vaginal/cervical swabs'],
                ['name' => 'Murder Cases (Human DNA)', 'code' => 'SMP-MDN', 'desc' => 'Bloodstains on weapons, forensic trace touch DNA'],
                ['name' => 'Armed Robbery (Human DNA)', 'code' => 'SMP-RDN', 'desc' => 'DNA extracted from clothing, masks, discarded items'],
                ['name' => 'Attempted Murder (Human DNA)', 'code' => 'SMP-AMDN', 'desc' => 'Biological matter from assault struggles'],
                ['name' => 'Attempted Homicide (Human DNA)', 'code' => 'SMP-AHDN', 'desc' => 'Straggle evidence, fingernail scrapings'],
                ['name' => 'Disaster Victims ID (Human DNA)', 'code' => 'SMP-DVI', 'desc' => 'Skeletonized remains, bone, teeth, deep tissue'],
                ['name' => 'Wildlife Poaching (Non-Human DNA)', 'code' => 'SMP-WLP', 'desc' => 'Elephant ivory, rhino horn, skins, illicit bushmeat'],
                ['name' => 'Wildlife Trafficking (Non-Human DNA)', 'code' => 'SMP-WLT', 'desc' => 'Illegal animal trading, birds, reptiles, exotic hides'],
                ['name' => 'Animal Attacks (Non-Human DNA)', 'code' => 'SMP-AAT', 'desc' => 'Predator/canine saliva swabs, hair, claws'],
                ['name' => 'Misc DNA Investigation', 'code' => 'SMP-MDNA', 'desc' => 'Trace epithelial cells, suspected touch surfaces, hair'],
                ['name' => 'Forensic DNA Others', 'code' => 'SMP-FDO', 'desc' => 'Other genetic identifications'],

                // Forensic Toxicology
                ['name' => 'Murder Cases (Tox)', 'code' => 'SMP-MTO', 'desc' => 'Post-mortem viscera, stomach contents, vitreous humor'],
                ['name' => 'Attempted Murder (Tox)', 'code' => 'SMP-AMTO', 'desc' => 'Suspected poisoned food, clinical toxic blood panels'],
                ['name' => 'Attempted Homicide (Tox)', 'code' => 'SMP-AHTO', 'desc' => 'Clinical toxic screens for biological agents'],
                ['name' => 'Misc Tox Investigation', 'code' => 'SMP-MTOX', 'desc' => 'Ingestion of unknown organic/inorganic toxins'],
                ['name' => 'Forensic Tox Others', 'code' => 'SMP-FTO', 'desc' => 'Other toxicological profiles'],
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
            // 6. Seed 10 Forensic Analysis Types
            // ----------------------------------------------------------------
            $analysisTypesData = [
                ['name' => 'Cannabis Potency Screen', 'code' => 'ANA-CAN', 'st_index' => 0, 'lab_index' => 0],
                ['name' => 'Catha edulis active agent assay', 'code' => 'ANA-CAT', 'st_index' => 1, 'lab_index' => 0],
                ['name' => 'Cocaine GC-MS Quantitation', 'code' => 'ANA-COC', 'st_index' => 2, 'lab_index' => 0],
                ['name' => 'Heroin Purity Screening', 'code' => 'ANA-HER', 'st_index' => 3, 'lab_index' => 0],
                ['name' => 'Amphetamine HPLC Check', 'code' => 'ANA-AMP', 'st_index' => 4, 'lab_index' => 0],
                ['name' => 'Methamphetamine GC-MS Scan', 'code' => 'ANA-MET', 'st_index' => 5, 'lab_index' => 0],
                ['name' => 'Fentanyl LC-MS/MS Assay', 'code' => 'ANA-FEN', 'st_index' => 6, 'lab_index' => 0],
                ['name' => 'STR DNA Profiling (Human)', 'code' => 'ANA-STR', 'st_index' => 13, 'lab_index' => 1],
                ['name' => 'Wildlife Species DNA ID', 'code' => 'ANA-WLD', 'st_index' => 19, 'lab_index' => 1],
                ['name' => 'Post-Mortem Poison Screen', 'code' => 'ANA-TOX', 'st_index' => 24, 'lab_index' => 2],
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
