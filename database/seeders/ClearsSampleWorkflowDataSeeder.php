<?php

namespace Database\Seeders;

use Database\Seeders\Concerns\ClearsAllSampleBatchData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearsSampleWorkflowDataSeeder extends Seeder
{
    use ClearsAllSampleBatchData;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('CLEARING ALL SAMPLE / BATCH WORKFLOW DATA');
            $this->command?->info('====================================================');

            $this->clearAllSampleBatchData();

            $this->command?->info('====================================================');
            $this->command?->info('SAMPLE / BATCH CLEAR COMPLETED');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }
}
