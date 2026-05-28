<?php

namespace App\Observers;

use App\CapturedResult;
use App\Models\Lab\TatCaptured;
use App\Services\Analysis\CapturedResultWorksheetSyncService;
use App\SampleAnalysisDates;
use App\SampleDate;
use App\SampleHeader;
use App\Services\Dashboards\Concerns\DashboardHelpers;

class CapturedObserver
{
    public function __construct(
        protected CapturedResultWorksheetSyncService $worksheetSyncService,
    ) {}

    /**
     * Handle the captured "created" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function created(CapturedResult $captured): void
    {
        if (! $this->worksheetSyncService->sync($captured)) {
            return;
        }

        $captured->saveQuietly();
    }

    /**
     * Handle the captured "updated" event.
     *
     * Writes a *signed* tat_overdue_days value using the 12-hour grace rule:
     *   < 0  finished early (more than 12 h before deadline)
     *   = 0  on-time       (within ±12 h of deadline)
     *   > 0  finished late (more than 12 h after deadline)
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function updated(CapturedResult $captured)
    {
        $tat_exist     = TatCaptured::where('captured_result_id', $captured->id)->where('is_complete', 0)->first();
        $tat_complete  = TatCaptured::where('captured_result_id', $captured->id)->where('is_complete', 1)->first();
        $analysis_date = SampleAnalysisDates::where('sample_detail_id', $captured->sample_detail_id)
            ->where('sample_header_id', $captured->sample_header_id)
            ->first();

        // Skip if nothing has changed
        if (isset($tat_complete->id) && $tat_complete->result == $captured->result) {
            return "done";
        }
        if (isset($tat_exist->id) && $tat_exist->result == $captured->result) {
            return "done";
        }

        $batch       = SampleHeader::find($captured->sample_header_id);
        $sample_date = SampleDate::where('sample_header_id', $batch->id)->where('name', 'Target Date')->first();
        $finishedAt  = date('Y-m-d H:i:s');

        // Signed offset with 12-hour grace (negative = early, 0 = on-time, positive = late)
        $signedOffset = DashboardHelpers::computeSignedTatOffset($sample_date->date, $finishedAt);

        if (isset($tat_exist->id) && $tat_exist->result != $captured->result) {
            // Update existing incomplete TAT record
            $tat_exist->result              = $captured->result;
            $tat_exist->analyst_id          = $captured->analystIdForTat();
            $tat_exist->start_date_analysis = $analysis_date->start_analysis_date ?? null;
            $tat_exist->finished_date       = $finishedAt;
            $tat_exist->tat_date            = $sample_date->date;
            $tat_exist->tat_overdue_days    = $signedOffset;
            $tat_exist->tat_remark          = $this->resolveTatRemark($signedOffset);
            $tat_exist->save();
            return "done";
        }

        if (!isset($tat_complete->id) && !isset($tat_exist->id)) {
            // Create a new TAT record
            $tat                      = new TatCaptured();
            $tat->captured_result_id  = $captured->id;
            $tat->analysis_type_id    = $captured->analysis_type_id;
            $tat->analyte_id          = $captured->analyte_id;
            $tat->sample_type_id      = $batch->sample_type_id;
            $tat->sample_detail_id    = $captured->sample_detail_id;
            $tat->result              = $captured->result;
            $tat->analyst_id          = $captured->analystIdForTat();
            $tat->tat_date            = $sample_date->date;
            $tat->sample_header_id    = $batch->id;
            $tat->finished_date       = $finishedAt;
            $tat->tat_overdue_days    = $signedOffset;
            $tat->start_date_analysis = $analysis_date->start_analysis_date ?? '';
            $tat->tat_remark          = $this->resolveTatRemark($signedOffset);
            $tat->save();
            return "done";
        }

        if (isset($tat_complete->id) && $tat_complete->result == $captured->result && !isset($tat_exist->id)) {
            // Re-completion path — create another TAT record alongside the completed one
            $tat                      = new TatCaptured();
            $tat->captured_result_id  = $captured->id;
            $tat->analysis_type_id    = $captured->analysis_type_id;
            $tat->analyte_id          = $captured->analyte_id;
            $tat->sample_type_id      = $batch->sample_type_id;
            $tat->sample_detail_id    = $captured->sample_detail_id;
            $tat->result              = $captured->result;
            $tat->analyst_id          = $captured->analystIdForTat();
            $tat->tat_date            = $sample_date->date;
            $tat->sample_header_id    = $batch->id;
            $tat->finished_date       = $finishedAt;
            $tat->tat_overdue_days    = $signedOffset;
            $tat->start_date_analysis = $analysis_date->start_analysis_date ?? '';
            $tat->tat_remark          = $this->resolveTatRemark($signedOffset);
            $tat->save();
            return "done";
        }

        return "Done";
    }

    /**
     * Handle the captured "deleted" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function deleted(CapturedResult $captured)
    {
        //
    }

    /**
     * Handle the captured "restored" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function restored(CapturedResult $captured)
    {
        //
    }

    /**
     * Handle the captured "force deleted" event.
     *
     * @param  \App\CapturedResult  $captured
     * @return void
     */
    public function forceDeleted(CapturedResult $captured)
    {
        //
    }

    // -------------------------------------------------------------------------
    // Private Helpers
    // -------------------------------------------------------------------------

    /**
     * Map a signed TAT offset to the legacy tat_remark code.
     *
     *  1 = Excellent        (early ≥ 2d beyond grace)
     *  2 = Satisfactory     (early 1d beyond grace)
     *  3 = Good / On Time   (within ±12 h grace)
     *  4 = Need Improvement (late 1d beyond grace)
     *  5 = Unsatisfactory   (late ≥ 2d beyond grace)
     */
    private function resolveTatRemark(int $signedOffset): int
    {
        if ($signedOffset <= -2) return 1;
        if ($signedOffset === -1) return 2;
        if ($signedOffset === 0)  return 3;
        if ($signedOffset === 1)  return 4;
        return 5;
    }
}
