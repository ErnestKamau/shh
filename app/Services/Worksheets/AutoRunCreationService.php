<?php

namespace App\Services\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\Formulars\FormulaVersion;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Worksheets\MethodSequenceRun;
use App\Models\Worksheets\MethodSequenceRunSample;
use App\Models\Worksheets\MethodSequenceRunStageData;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\Models\Worksheets\SampleWorksheetFormularMandatoryData;
use App\Models\Worksheets\SampleWorksheetFormularStepData;
use App\Models\Worksheets\SerHeaderWorksheetSampleRelation;
use App\Models\Worksheets\SerStepWorksheetSampleRelation;
use App\Models\SerWorksheetStep;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AutoRunCreationService
{
    /**
     * Entry point to create runs and formulas for a batch after sample registration/assignment.
     */
    public function createRunsForBatch($batchId)
    {
        Log::info('Triggering auto run creation for batch', ['batch_id' => $batchId]);

        try {
            DB::beginTransaction();

            $batch = SampleHeader::find($batchId);
            if (!$batch) {
                Log::warning('Batch not found for auto run creation', ['batch_id' => $batchId]);
                DB::rollBack();
                return;
            }

            // Get all captured results for this batch that need worksheets
            $capturedResults = CapturedResult::where('sample_header_id', $batchId)
                ->with('analysis_type')
                ->where(function ($query) {
                    $query->whereNotNull('formular_id')
                        ->orWhereNotNull('method_sequence_id')
                        ->orWhere('has_no_result_capture', 1);
                })
                ->get();

            if ($capturedResults->isEmpty()) {
                Log::info('No worksheet-eligible results found for batch', ['batch_id' => $batchId]);
                DB::commit();
                return;
            }

            // 1. Process Method Sequences (create runs)
            $this->processMethodSequences($batch, $capturedResults);

            // 2. Process Formulas (create captured worksheet formulas)
            $this->processFormulas($batch, $capturedResults);

            // 3. Process SER Worksheets (No Capture results)
            $this->processSerWorksheets($batch, $capturedResults);

            DB::commit();
            Log::info('Auto run creation completed for batch', ['batch_id' => $batchId]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed auto run creation for batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Group results by Method Sequence and create Runs.
     */
    private function processMethodSequences(SampleHeader $batch, $capturedResults)
    {
        $sequenceGroups = $capturedResults->whereNotNull('method_sequence_id')->groupBy('method_sequence_id');

        foreach ($sequenceGroups as $methodSequenceId => $results) {
            // Check if a pending run already exists for this sequence in this batch
            $existingRun = MethodSequenceRun::where('sample_header_id', $batch->id)
                ->where('method_sequence_id', $methodSequenceId)
                ->where('status', 'pending')
                ->first();

            if ($existingRun) {
                // Add new results to existing pending run
                $this->addResultsToRun($existingRun, $results);
                continue;
            }

            // Create new run
            $this->createNewRun($batch, $methodSequenceId, $results);
        }
    }

    /**
     * Create a new MethodSequenceRun in pending status.
     */
    private function createNewRun(SampleHeader $batch, $methodSequenceId, $results)
    {
        $methodSequence = MethodSequence::with('activeVersion.stages')->find($methodSequenceId);
        if (!$methodSequence || !$methodSequence->activeVersion) {
            return;
        }

        // Determine run number
        $lastRunNumber = MethodSequenceRun::where('sample_header_id', $batch->id)
            ->where('method_sequence_id', $methodSequenceId)
            ->max('run_number') ?? 0;

        $runNumber = $lastRunNumber + 1;
        $runName = "Auto-created Run " . $runNumber;

        $run = MethodSequenceRun::create([
            'sample_header_id' => $batch->id,
            'method_sequence_id' => $methodSequenceId,
            'run_date' => now()->toDateString(),
            'run_number' => $runNumber,
            'run_name' => $runName,
            'status' => 'pending',
            'current_stage_id' => $methodSequence->activeVersion->stages->first()->id ?? null,
        ]);

        $this->addResultsToRun($run, $results);

        // Pre-create stage data placeholders
        foreach ($methodSequence->activeVersion->stages as $stage) {
            MethodSequenceRunStageData::create([
                'run_id' => $run->id,
                'stage_id' => $stage->id,
                'status' => 'pending',
                'duration_hours' => $stage->duration,
                'safe_duration_hours' => $stage->move_to_next_stage_safe_duration,
            ]);
        }

        Log::info('Created auto run', ['run_id' => $run->id, 'run_name' => $runName]);
    }

    /**
     * Link CapturedResults to a MethodSequenceRun.
     */
    private function addResultsToRun(MethodSequenceRun $run, $results)
    {
        foreach ($results as $result) {
            // Check if already in run
            $exists = MethodSequenceRunSample::where('run_id', $run->id)
                ->where('captured_result_id', $result->id)
                ->exists();

            if (!$exists) {
                MethodSequenceRunSample::create([
                    'run_id' => $run->id,
                    'captured_result_id' => $result->id,
                    'sample_detail_id' => $result->sample_detail_id,
                    'sample_header_id' => $run->sample_header_id,
                ]);
            }
        }
    }

    /**
     * Process Formulas and create SampleCapturedWorksheetFormula entries.
     */
    private function processFormulas(SampleHeader $batch, $capturedResults)
    {
        $formulaResults = $capturedResults->whereNotNull('formular_id');

        foreach ($formulaResults as $result) {
            // Check if already created
            $exists = SampleCapturedWorksheetFormula::where('captured_result_id', $result->id)->exists();
            if ($exists)
                continue;

            $formula = Formula::with('activeVersion.formulaSteps', 'activeVersion.mandatoryFields')->find($result->formular_id);
            if (!$formula || !$formula->activeVersion)
                continue;

            $worksheetFormula = SampleCapturedWorksheetFormula::create([
                'sample_header_id' => $batch->id,
                'sample_detail_id' => $result->sample_detail_id,
                'captured_result_id' => $result->id,
                'formular_id' => $result->formular_id,
                'date' => now()->toDateString(),
                'time_in' => now()->format('H:i'),
                'lab_no' => $result->sample->sample_code ?? '',
            ]);

            // Pre-create step data
            foreach ($formula->activeVersion->formulaSteps as $step) {
                SampleWorksheetFormularStepData::create([
                    'worksheet_formular_id' => $worksheetFormula->id,
                    'formula_step_id' => $step->id,
                ]);
            }

            // Pre-create mandatory data
            foreach ($formula->activeVersion->mandatoryFields as $field) {
                SampleWorksheetFormularMandatoryData::create([
                    'worksheet_formular_id' => $worksheetFormula->id,
                    'formula_mandatory_field_id' => $field->id,
                ]);
            }
        }
    }

    /**
     * Process SER Worksheets (samples with has_no_result_capture = 1).
     */
    private function processSerWorksheets(SampleHeader $batch, $capturedResults)
    {
        $serResults = $capturedResults->filter(function ($result) {
            return $result->has_no_result_capture == 1;
        });

        foreach ($serResults as $result) {
            // Check if already created
            $exists = SerHeaderWorksheetSampleRelation::where('sample_detail_id', $result->sample_detail_id)
                ->where('analysis_type_id', $result->analysis_type_id)
                ->exists();

            if ($exists)
                continue;

            // Create Header (following SerWorksheet logic: one per sample)
            $header = SerHeaderWorksheetSampleRelation::create([
                'sample_detail_id' => $result->sample_detail_id,
                'analysis_type_id' => $result->analysis_type_id,
                'date_received' => $batch->receipt_date,
                'date_tested' => now()->toDateString(),
                'start_time' => now(),
                // method_id defaults to the analysis element method or the first available method
                'method_id' => $result->analysisElement->method ?? null,
            ]);

            // Pre-populate steps from active configurations
            $activeSteps = SerWorksheetStep::where('is_active', 1)->orderBy('id')->get();
            foreach ($activeSteps as $index => $step) {
                SerStepWorksheetSampleRelation::create([
                    'ser_header_id' => $header->id,
                    'ser_worksheet_step_id' => $step->id,
                    'step_number' => $index + 1,
                    'measurand_id' => $step->default_measurand_ids[0] ?? null,
                    'equipment_id' => $step->default_equipment_id,
                    'analyst_id' => $step->default_analyst_id ?: (Auth::id() ?: 1), // Fallback if no user context
                ]);
            }

            Log::info('Created auto SER run', ['header_id' => $header->id, 'sample_id' => $result->sample_detail_id]);
        }
    }
}
