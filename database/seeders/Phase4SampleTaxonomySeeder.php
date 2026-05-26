<?php

namespace Database\Seeders;

use App\Company;
use App\Models\Formulars\Formula;
use App\SampleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase4SampleTaxonomySeeder extends Seeder
{
    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 4 SEEDING: Sample Taxonomy & Formula Library');
            $this->command?->info('====================================================');

            $company = Company::query()->first();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');
                return;
            }

            $sampleTypes = [
                ['name' => 'Cannabis', 'code' => 'SMP-CAN', 'desc' => 'Cannabis plant material, hash oil, resin'],
                ['name' => 'Catha edulis', 'code' => 'SMP-CAT', 'desc' => 'Khat twigs, leaves, active alkaloids'],
                ['name' => 'Cocaine', 'code' => 'SMP-COC', 'desc' => 'Hydrochloride powder, crack isolates, adulterants'],
                ['name' => 'Heroin', 'code' => 'SMP-HER', 'desc' => 'Brown/white powder, acetylated morphine derivatives'],
                ['name' => 'Amphetamine', 'code' => 'SMP-AMP', 'desc' => 'Synthetic psychostimulants, tablets'],
                ['name' => 'Methamphetamine', 'code' => 'SMP-MET', 'desc' => 'Crystal methamphetamine, ice'],
                ['name' => 'Fentanyl', 'code' => 'SMP-FEN', 'desc' => 'Synthetic opioids, patches, trace precursors'],
                ['name' => 'Forensic Blood', 'code' => 'SMP-FBL', 'desc' => 'Whole blood from crime scenes or drivers'],
                ['name' => 'Forensic Urine', 'code' => 'SMP-FUR', 'desc' => 'Urine specimens from biological screening'],
                ['name' => 'Miscellaneous Chemistry', 'code' => 'SMP-MCH', 'desc' => 'Unknown powders, fire debris, trace items'],
                ['name' => 'Rape Cases (Human DNA)', 'code' => 'SMP-RAP', 'desc' => 'Sexual assault kits and swabs'],
                ['name' => 'Murder Cases (Human DNA)', 'code' => 'SMP-MDN', 'desc' => 'Bloodstains, weapons, touch DNA'],
                ['name' => 'Armed Robbery (Human DNA)', 'code' => 'SMP-RDN', 'desc' => 'DNA from clothing, masks, discarded items'],
                ['name' => 'Disaster Victims ID (Human DNA)', 'code' => 'SMP-DVI', 'desc' => 'Remains, bone, teeth, deep tissue'],
                ['name' => 'Wildlife Poaching (Non-Human DNA)', 'code' => 'SMP-WLP', 'desc' => 'Ivory, rhino horn, skins, illicit bushmeat'],
                ['name' => 'Wildlife Trafficking (Non-Human DNA)', 'code' => 'SMP-WLT', 'desc' => 'Illegal animal trading and animal products'],
                ['name' => 'Murder Cases (Toxicology)', 'code' => 'SMP-MTO', 'desc' => 'Post-mortem viscera, stomach contents, vitreous humor'],
                ['name' => 'Attempted Poisoning (Toxicology)', 'code' => 'SMP-AMTO', 'desc' => 'Suspected poisoned food and clinical toxic panels'],
                ['name' => 'Food Product Compliance', 'code' => 'SMP-FOOD', 'desc' => 'Food safety and product compliance samples'],
                ['name' => 'Drug Product Compliance', 'code' => 'SMP-DRUG', 'desc' => 'Pharmaceutical and drug quality samples'],
                ['name' => 'Microbiology Swab', 'code' => 'SMP-MIC', 'desc' => 'Surface, clinical, and product microbiology swabs'],
                ['name' => 'Water Quality', 'code' => 'SMP-WTR', 'desc' => 'Potable, industrial, and environmental water'],
                ['name' => 'Soil and Sediment', 'code' => 'SMP-SOIL', 'desc' => 'Environmental soil and sediment samples'],
                ['name' => 'Technical Service Calibration Item', 'code' => 'SMP-TSU', 'desc' => 'Technical service verification or calibration item'],
            ];

            foreach ($sampleTypes as $data) {
                $sampleType = SampleType::query()->updateOrCreate(
                    ['code' => $data['code'], 'company_id' => $company->id],
                    [
                        'name' => $data['name'],
                        'description' => $data['desc'],
                        'active' => true,
                        'is_results_attachable' => true,
                    ]
                );

                $this->command?->info("Seeded Sample Type: {$sampleType->code} - {$sampleType->name}");
            }

            $formulas = [
                ['name' => 'Water Purity Index', 'desc' => 'Calculates contamination index from raw chemical factors'],
                ['name' => 'Hemoglobin Hematocrit Ratio', 'desc' => 'Calculates blood concentration indexes'],
                ['name' => 'Soil Organic Nitrogen Ratio', 'desc' => 'Assesses nutrient viability profile'],
                ['name' => 'Food Caloric Yield', 'desc' => 'Calculates calorie count from carbohydrate and fat percentages'],
                ['name' => 'Volatile PPM Index', 'desc' => 'Calculates raw parts per million from toxic canister fractions'],
            ];

            foreach ($formulas as $data) {
                Formula::query()->updateOrCreate(
                    ['name' => $data['name']],
                    [
                        'description' => $data['desc'],
                        'is_active' => true,
                    ]
                );

                $this->command?->info("Seeded Formula: {$data['name']}");
            }

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 4 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
