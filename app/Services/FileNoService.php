<?php

namespace App\Services;

use App\Models\FileNoSequence;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FileNoService
{
    /**
     * Assign a file no to the batch and compute a sample_id_file for each selected sample.
     *
     * File no format:   {sequential}/{year}  e.g. 100/2026
     * Sample file ID:   {customer_sample_id}{sequential}  e.g. B100
     *
     * The sequential counter is derived from file numbers already assigned on batches
     * for the calendar year, so failed or test runs cannot leave "gaps" in the counter.
     *
     * @param  array<int|string>  $sampleDetailIds
     * @return array{file_no: string, sample_id_files: list<string>}
     */
    public function assignFileNoToBatch(SampleHeader $batch, array $sampleDetailIds): array
    {
        return DB::transaction(function () use ($batch, $sampleDetailIds) {
            $year = (int) now()->format('Y');
            $sequential = $this->resolveSequentialForBatch($batch, $year);

            /** @var FileNoSequence $sequenceRow */
            $sequenceRow = FileNoSequence::query()
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $sequenceRow) {
                $sequenceRow = FileNoSequence::create([
                    'year' => $year,
                    'last_sequence' => $sequential,
                ]);
            } elseif ($sequential > $sequenceRow->last_sequence) {
                $sequenceRow->update(['last_sequence' => $sequential]);
            }

            $batch->file_no = $sequential . '/' . $year;
            $batch->save();

            $sampleIdFiles = [];

            if ($sampleDetailIds !== []) {
                $normalizedIds = array_values(array_unique(array_map('strval', $sampleDetailIds)));

                $details = SampleDetails::query()
                    ->whereIn('id', $normalizedIds)
                    ->get();

                if ($details->isEmpty()) {
                    throw new RuntimeException('No matching samples were found to assign Sample File IDs.');
                }

                foreach ($details as $detail) {
                    $prefix = $detail->resolvedCustomerSampleId();
                    $detail->sample_id_file = $prefix !== '' ? $prefix . $sequential : (string) $sequential;
                    $detail->save();
                    $sampleIdFiles[] = (string) $detail->sample_id_file;
                }
            }

            return [
                'file_no' => (string) $batch->file_no,
                'sample_id_files' => $sampleIdFiles,
            ];
        });
    }

    /**
     * Reuse this batch's existing file-no sequence when regenerating; otherwise allocate the next number for the year.
     */
    private function resolveSequentialForBatch(SampleHeader $batch, int $year): int
    {
        $existing = $this->parseSequentialFromFileNo((string) ($batch->file_no ?? ''), $year);
        if ($existing !== null) {
            return $existing;
        }

        return $this->maxAssignedSequenceForYear($year) + 1;
    }

    /**
     * Highest sequential file-no number already assigned to any batch for the given year.
     */
    public function maxAssignedSequenceForYear(int $year): int
    {
        $max = 0;

        SampleHeader::query()
            ->whereNotNull('file_no')
            ->pluck('file_no')
            ->each(function (mixed $fileNo) use ($year, &$max): void {
                $sequential = $this->parseSequentialFromFileNo((string) $fileNo, $year);
                if ($sequential !== null) {
                    $max = max($max, $sequential);
                }
            });

        return $max;
    }

    private function parseSequentialFromFileNo(string $fileNo, int $year): ?int
    {
        if ($fileNo === '') {
            return null;
        }

        if (preg_match('/^(\d+)\/' . preg_quote((string) $year, '/') . '$/', $fileNo, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }
}
