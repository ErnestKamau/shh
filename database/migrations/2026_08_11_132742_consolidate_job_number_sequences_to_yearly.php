<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collapse daily job_number_sequences / technical_job_number_sequences rows
     * into one counter per calendar year (YY). The job number format stays
     * YYMMDD + 3-digit sequence; only the reset boundary changes.
     */
    public function up(): void
    {
        $this->consolidateToYearly('job_number_sequences');
        $this->consolidateToYearly('technical_job_number_sequences');
    }

    public function down(): void
    {
        // Irreversible: daily rows cannot be reconstructed from yearly totals.
    }

    private function consolidateToYearly(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        $rows = DB::table($table)->orderBy('id')->get(['date_ymd', 'last_sequence']);

        if ($rows->isEmpty()) {
            return;
        }

        /** @var array<string, int> $byYear */
        $byYear = [];

        foreach ($rows as $row) {
            $key = (string) $row->date_ymd;
            $year = strlen($key) >= 2 ? substr($key, 0, 2) : $key;

            // Sum daily counters so mid-year cutover continues from total jobs issued.
            $byYear[$year] = ($byYear[$year] ?? 0) + (int) $row->last_sequence;
        }

        DB::table($table)->delete();

        $now = now();

        foreach ($byYear as $year => $lastSequence) {
            DB::table($table)->insert([
                'date_ymd' => $year,
                'last_sequence' => $lastSequence,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
};
