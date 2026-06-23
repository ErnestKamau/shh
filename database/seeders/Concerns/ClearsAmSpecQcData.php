<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;

trait ClearsAmSpecQcData
{
    /** @var list<string> */
    private const QC_TYPE_CODES = ['BLK', 'SPK', 'DUP', 'CRM', 'CAL'];

    /** @var list<string> */
    private const QC_SCHEME_CODES = ['UNODC-QA', 'ISO-17025', 'SWGDAM-QA', 'ENFSI-QA', 'SOFT-CAL'];

    protected function clearAmSpecQcData(): void
    {
        $deletedTypes = DB::connection('pgsql')
            ->table('qc_types')
            ->whereIn('code', self::QC_TYPE_CODES)
            ->delete();

        $deletedSchemes = DB::connection('pgsql')
            ->table('qc_scheme')
            ->whereIn('code', self::QC_SCHEME_CODES)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared AmSpec QC seed data: %d QC types, %d QC schemes.',
            $deletedTypes,
            $deletedSchemes,
        ));
    }
}
