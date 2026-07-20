<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Database\Seeders\Concerns\SeedsAmSpecFoodPricelist;
use Database\Seeders\Concerns\SeedsAmSpecFoodStandards;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase13FoodStandardsAndPricelistSeeder extends Seeder
{
    use ResolvesAmSpecCompany;
    use SeedsAmSpecFoodPricelist;
    use SeedsAmSpecFoodStandards;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 13 SEEDING: Food Standards');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();

            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $standardStats = $this->seedAmSpecFoodStandards($company);
            $this->command?->info(sprintf(
                'Food standards: %d standard(s), %d lookup value(s), %d analyte limit(s), %d skipped.',
                $standardStats['standard'],
                $standardStats['standard_values'],
                $standardStats['standard_analytes'],
                $standardStats['skipped'],
            ));

            // Pricelists (master / customer per-parameter / one package) are seeded in Phase 14.
            $this->seedAmSpecFoodPricelist($company);

            $this->command?->info('====================================================');
            $this->command?->info('PHASE 13 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
