<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAmSpecQcWorkflowData
{
    /** @var list<string> */
    private const QC_WORKFLOW_BATCH_PREFIXES = ['SEED-QC-'];

    /** @var list<string> */
    private const QC_WORKFLOW_STANDARD_CODES = ['STD-QC-REF', 'STD-QC-BLK', 'STD-QC-SPK', 'STD-QC-DUP', 'STD-QC-CAL'];

    protected function clearAmSpecQcWorkflowData(): void
    {
        $headerIds = DB::connection('pgsql')
            ->table('sample_headers')
            ->where(function ($query): void {
                foreach (self::QC_WORKFLOW_BATCH_PREFIXES as $prefix) {
                    $query->orWhere('batch_code', 'like', $prefix.'%');
                }
            })
            ->pluck('id');

        if ($headerIds->isNotEmpty()) {
            $detailIds = DB::connection('pgsql')
                ->table('sample_details')
                ->whereIn('sample_header_id', $headerIds)
                ->pluck('id');

            if ($detailIds->isNotEmpty()) {
                DB::connection('pgsql')->table('qc_results')->whereIn('sample_header_id', $headerIds)->delete();
                DB::connection('pgsql')->table('results')->whereIn('sample_header_id', $headerIds)->delete();
                DB::connection('pgsql')->table('captured_results')->whereIn('sample_header_id', $headerIds)->delete();
                DB::connection('pgsql')->table('sample_details')->whereIn('id', $detailIds)->delete();
            }

            DB::connection('pgsql')->table('sample_headers')->whereIn('id', $headerIds)->delete();
        }

        $standardIds = DB::connection('pgsql')
            ->table('standards')
            ->whereIn('code', self::QC_WORKFLOW_STANDARD_CODES)
            ->pluck('id');

        if ($standardIds->isNotEmpty() && Schema::connection('pgsql')->hasTable('standards_analytes')) {
            DB::connection('pgsql')->table('standards_analytes')->whereIn('standard_id', $standardIds)->delete();
        }

        if (Schema::connection('pgsql')->hasTable('qc_approvers_config')) {
            $deletedApprovers = DB::connection('pgsql')->table('qc_approvers_config')->delete();
            $this->command?->info("Cleared {$deletedApprovers} QC approver(s) for re-seeding.");
        }

        $this->command?->info(sprintf(
            'Cleared AmSpec QC workflow data: %d QC batch(es), %d QC standard(s).',
            $headerIds->count(),
            $standardIds->count(),
        ));
    }
}
