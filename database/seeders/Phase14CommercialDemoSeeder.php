<?php

namespace Database\Seeders;

use App\User;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Database\Seeders\Concerns\SeedsCommercialPricelistsAndQuotations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Phase14CommercialDemoSeeder extends Seeder
{
    use ResolvesAmSpecCompany;
    use SeedsCommercialPricelistsAndQuotations;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 14 SEEDING: Master + Customer + Package Pricelists + Quotations');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $preparedBy = User::query()->where('active', 1)->where('is_client', 0)->orderBy('id')->first()
                ?? User::query()->orderBy('id')->first();

            if (! $preparedBy) {
                $this->command?->error('No staff user found for quotation preparation.');

                return;
            }

            $stats = $this->seedCommercialPricelistsAndQuotations($company, $preparedBy);

            $this->command?->info(sprintf(
                'Phase 14 complete: %d pricelist(s), %d item(s), %d assignment(s), %d quotation(s), %d line(s).',
                $stats['pricelists'],
                $stats['items'],
                $stats['assignments'],
                $stats['quotations'],
                $stats['lines'],
            ));
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 14 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
