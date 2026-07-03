<?php

namespace Database\Seeders\Concerns;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Models\SubmissionFormInstance;
use App\SampleDetails;
use App\SampleHeader;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait ClearsAmSpecAnalyticalResultsData
{
    protected function clearAmSpecAnalyticalResultsData(): void
    {
        $headerIds = $this->seededSampleHeaderIds();

        if ($headerIds->isEmpty()) {
            $this->command?->info('No seeded sample batches found to clear analytical results for.');

            return;
        }

        $detailIds = SampleDetails::query()
            ->whereIn('sample_header_id', $headerIds)
            ->pluck('id');

        if ($detailIds->isEmpty()) {
            return;
        }

        $capturedIds = DB::connection('pgsql')
            ->table('captured_results')
            ->whereIn('sample_detail_id', $detailIds)
            ->pluck('id');

        $deletedResults = 0;
        if ($capturedIds->isNotEmpty()) {
            $deletedResults = DB::connection('pgsql')
                ->table('results')
                ->whereIn('captured_result_id', $capturedIds)
                ->delete();
        }

        $deletedTat = DB::connection('pgsql')
            ->table('tat_captured')
            ->whereIn('sample_header_id', $headerIds)
            ->delete();

        $deletedCaptured = DB::connection('pgsql')
            ->table('captured_results')
            ->whereIn('sample_detail_id', $detailIds)
            ->delete();

        $this->command?->info(sprintf(
            'Cleared analytical results for seeded batches: %d captured results, %d results, %d TAT rows.',
            $deletedCaptured,
            $deletedResults,
            $deletedTat,
        ));
    }

    /**
     * @return Collection<int, string>
     */
    protected function seededSampleHeaderIds(): Collection
    {
        $seedSfiIds = SubmissionFormInstance::query()
            ->where('form_number', 'like', 'SEED-TRF-%')
            ->pluck('id');

        if ($seedSfiIds->isEmpty()) {
            return collect();
        }

        return SampleHeader::query()
            ->whereIn('id', AnalysisAcceptanceForm::query()
                ->whereIn('submission_form_instance_id', $seedSfiIds)
                ->whereNotNull('sample_header_id')
                ->pluck('sample_header_id'))
            ->pluck('id');
    }
}
