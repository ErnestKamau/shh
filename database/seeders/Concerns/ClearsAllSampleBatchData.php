<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait ClearsAllSampleBatchData
{
    protected function clearAllSampleBatchData(): void
    {
        $db = DB::connection('pgsql');

        $deletedCaptured = $this->deleteAllFromTable($db, 'captured_results');
        $deletedResults = $this->deleteAllFromTable($db, 'results');
        $deletedTat = $this->deleteAllFromTable($db, 'tat_captured');
        $deletedQcResults = $this->deleteAllFromTable($db, 'qc_results');
        $deletedCapturedView = $this->deleteAllFromTable($db, 'captured_view');
        $deletedSampleDetails = $this->deleteAllFromTable($db, 'sample_details');
        $deletedAcceptanceLines = $this->deleteAllFromTable($db, 'analysis_acceptance_form_lines');
        $deletedAcceptanceForms = $this->deleteAllFromTable($db, 'analysis_acceptance_forms');
        $deletedSampleHeaders = $this->deleteAllFromTable($db, 'sample_headers');
        $deletedQuotations = $this->deleteQuotationsLinkedToEnquiries($db);
        $deletedTrfInstances = $this->deleteAllFromTable($db, 'test_request_form_instances');
        $deletedSubmissionInstances = $this->deleteAllFromTable($db, 'submission_form_instances');
        $deletedEnquiries = $this->deleteAllFromTable($db, 'sample_submission_requests');

        $this->command?->info(sprintf(
            'Cleared all sample/batch workflow data: %d headers, %d details, %d captured results, %d results, %d TAT rows, %d QC results, %d acceptance forms, %d enquiries, %d TRF instances, %d submission instances, %d enquiry quotations.',
            $deletedSampleHeaders,
            $deletedSampleDetails,
            $deletedCaptured,
            $deletedResults,
            $deletedTat,
            $deletedQcResults,
            $deletedAcceptanceForms,
            $deletedEnquiries,
            $deletedTrfInstances,
            $deletedSubmissionInstances,
            $deletedQuotations,
        ));

        if ($deletedAcceptanceLines > 0) {
            $this->command?->info("  Also cleared {$deletedAcceptanceLines} acceptance form lines.");
        }

        if ($deletedCapturedView > 0) {
            $this->command?->info("  Also cleared {$deletedCapturedView} captured view rows.");
        }
    }

    /**
     * @param  \Illuminate\Database\Connection  $db
     */
    private function deleteQuotationsLinkedToEnquiries($db): int
    {
        if (! Schema::connection('pgsql')->hasTable('quotation_headers')) {
            return 0;
        }

        if (! Schema::connection('pgsql')->hasColumn('quotation_headers', 'sample_submission_request_id')) {
            return 0;
        }

        return (int) $db->table('quotation_headers')
            ->whereNotNull('sample_submission_request_id')
            ->delete();
    }

    /**
     * @param  \Illuminate\Database\Connection  $db
     */
    private function deleteAllFromTable($db, string $table): int
    {
        if (! Schema::connection('pgsql')->hasTable($table)) {
            return 0;
        }

        return (int) $db->table($table)->delete();
    }
}
