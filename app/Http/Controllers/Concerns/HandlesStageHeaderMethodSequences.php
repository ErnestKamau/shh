<?php

namespace App\Http\Controllers\Concerns;

use App\CapturedResult;
use App\SampleHeader;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait HandlesStageHeaderMethodSequences
{
    public function getMethodSequence($batchId)
    {
        $batch = SampleHeader::find($batchId);

        if (!$batch) {
            return response()->json(['error' => 'Batch not found'], 404);
        }

        // Query captured results with stage_header_id not null
        $capturedResults = $batch->captured_results()
            ->whereNotNull('stage_header_id')
            ->with(['stageHeader.testStages', 'stageHeader.method', 'stageHeader.analyte', 'stageHeader.sampleType'])
            ->get();

        // Group by stage_header_id and get unique stage headers
        $stageHeaders = $capturedResults->groupBy('stage_header_id')
            ->map(function ($results) {
                $stageHeader = $results->first()->stageHeader;
                if ($stageHeader) {
                    $stageHeader->sample_count = $results->count();
                    $stageHeader->sample_codes = $results->pluck('sample_detail_code')->unique()->values();

                    // Process test stages to include media and equipment names
                    if ($stageHeader->testStages) {
                        $stageHeader->testStages = $stageHeader->testStages->map(function ($stage) {
                            // Process media required
                            if ($stage->media_required) {
                                try {
                                    $mediaIds = json_decode($stage->media_required, true);
                                    if (is_array($mediaIds) && count($mediaIds) > 0) {
                                        $mediaNames = \App\LabSubCategory::whereIn('id', $mediaIds)
                                            ->pluck('name')
                                            ->toArray();
                                        $stage->media_names = implode(', ', $mediaNames);
                                    } else {
                                        $stage->media_names = 'None';
                                    }
                                } catch (Exception $e) {
                                    $stage->media_names = $stage->media_required;
                                }
                            } else {
                                $stage->media_names = 'None';
                            }

                            // Process equipment required
                            if ($stage->equipment_required) {
                                try {
                                    $equipmentIds = json_decode($stage->equipment_required, true);
                                    if (is_array($equipmentIds) && count($equipmentIds) > 0) {
                                        $equipmentNames = \App\Models\Equipments\Equipment::whereIn('id', $equipmentIds)
                                            ->get()
                                            ->map(function ($equipment) {
                                                return $equipment->name . ' (' . $equipment->equipment_number . ')';
                                            })
                                            ->toArray();
                                        $stage->equipment_names = implode(', ', $equipmentNames);
                                    } else {
                                        $stage->equipment_names = 'None';
                                    }
                                } catch (Exception $e) {
                                    $stage->equipment_names = $stage->equipment_required;
                                }
                            } else {
                                $stage->equipment_names = 'None';
                            }

                            return $stage;
                        });
                    }
                }
                return $stageHeader;
            })
            ->filter() // Remove null values
            ->values();

        return response()->json($stageHeaders);
    }

    /**
     * Display method sequences for a batch
     */
    public function methodSequences($batchId)
    {
        SampleHeader::findOrFail($batchId);

        return redirect()->route('batch-worksheets', [
            'batch' => $batchId,
            'tab' => 'method-sequences',
        ]);
    }

    /**
     * Get samples for a stage header in a batch
     */
    public function getMethodSequenceSamples(Request $request, $batchId, $stageHeaderId)
    {
        \App\Models\StageHeader::findOrFail($stageHeaderId);

        // Current batch: all samples with captured results for this stage header (may be in multiple runs)
        $currentSamples = CapturedResult::query()
            ->where('stage_header_id', $stageHeaderId)
            ->where('sample_header_id', $batchId)
            ->with('sample:id,sample_code')
            ->get()
            ->pluck('sample')
            ->filter()
            ->unique('id')
            ->values()
            ->map(fn ($sample) => [
                'id' => $sample->id,
                'sample_code' => $sample->sample_code,
            ])
            ->values();

        // Other batches: same stage header, in lab (samples may already be in other runs)
        $otherSamples = CapturedResult::query()
            ->where('stage_header_id', $stageHeaderId)
            ->where('sample_header_id', '!=', $batchId)
            ->whereHas('sampleHeader', function ($q) {
                $q->where('status', 'Samples In Lab');
            })
            ->with(['sample:id,sample_code', 'sampleHeader:id,batch_code'])
            ->get()
            ->map(function ($cr) {
                return [
                    'id' => $cr->sample->id,
                    'sample_code' => $cr->sample->sample_code,
                    'display_code' => $cr->sample->sample_code.' ('.($cr->sampleHeader->batch_code ?? 'N/A').')',
                ];
            })
            ->unique('id')
            ->values();

        return response()->json([
            'current' => $currentSamples,
            'other' => $otherSamples,
            'messages' => [
                'current_empty' => $currentSamples->isEmpty()
                    ? 'No samples in this batch are configured for this method sequence.'
                    : null,
            ],
        ]);
    }

    /**
     * Auto-create first run for all stage headers in a batch
     */
    public function autoCreateFirstRunForBatch(Request $request, $batchId)
    {
        try {
            $batch = SampleHeader::findOrFail($batchId);
            \Log::info('=== START AutoCreateFirstRunForBatch for Batch: ' . $batchId . ' (' . $batch->batch_code . ') ===');

            // Get all stage headers for this batch
            $stageHeaders = \App\Models\StageHeader::whereHas('capturedResults', function($q) use ($batchId) {
                $q->whereHas('sample', function($sq) use ($batchId) {
                    $sq->where('sample_header_id', $batchId);
                });
            })->get();

            $createdRuns = [];

            // Get all sample IDs for this batch
            $batchSampleIds = \App\SampleDetail::where('sample_header_id', $batchId)->pluck('id')->toArray();
            \Log::info('AutoCreateFirstRunForBatch: Batch ID: ' . $batchId . ', Batch Code: ' . $batch->batch_code . ', Sample IDs: ' . json_encode($batchSampleIds));
            
            // Load sample details for logging
            $batchSampleDetails = \App\SampleDetail::whereIn('id', $batchSampleIds)->with('sampleHeader')->get();
            \Log::info('AutoCreateFirstRunForBatch: Sample Details: ' . $batchSampleDetails->map(function($s) { return $s->id . ' (' . $s->code . ')'; })->implode(', '));

            foreach ($stageHeaders as $stageHeader) {
                \Log::info('  Processing Stage Header: ' . $stageHeader->id . ' (' . $stageHeader->name . ')');
                
                // Check if run already exists for this stage header
                $existingRun = \App\Models\StageHeaderRun::where('stage_header_id', $stageHeader->id)
                    ->whereHas('trackRecords.sampleDetail', function($q) use ($batchId) {
                        $q->where('sample_header_id', $batchId);
                    })
                    ->exists();

                if (!$existingRun) {
                    // Only use samples from this batch for this stage header
                    $sampleIds = CapturedResult::where('stage_header_id', $stageHeader->id)
                        ->whereIn('sample_detail_id', $batchSampleIds)
                        ->pluck('sample_detail_id')
                        ->unique()
                        ->values()
                        ->toArray();
                    
                    \Log::info('  AutoCreateFirstRunForBatch Stage: Stage Header ID: ' . $stageHeader->id . ', Batch Sample IDs: ' . json_encode($batchSampleIds) . ', Filtered Sample IDs: ' . json_encode($sampleIds));
                    
                    // Verify the samples belong to this batch
                    $verifyBatch = \App\SampleDetail::whereIn('id', $sampleIds)->pluck('sample_header_id')->unique()->toArray();
                    \Log::info('  Verification: Sample IDs belong to batch headers: ' . json_encode($verifyBatch));

                    if (!empty($sampleIds)) {
                        // Create run with only current batch samples
                        $run = \App\Models\StageHeaderRun::create([
                            'stage_header_id' => $stageHeader->id,
                            'user_id' => auth()->id(),
                        ]);

                        // Create track records for each test stage
                        foreach ($stageHeader->testStages as $testStage) {
                            foreach ($sampleIds as $sampleId) {
                                // Get the captured result for this sample/stage combination
                                $capturedResult = CapturedResult::where('stage_header_id', $stageHeader->id)
                                    ->where('sample_detail_id', $sampleId)
                                    ->first();

                                if ($capturedResult) {
                                    \App\Models\SampleCapturedTestStagesTrack::create([
                                        'stage_header_run_id' => $run->id,
                                        'captured_result_id' => $capturedResult->id,
                                        'sample_detail_id' => $sampleId,
                                        'stage_header_id' => $stageHeader->id,
                                        'test_stage_id' => $testStage->id,
                                        'status' => 'pending',
                                        'equipment_data' => $testStage->equipment_required,
                                        'media_data' => $testStage->media_required,
                                        'controls_data' => $testStage->controls_required,
                                        'diluents_data' => $testStage->diluents_required,
                                    ]);
                                }
                            }
                        }

                        $createdRuns[] = [
                            'stage_header_id' => $stageHeader->id,
                            'run_id' => $run->id,
                            'samples_count' => count($sampleIds),
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($createdRuns) . ' run(s) created automatically',
                'created_runs' => $createdRuns,
            ]);

        } catch (\Exception $e) {
            Log::error('Error auto-creating runs: ' . $e->getMessage(), [
                'batch_id' => $batchId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating runs: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ensure a run and stage track records exist for this stage header and batch.
     */
    protected function ensureStageTracks(string $stageHeaderId, string $batchId): void
    {
        $stageHeader = \App\Models\StageHeader::with('testStages')->find($stageHeaderId);

        if (! $stageHeader || $stageHeader->testStages->isEmpty()) {
            return;
        }

        $batchSampleIds = \App\SampleDetails::where('sample_header_id', $batchId)->pluck('id')->toArray();

        if (empty($batchSampleIds)) {
            return;
        }

        $existingRun = \App\Models\StageHeaderRun::where('stage_header_id', $stageHeaderId)
            ->whereHas('trackRecords.sampleDetail', function ($q) use ($batchId) {
                $q->where('sample_header_id', $batchId);
            })
            ->exists();

        if ($existingRun) {
            return;
        }

        $sampleIds = CapturedResult::where('stage_header_id', $stageHeaderId)
            ->whereIn('sample_detail_id', $batchSampleIds)
            ->pluck('sample_detail_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($sampleIds)) {
            return;
        }

        $run = \App\Models\StageHeaderRun::create([
            'stage_header_id' => $stageHeaderId,
            'user_id' => Auth::id(),
        ]);

        foreach ($stageHeader->testStages as $testStage) {
            foreach ($sampleIds as $sampleId) {
                $capturedResult = CapturedResult::where('stage_header_id', $stageHeaderId)
                    ->where('sample_detail_id', $sampleId)
                    ->first();

                if (! $capturedResult) {
                    continue;
                }

                \App\Models\SampleCapturedTestStagesTrack::create([
                    'stage_header_run_id' => $run->id,
                    'captured_result_id' => $capturedResult->id,
                    'sample_detail_id' => $sampleId,
                    'stage_header_id' => $stageHeaderId,
                    'test_stage_id' => $testStage->id,
                    'status' => 'pending',
                    'equipment_data' => $testStage->equipment_required,
                    'media_data' => $testStage->media_required,
                    'controls_data' => $testStage->controls_required,
                    'diluents_data' => $testStage->diluents_required,
                ]);
            }
        }
    }

    /**
     * Get runs for a stage header
     */
    public function getMethodSequenceRuns(Request $request, $stageHeaderId)
    {
        // Stage 2 of auto-creation: ensure runs and tracking records exist
        $batchId = $request->query('batch_id');
        if ($stageHeaderId && $batchId) {
            $this->ensureStageTracks($stageHeaderId, $batchId);
        }

        $query = \App\Models\StageHeaderRun::where('stage_header_id', $stageHeaderId);

        if ($batchId) {
            $query->whereHas('trackRecords.sampleDetail', function($q) use ($batchId) {
                $q->where('sample_header_id', $batchId);
            });
        }

        $runs = $query->with([
                'user',
                'trackRecords.testStage',
                'trackRecords.sampleDetail',
                'trackRecords.user',
                'trackRecords.readBy',
                'trackRecords.endedBy',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        // Filter trackRecords to only include samples from the current batch
        if ($batchId) {
            \Log::info("getMethodSequenceRuns: BatchID $batchId, Found " . $runs->count() . " runs");
            foreach ($runs as $run) {
                $allTracks = $run->trackRecords->count();
                $run->trackRecords = $run->trackRecords->filter(function($track) use ($batchId) {
                    $belongs = $track->sampleDetail && $track->sampleDetail->sample_header_id == $batchId;
                    if (!$belongs && $track->sampleDetail) {
                        \Log::warning("getMethodSequenceRuns: Filtering OUT track {$track->id} - belongs to batch {$track->sampleDetail->sample_header_id}, not $batchId");
                    }
                    return $belongs;
                })->values();
                $filteredTracks = $run->trackRecords->count();
                \Log::info("getMethodSequenceRuns: Run {$run->id} - All tracks: $allTracks, Filtered tracks: $filteredTracks");
            }
        }

        // For each run, enhance test_stage data with names and user-edited data
        foreach ($runs as $run) {
            foreach ($run->trackRecords as $track) {
                // If user has edited equipment data, load the names
                if ($track->equipment_data && isset($track->equipment_data['equipment_ids'])) {
                    $equipmentIds = $track->equipment_data['equipment_ids'];
                    $equipmentNames = \App\Models\Equipments\Equipment::whereIn('id', $equipmentIds)->pluck('name')->toArray();
                    $equipmentData = $track->equipment_data;
                    $equipmentData['equipment_names'] = $equipmentNames;
                    $track->equipment_data = $equipmentData;
                }

                // If user has edited media data, load the names
                if ($track->media_data && isset($track->media_data['media_ids'])) {
                    $mediaIds = $track->media_data['media_ids'];
                    $mediaNames = \App\LabSubCategory::whereIn('id', $mediaIds)->pluck('name')->toArray();
                    $mediaData = $track->media_data;
                    $mediaData['media_names'] = $mediaNames;
                    $track->media_data = $mediaData;
                }
                
                // If user has edited controls data, load the names
                if ($track->controls_data && isset($track->controls_data['controls_ids'])) {
                    $controlsIds = $track->controls_data['controls_ids'];
                    $controlsNames = \App\LabSubCategory::whereIn('id', $controlsIds)->pluck('name')->toArray();
                    $controlsData = $track->controls_data;
                    $controlsData['controls_names'] = $controlsNames;
                    $track->controls_data = $controlsData;
                }
                
                if ($track->testStage) {
                    // Parse and fetch equipment names
                    $equipmentData = $track->testStage->equipment_required ?? [];
                    if (!is_array($equipmentData)) {
                        $equipmentData = json_decode($equipmentData, true) ?? [];
                    }
                    $equipmentIds = collect($equipmentData)->map(function($item) {
                        return is_array($item) ? ($item['id'] ?? $item[0] ?? null) : $item;
                    })->filter()->toArray();

                    if (count($equipmentIds) > 0) {
                        $track->testStage->equipment_names = \App\Models\Equipments\Equipment::whereIn('id', $equipmentIds)->pluck('name')->toArray();
                    } else {
                        $track->testStage->equipment_names = [];
                    }
                    
                    // Parse and fetch media names
                    $mediaData = $track->testStage->media_required ?? [];
                    if (!is_array($mediaData)) {
                        $mediaData = json_decode($mediaData, true) ?? [];
                    }
                    $mediaIds = collect($mediaData)->map(function($item) {
                        return is_array($item) ? ($item['id'] ?? $item[0] ?? null) : $item;
                    })->filter()->toArray();

                    if (count($mediaIds) > 0) {
                        $track->testStage->media_names = \App\LabSubCategory::whereIn('id', $mediaIds)->pluck('name')->toArray();
                    } else {
                        $track->testStage->media_names = [];
                    }
                    
                    // Parse and fetch controls names
                    $controlsData = $track->testStage->controls_required ?? [];
                    if (!is_array($controlsData)) {
                        $controlsData = json_decode($controlsData, true) ?? [];
                    }
                    $controlsIds = collect($controlsData)->map(function($item) {
                        return is_array($item) ? ($item['id'] ?? $item[0] ?? null) : $item;
                    })->filter()->toArray();

                    if (count($controlsIds) > 0) {
                        $track->testStage->controls_names = \App\LabSubCategory::whereIn('id', $controlsIds)->pluck('name')->toArray();
                    } else {
                        $track->testStage->controls_names = [];
                    }
                }
            }
        }

        return response()->json($runs);
    }

    /**
     * Create a new run
     */
    public function createMethodSequenceRun(Request $request)
    {
        $request->validate([
            'stage_header_id' => 'required|exists:stage_headers,id',
            'sample_ids' => 'required|array|min:1',
            'sample_ids.*' => 'exists:sample_details,id',
        ]);

        DB::beginTransaction();
        try {
            // Create the run
            $run = \App\Models\StageHeaderRun::create([
                'stage_header_id' => $request->stage_header_id,
                'user_id' => auth()->id(),
            ]);

            // Get all test stages for this stage header
            $testStages = \App\Models\TestStage::where('stage_header_id', $request->stage_header_id)
                ->orderBy('order')
                ->get();

            // Create tracking records for each sample and each test stage
            foreach ($request->sample_ids as $sampleId) {
                // Get or create captured results for this sample and stage header
                $capturedResults = CapturedResult::where('sample_detail_id', $sampleId)
                    ->where('stage_header_id', $request->stage_header_id)
                    ->get();

                // If no captured results exist, create one for this stage header
                if ($capturedResults->isEmpty()) {
                    $stageHeader = \App\Models\StageHeader::findOrFail($request->stage_header_id);
                    $sample = \App\SampleDetails::findOrFail($sampleId);

                    // Create a captured result for this sample and stage header
                    $capturedResult = CapturedResult::create([
                        'sample_detail_id' => $sampleId,
                        'sample_header_id' => $sample->sample_header_id,
                        'stage_header_id' => $request->stage_header_id,
                        'analyte_id' => $stageHeader->analyte_id,
                        'analysis_type_id' => null, // Will be set when posting results
                        'result' => null,
                        'status' => 'pending',
                    ]);
                    $capturedResults = collect([$capturedResult]);
                }

                foreach ($capturedResults as $capturedResult) {
                    foreach ($testStages as $testStage) {
                        \App\Models\SampleCapturedTestStagesTrack::create([
                            'stage_header_run_id' => $run->id,
                            'captured_result_id' => $capturedResult->id,
                            'sample_detail_id' => $sampleId,
                            'stage_header_id' => $request->stage_header_id,
                            'test_stage_id' => $testStage->id,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'run' => $run]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a method sequence run (and its track records via cascade).
     */
    public function deleteMethodSequenceRun(Request $request, $runId)
    {
        try {
            $run = \App\Models\StageHeaderRun::findOrFail($runId);
            $run->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            logger()->error('Error deleting method sequence run: ' . $e->getMessage(), [
                'run_id' => $runId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error deleting run: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start a stage
     */
    public function startMethodSequenceStage(Request $request, $trackId)
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be logged in to start a stage.',
                ], 403);
            }

            // Get the track to find its stage and run
            $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);
            $testStageId = $track->test_stage_id;
            $runId = $track->stage_header_run_id;

            // Use direct DB update to avoid Eloquent events and serialization issues
            // Only update columns that exist in the database
            $startTime = now();

            // Ensure we're in a transaction and commit it explicitly
            DB::beginTransaction();
            try {
                // Start ALL pending tracks for this stage in this run (all samples together)
                $updatedCount = DB::table('sample_captured_test_stages_track')
                    ->where('stage_header_run_id', $runId)
                    ->where('test_stage_id', $testStageId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'running',
                        'started_at' => $startTime,
                        'ended_at' => null,
                        'ended_by' => null,
                        'user_id' => $userId,
                        'updated_at' => now(),
                    ]);

                // Commit the transaction explicitly to ensure data is persisted
                DB::commit();

                // Verify the update was successful by checking the database
                $updatedStatus = DB::table('sample_captured_test_stages_track')
                    ->where('id', $trackId)
                    ->value('status');

                if ($updatedStatus !== 'running') {
                    throw new \Exception('Status update verification failed. Expected "running", got: ' . $updatedStatus);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Return success immediately
            return response()->json([
                'success' => true,
                'status' => $updatedStatus, // Include status in response for debugging
                'updated_count' => $updatedCount ?? 0, // Number of tracks updated
            ], 200);
        } catch (\Exception $e) {
            logger()->error('Error starting method sequence stage: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error starting stage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End a stage
     */
    public function endMethodSequenceStage(Request $request, $trackId)
    {
        try {
            $userId = auth()->id();
            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User must be logged in to end a stage.',
                ], 403);
            }

            // Get the track to find its stage and run
            $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);
            $testStageId = $track->test_stage_id;
            $runId = $track->stage_header_run_id;

            // Use direct DB update to avoid Eloquent events and serialization issues
            // Only update columns that exist in the database
            // End ALL running tracks for this stage in this run (all samples together)
            DB::beginTransaction();
            try {
                $updatedCount = DB::table('sample_captured_test_stages_track')
                    ->where('stage_header_run_id', $runId)
                    ->where('test_stage_id', $testStageId)
                    ->whereIn('status', ['running', 'overdue'])
                    ->update([
                        'status' => 'completed',
                        'ended_at' => now(),
                        'ended_by' => $userId,
                        'updated_at' => now(),
                    ]);

                // Commit the transaction explicitly to ensure data is persisted
                DB::commit();

                // Verify the update was successful
                $updatedStatus = DB::table('sample_captured_test_stages_track')
                    ->where('id', $trackId)
                    ->value('status');

                if ($updatedStatus !== 'completed') {
                    throw new \Exception('Status update verification failed. Expected "completed", got: ' . $updatedStatus);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

            // Return success immediately
            return response()->json([
                'success' => true,
                'status' => $updatedStatus,
                'updated_count' => $updatedCount ?? 0, // Number of tracks updated
            ], 200);
        } catch (\Exception $e) {
            logger()->error('Error ending method sequence stage: ' . $e->getMessage(), [
                'track_id' => $trackId,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error ending stage: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stage data
     */
    public function updateMethodSequenceStageData(Request $request, $trackId)
    {
        $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);

        if (in_array($track->status, ['completed'], true) || $track->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'This stage is completed and cannot be edited.',
            ], 422);
        }

        // Step 5 Validation: Ensure at least one item type (equipment, media, controls, or diluents) is added
        $hasEquipment = !empty($request->equipment_items) || !empty($request->equipment_ids);
        $hasMedia = !empty($request->media_items) || !empty($request->media_ids);
        $hasControls = !empty($request->controls_items) || !empty($request->controls_ids);
        $hasDiluents = !empty($request->diluents_items) || !empty($request->diluents_ids);

        // Allow saving of ANY single data type independently
        if (!$hasEquipment && !$hasMedia && !$hasControls && !$hasDiluents) {
            return response()->json([
                'success' => false,
                'message' => 'Please add at least one equipment, media, control, or diluent item'
            ], 422);
        }

        $updateData = [];

        // ===== SMART MERGE: Compare DB data with DOM data =====
        // Save if: (1) new items added, (2) items removed, or (3) DB has legacy format (upgrade to full objects with metadata)
        
        // Helper function to extract IDs from database JSON data
        $extractIds = function($jsonData) {
            if (empty($jsonData)) return [];
            $data = is_string($jsonData) ? json_decode($jsonData, true) : $jsonData;
            if (!is_array($data)) return [];
            
            // If it's in legacy format: ["31", "32"] (just IDs as strings)
            if (isset($data[0]) && is_string($data[0])) {
                return array_map('intval', $data);
            }
            
            // If it's new format: [{id: 31, ...}, {id: 32, ...}] (objects with item data)
            if (isset($data['equipment_items']) || isset($data['controls_items']) || 
                isset($data['media_items']) || isset($data['diluents_items'])) {
                $items = $data['equipment_items'] ?? $data['controls_items'] ?? 
                         $data['media_items'] ?? $data['diluents_items'] ?? [];
                return array_map(function($item) { return (int)$item['id']; }, $items);
            }
            
            return [];
        };

        // Helper to detect if DB has legacy format (ID-only strings without metadata)
        $hasLegacyFormat = function($jsonData) {
            if (empty($jsonData)) return false;
            $data = is_string($jsonData) ? json_decode($jsonData, true) : $jsonData;
            if (!is_array($data)) return false;
            
            // Check if it's an array of ID strings: ["31", "32"] (no metadata)
            return isset($data[0]) && is_string($data[0]) && is_numeric($data[0]);
        };

        // Helper to decide if we should update
        $shouldUpdate = function($dbData, $requestItems) use ($extractIds, $hasLegacyFormat) {
            if (empty($requestItems)) return false; // No items to save
            
            $dbIds = $extractIds($dbData);
            $requestIds = array_map(function($item) { return (int)$item['id']; }, $requestItems);
            
            sort($dbIds);
            sort($requestIds);
            
            // Different ID sets = new items added or removed → UPDATE
            if ($dbIds !== $requestIds) {
                return true;
            }
            
            // ALWAYS update if DB has legacy format (just IDs without metadata)
            // This ensures we capture serial numbers, prep dates, remarks, calibration, etc.
            // Even if IDs match, the DOM data has metadata that needs to be saved
            if ($hasLegacyFormat($dbData)) {
                return true;
            }
            
            return false; // Same IDs and DB already has full object data with metadata → skip
        };

        // Equipment: save if IDs differ OR if DB has legacy format
        if ($shouldUpdate($track->equipment_data, $request->equipment_items ?? [])) {
            if ($request->equipment_items) {
                $updateData['equipment_data'] = ['equipment_items' => $request->equipment_items];
            }
        }

        // Controls: save if IDs differ OR if DB has legacy format
        if ($shouldUpdate($track->controls_data, $request->controls_items ?? [])) {
            if ($request->controls_items) {
                $controlsItems = array_map(function($item) {
                    return [
                        'id' => (int)$item['id'],
                        'result_nature' => $item['result_nature'] ?? 'none',
                        'is_mandatory' => $item['is_mandatory'] ?? '1',
                        'preparation' => $item['preparation'] ?? '',
                        'expiry' => $item['expiry'] ?? '',
                    ];
                }, $request->controls_items);
                $updateData['controls_data'] = ['controls_items' => $controlsItems];
            }
        }

        // Media: save if IDs differ OR if DB has legacy format
        if ($shouldUpdate($track->media_data, $request->media_items ?? [])) {
            if ($request->media_items) {
                $mediaItems = array_map(function($item) {
                    return [
                        'id' => (int)$item['id'],
                        'result_nature' => $item['result_nature'] ?? 'none',
                        'is_mandatory' => $item['is_mandatory'] ?? '1',
                        'preparation' => $item['preparation'] ?? '',
                        'preparation_number' => $item['preparation_number'] ?? '',
                    ];
                }, $request->media_items);
                $updateData['media_data'] = ['media_items' => $mediaItems];
            }
        }

        // Diluents: save if IDs differ OR if DB has legacy format
        if ($shouldUpdate($track->diluents_data, $request->diluents_items ?? [])) {
            if ($request->diluents_items) {
                $diluentsItems = array_map(function($item) {
                    return [
                        'id' => (int)$item['id'],
                        'result_nature' => $item['result_nature'] ?? 'none',
                        'is_mandatory' => $item['is_mandatory'] ?? '1',
                        'preparation_date' => $item['preparation_date'] ?? '',
                        'preparation_number' => $item['preparation_number'] ?? '',
                        'expiry' => $item['expiry'] ?? '',
                    ];
                }, $request->diluents_items);
                $updateData['diluents_data'] = ['diluents_items' => $diluentsItems];
            }
        }

        // Save timestamps
        if ($request->started_at) {
            $updateData['started_at'] = $request->started_at;
        }
        if ($request->ended_at) {
            $updateData['ended_at'] = $request->ended_at;
        }

        $track->update($updateData);

        return response()->json(['success' => true, 'track' => $track->fresh()->load(['endedBy', 'user'])]);
    }

    /**
     * Save sample and solution results (Step 6) - to staging tables and track JSON columns
     * Saves to TrackSampleResult (editable), TrackMediaResult, TrackControlResult
     * Also updates media_data, controls_data, diluents_data JSON columns with results/remarks
     * Updates main track record with metadata (results_posted_at, reading_date, read_by, etc)
     * NOT to CapturedResult (that happens only on POST)
     */
    public function saveMethodSequenceResults(Request $request, $trackId)
    {
        $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);

        if (in_array($track->status, ['completed'], true) || $track->ended_at) {
            return response()->json([
                'success' => false,
                'message' => 'This stage is completed and cannot be edited.',
            ], 422);
        }
        
        $sampleResults = $request->sample_results ?? [];
        $mediaResults = $request->media_results ?? [];
        $controlResults = $request->control_results ?? [];
        $diluentResults = $request->diluent_results ?? [];
        
        // Validate that results are provided
        if (empty($sampleResults) && empty($mediaResults) && empty($controlResults) && empty($diluentResults)) {
            return response()->json([
                'success' => false,
                'message' => 'No results provided'
            ], 422);
        }
        
        try {
            DB::transaction(function () use ($trackId, $sampleResults, $mediaResults, $controlResults, $diluentResults, $track) {
                // *** SAVE SAMPLE RESULTS TO STAGING TABLE (TrackSampleResult) ***
                // NOT to CapturedResult - that only happens on POST
                foreach ($sampleResults as $result) {
                    $capturedResult = \App\CapturedResult::find($result['captured_result_id']);
                    if (!$capturedResult) {
                        continue;
                    }

                    // Find the correct track for this sample
                    $sampleTrack = \App\Models\SampleCapturedTestStagesTrack::where('stage_header_run_id', $track->stage_header_run_id)
                        ->where('test_stage_id', $track->test_stage_id)
                        ->where('captured_result_id', $result['captured_result_id'])
                        ->first();
                    
                    $correctTrackId = $sampleTrack ? $sampleTrack->id : $track->id;

                    // Determine if remark should be auto-calculated or manual
                    $remark = '';
                    $isAutoCalculated = false;
                    
                    if (!empty($result['result'])) {
                        $resultRemarkService = app(\App\Services\ResultRemarkService::class);
                        $shouldAutoCalculate = $resultRemarkService->shouldAutoCalculateRemark(
                            $capturedResult,
                            $result['reporting_symbol'] ?? null
                        );

                        if ($shouldAutoCalculate) {
                            $remark = $resultRemarkService->calculateResultRemark(
                                $capturedResult,
                                $result['result'],
                                $result['reporting_symbol'] ?? null
                            );
                            $isAutoCalculated = true;
                        } else {
                            $remark = $result['remark'] ?? '';
                            $isAutoCalculated = false;
                        }
                    } else {
                        $remark = $result['remark'] ?? '';
                    }

                    // Save to TrackSampleResult (editable staging) with all fields including reporting_unit
                    \App\Models\TrackSampleResult::updateOrCreate(
                        [
                            'track_id' => $correctTrackId,
                            'captured_result_id' => $result['captured_result_id'],
                        ],
                        [
                            'reporting_unit' => $result['reporting_unit'] ?? $capturedResult->unit ?? null,
                            'result' => $result['result'] ?? null,
                            'standard_limit' => $result['standard_limit'] ?? null,
                            'remark' => $remark,
                            'remark_is_auto_calculated' => $isAutoCalculated,
                            'reporting_symbol' => $result['reporting_symbol'] ?? null,
                            'analyst_id' => auth()->id(),
                            'recorded_at' => now(),
                        ]
                    );

                    // Update the underlying track's result field so it appears in the Post Results modal
                    if ($sampleTrack) {
                        $sampleTrack->updateResult($result['result'] ?? '', $remark, auth()->id());
                    }
                }
                
                // Update media_data JSON column with result and remark for each media item
                $updatedMediaData = $track->media_data ?? [];
                if (isset($updatedMediaData['media_items']) && is_array($updatedMediaData['media_items'])) {
                    foreach ($updatedMediaData['media_items'] as &$mediaItem) {
                        $mediaId = $mediaItem['id'];
                        $mediaResult = collect($mediaResults)->firstWhere('media_id', $mediaId);
                        if ($mediaResult) {
                            $mediaItem['result'] = $mediaResult['result'] ?? '';
                            $mediaItem['remark'] = $mediaResult['remark'] ?? '';
                        } else {
                            $mediaItem['result'] = '';
                            $mediaItem['remark'] = '';
                        }
                    }
                    unset($mediaItem); // Unset reference
                }
                
                // Save media results to track_media_results table
                foreach ($mediaResults as $result) {
                    if (!empty($result['media_id']) && !empty($result['result']) && ($result['result_nature'] ?? null) !== 'none') {
                        \App\Models\TrackMediaResult::updateOrCreate(
                            [
                                'track_id' => $trackId,
                                'media_id' => $result['media_id'],
                            ],
                            [
                                'result' => $result['result'],
                                'remark' => $result['remark'] ?? '',
                                'analyst_id' => auth()->id(),
                                'recorded_at' => now(),
                            ]
                        );
                    }
                }
                
                // Update controls_data JSON column with result and remark for each control item
                $updatedControlsData = $track->controls_data ?? [];
                if (isset($updatedControlsData['controls_items']) && is_array($updatedControlsData['controls_items'])) {
                    foreach ($updatedControlsData['controls_items'] as &$controlItem) {
                        $controlId = $controlItem['id'];
                        $controlResult = collect($controlResults)->firstWhere('control_id', $controlId);
                        if ($controlResult) {
                            $controlItem['result'] = $controlResult['result'] ?? '';
                            $controlItem['remark'] = $controlResult['remark'] ?? '';
                        } else {
                            $controlItem['result'] = '';
                            $controlItem['remark'] = '';
                        }
                    }
                    unset($controlItem); // Unset reference
                }
                
                // Save control results to track_control_results table
                foreach ($controlResults as $result) {
                    if (!empty($result['control_id']) && !empty($result['result']) && ($result['result_nature'] ?? null) !== 'none') {
                        \App\Models\TrackControlResult::updateOrCreate(
                            [
                                'track_id' => $trackId,
                                'control_id' => $result['control_id'],
                            ],
                            [
                                'result' => $result['result'],
                                'remark' => $result['remark'] ?? '',
                                'analyst_id' => auth()->id(),
                                'recorded_at' => now(),
                            ]
                        );
                    }
                }
                
                // Update diluents_data JSON column with result and preparation_number for each diluent item
                $updatedDiluentData = $track->diluents_data ?? [];
                if (isset($updatedDiluentData['diluents_items']) && is_array($updatedDiluentData['diluents_items'])) {
                    foreach ($updatedDiluentData['diluents_items'] as &$diluentItem) {
                        $diluentId = $diluentItem['id'];
                        $diluentResult = collect($diluentResults)->firstWhere('diluent_id', $diluentId);
                        if ($diluentResult) {
                            $diluentItem['result'] = $diluentResult['result'] ?? '';
                            $diluentItem['preparation_number'] = $diluentResult['preparation_number'] ?? '';
                        } else {
                            $diluentItem['result'] = '';
                            $diluentItem['preparation_number'] = '';
                        }
                    }
                    unset($diluentItem); // Unset reference
                }
                
                // Update main track record with results metadata
                // Aggregate sample results with sample ID
                $sampleResultsData = array_map(function($r) { 
                    return [
                        'sample_id' => $r['captured_result_id'] ?? null,
                        'result' => $r['result'] ?? null,
                    ];
                }, $sampleResults);
                
                // Aggregate sample remarks with sample ID
                $sampleRemarksData = array_map(function($r) { 
                    return [
                        'sample_id' => $r['captured_result_id'] ?? null,
                        'remark' => $r['remark'] ?? '',
                    ];
                }, $sampleResults);
                
                $track->update([
                    'result' => !empty($sampleResultsData) ? json_encode($sampleResultsData) : null,
                    'remarks' => !empty($sampleRemarksData) ? json_encode($sampleRemarksData) : null,
                    'reading_date' => now(),
                    'results_posted_at' => now(),
                    'results_posted_by' => auth()->id(),
                    'read_by' => auth()->id(),
                    'media_data' => $updatedMediaData,
                    'controls_data' => $updatedControlsData,
                    'diluents_data' => $updatedDiluentData,
                ]);
            });
            
            return response()->json(['success' => true, 'message' => 'Results saved successfully']);
        } catch (\Exception $e) {
            \Log::error('Error in saveMethodSequenceResults', [
                'track_id' => $trackId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error saving results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get equipment, media, and controls for edit modal
     */
    public function getEditStageData(Request $request, $trackId): \Illuminate\Http\JsonResponse
    {
        $track = \App\Models\SampleCapturedTestStagesTrack::with(['testStage', 'user', 'readBy', 'endedBy'])->findOrFail($trackId);

        // ===== GET ALL AVAILABLE ITEMS FOR DROPDOWNS =====
        
        // Equipment with calibration metadata
        $equipment = \App\Models\Equipments\Equipment::where('active', 1)
            ->get(['id', 'name', 'equipment_number', 'date_purchased', 'calibration_days'])
            ->map(function ($eq) {
                $logs = $eq->last_logs();
                $calibrationDate = null;
                if (isset($logs['calibration']) && $logs['calibration']) {
                    $calibrationDate = $logs['calibration']->date;
                } else {
                    $calibrationDate = $eq->date_purchased;
                }
                return [
                    'id' => $eq->id,
                    'name' => $eq->name,
                    'equipment_number' => $eq->equipment_number ?? '',
                    'last_calibration_date' => $calibrationDate ? \Carbon\Carbon::parse($calibrationDate)->format('Y-m-d') : null
                ];
            })
            ->values()
            ->toArray();

        // Media with preparation metadata
        $mediaCategoryId = \App\Models\System\SystemConfiguration::where('key', 'media_solution_type_id')->value('value');
        $media = $mediaCategoryId
            ? \App\LabSubCategory::where('category_id', $mediaCategoryId)->where('active', 1)
                ->get(['id', 'name', 'batch_prepared_date', 'current_batch_number'])
                ->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'latest_prep_date' => $m->batch_prepared_date ? \Carbon\Carbon::parse($m->batch_prepared_date)->format('Y-m-d') : null,
                        'latest_prep_number' => $m->current_batch_number ?? ''
                    ];
                })
                ->values()
                ->toArray()
            : [];

        // Controls
        $controlCategoryId = \App\Models\System\SystemConfiguration::where('key', 'control_solution_type_id')->value('value');
        $controls = $controlCategoryId
            ? \App\LabSubCategory::where('category_id', $controlCategoryId)->where('active', 1)
                ->get(['id', 'name', 'batch_prepared_date', 'batch_expiry_date', 'current_batch_number'])
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'batch_number' => $c->current_batch_number ?? '',
                        'expiry_date' => $c->batch_expiry_date ? \Carbon\Carbon::parse($c->batch_expiry_date)->format('Y-m-d') : null
                    ];
                })
                ->values()
                ->toArray()
            : [];

        // Diluents (matching media structure for consistency)
        $diluentCategoryId = \App\Models\System\SystemConfiguration::where('key', 'diluent_solution_type_id')->value('value');
        $diluents = $diluentCategoryId
            ? \App\LabSubCategory::where('category_id', $diluentCategoryId)->where('active', 1)
                ->get(['id', 'name', 'batch_prepared_date', 'current_batch_number'])
                ->map(function ($d) {
                    return [
                        'id' => $d->id,
                        'name' => $d->name,
                        'latest_prep_date' => $d->batch_prepared_date ? \Carbon\Carbon::parse($d->batch_prepared_date)->format('Y-m-d') : null,
                        'latest_prep_number' => $d->current_batch_number ?? ''
                    ];
                })
                ->values()
                ->toArray()
            : [];

        // ===== GET CURRENTLY CONFIGURED ITEMS =====
        
        $currentItems = [
            'equipment' => [],
            'media' => [],
            'controls' => [],
            'diluents' => []
        ];

        // Build map of available items for lookup
        $equipmentMap = collect($equipment)->keyBy('id')->toArray();
        $mediaMap = collect($media)->keyBy('id')->toArray();
        $controlsMap = collect($controls)->keyBy('id')->toArray();
        $diluentsMap = collect($diluents)->keyBy('id')->toArray();

        // Equipment: from track data or test stage config
        $currentEquipmentIds = [];
        
        // First, try to load from saved equipment_items (new format with metadata)
        if ($track->equipment_data && isset($track->equipment_data['equipment_items']) && is_array($track->equipment_data['equipment_items'])) {
            // Use the saved equipment_items directly as they already have all metadata details
            foreach ($track->equipment_data['equipment_items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    $id = (string)$item['id'];
                    $name = 'Unknown Equipment';
                    
                    // Resolve name from equipmentMap
                    if (isset($equipmentMap[$id])) {
                        $name = (string)$equipmentMap[$id]['name'];
                    }
                    
                    $currentItems['equipment'][] = [
                        'id' => $id,
                        'name' => $name,
                        'serial' => (string)($item['serial'] ?? ''),
                        'calibration' => (string)($item['calibration'] ?? '')
                    ];
                }
            }
        }
        // Fall back to legacy equipment_ids format
        elseif ($track->equipment_data && isset($track->equipment_data['equipment_ids']) && is_array($track->equipment_data['equipment_ids'])) {
            $currentEquipmentIds = $track->equipment_data['equipment_ids'];
            
            // Helper to safely extract ID from scalar or object
            $extractId = function($item) {
                if (is_scalar($item)) {
                    return (string)$item;
                } elseif (is_object($item) && isset($item->id)) {
                    return (string)$item->id;
                } elseif (is_array($item) && isset($item['id'])) {
                    return (string)$item['id'];
                }
                return null;
            };

            foreach ($currentEquipmentIds as $equipId) {
                $id = $extractId($equipId);
                if ($id && isset($equipmentMap[$id])) {
                    $eq = $equipmentMap[$id];
                    $currentItems['equipment'][] = [
                        'id' => (string)$eq['id'],
                        'name' => (string)$eq['name'],
                        'serial' => (string)($eq['equipment_number'] ?? ''),
                        'calibration' => (string)($eq['last_calibration_date'] ?? '')
                    ];
                }
            }
        }
        // Fall back to test stage config
        elseif ($track->testStage && $track->testStage->equipment_required) {
            $equipmentIds = is_string($track->testStage->equipment_required) 
                ? json_decode($track->testStage->equipment_required, true) 
                : (array)$track->testStage->equipment_required;
            if (is_array($equipmentIds)) {
                $currentEquipmentIds = $equipmentIds;
                
                // Helper to safely extract ID from scalar or object
                $extractId = function($item) {
                    if (is_scalar($item)) {
                        return (string)$item;
                    } elseif (is_object($item) && isset($item->id)) {
                        return (string)$item->id;
                    } elseif (is_array($item) && isset($item['id'])) {
                        return (string)$item['id'];
                    }
                    return null;
                };

                foreach ($currentEquipmentIds as $equipId) {
                    $id = $extractId($equipId);
                    if ($id && isset($equipmentMap[$id])) {
                        $eq = $equipmentMap[$id];
                        $currentItems['equipment'][] = [
                            'id' => (string)$eq['id'],
                            'name' => (string)$eq['name'],
                            'serial' => (string)($eq['equipment_number'] ?? ''),
                            'calibration' => (string)($eq['last_calibration_date'] ?? '')
                        ];
                    }
                }
            }
        }

        // Media: from track data or test stage config
        $currentMediaIds = [];
        
        // First, try to load from saved media_items (new format with preparation details)
        if ($track->media_data && isset($track->media_data['media_items']) && is_array($track->media_data['media_items'])) {
            // Use the saved media_items directly as they already have all the details
            foreach ($track->media_data['media_items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    $id = (string)$item['id'];
                    $name = 'Unknown Media';
                    
                    // Resolve name from mediaMap
                    if (isset($mediaMap[$id])) {
                        $name = (string)$mediaMap[$id]['name'];
                    }
                    
                    $currentItems['media'][] = [
                        'id' => $id,
                        'name' => $name,
                        'preparation' => (string)($item['preparation'] ?? ''),
                        'preparation_number' => (string)($item['preparation_number'] ?? ''),
                        'result' => (string)($item['result'] ?? ''),
                        'result_nature' => (string)($item['result_nature'] ?? 'none')
                    ];
                }
            }
        }

        // Fall back to legacy media_ids format
        elseif ($track->media_data && isset($track->media_data['media_ids']) && is_array($track->media_data['media_ids'])) {
            $currentMediaIds = $track->media_data['media_ids'];
            foreach ($currentMediaIds as $mediaId) {
                $id = $extractId($mediaId);
                if ($id && isset($mediaMap[$id])) {
                    $m = $mediaMap[$id];
                    $currentItems['media'][] = [
                        'id' => (string)$m['id'],
                        'name' => (string)$m['name'],
                        'preparation' => (string)($m['latest_prep_date'] ?? ''),
                        'remark' => (string)($m['latest_prep_number'] ?? '')
                    ];
                }
            }
        }
        // Fall back to test stage config
        elseif ($track->testStage && $track->testStage->media_required) {
            $mediaIds = is_string($track->testStage->media_required) 
                ? json_decode($track->testStage->media_required, true) 
                : (array)$track->testStage->media_required;
            if (is_array($mediaIds)) {
                $currentMediaIds = $mediaIds;
            }
            foreach ($currentMediaIds as $mediaId) {
                $id = $extractId($mediaId);
                $resultNature = 'none';
                
                if (is_array($mediaId) && isset($mediaId['result_nature'])) {
                    $resultNature = (string)$mediaId['result_nature'];
                } elseif (is_object($mediaId) && isset($mediaId->result_nature)) {
                    $resultNature = (string)$mediaId->result_nature;
                }
                
                if ($id && isset($mediaMap[$id])) {
                    $m = $mediaMap[$id];
                    $currentItems['media'][] = [
                        'id' => (string)$m['id'],
                        'name' => (string)$m['name'],
                        'preparation' => (string)($m['latest_prep_date'] ?? ''),
                        'remark' => (string)($m['latest_prep_number'] ?? ''),
                        'result_nature' => $resultNature
                    ];
                }
            }
        }

        // Controls: from track data or test stage config
        $currentControlIds = [];
        
        // First, try to load from saved controls_items (new format with result_nature)
        if ($track->controls_data && isset($track->controls_data['controls_items']) && is_array($track->controls_data['controls_items'])) {
            foreach ($track->controls_data['controls_items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    $id = (string)$item['id'];
                    $name = (string)($item['name'] ?? 'Unknown Control');
                    
                    // Get batch details from controls map if available
                    if (isset($controlsMap[$id])) {
                        $name = (string)$controlsMap[$id]['name'];
                    }
                    
                    $currentItems['controls'][] = [
                        'id' => $id,
                        'name' => $name,
                        'preparation' => (string)($item['preparation'] ?? ''),
                        'expiry' => (string)($item['expiry'] ?? ''),
                        'result_nature' => (string)($item['result_nature'] ?? 'none')
                    ];
                }
            }
        }
        // Fall back to legacy controls_ids format
        elseif ($track->controls_data && isset($track->controls_data['controls_ids']) && is_array($track->controls_data['controls_ids'])) {
            $currentControlIds = $track->controls_data['controls_ids'];
            foreach ($currentControlIds as $controlId) {
                $id = $extractId($controlId);
                if ($id && isset($controlsMap[$id])) {
                    $c = $controlsMap[$id];
                    $currentItems['controls'][] = [
                        'id' => (string)$c['id'],
                        'name' => (string)$c['name'],
                        'preparation' => (string)($c['batch_number'] ?? ''),
                        'expiry' => (string)($c['expiry_date'] ?? ''),
                        'result_nature' => 'none'
                    ];
                }
            }
        }
        // Fall back to test stage config
        elseif ($track->testStage && $track->testStage->controls_required) {
            $controlsIds = is_string($track->testStage->controls_required) 
                ? json_decode($track->testStage->controls_required, true) 
                : (array)$track->testStage->controls_required;
            if (is_array($controlsIds)) {
                $currentControlIds = $controlsIds;
            }
            
            foreach ($currentControlIds as $controlId) {
                // Extract ID and result_nature - controlId may be object with {id, result_nature} or just scalar
                $id = $extractId($controlId);
                $resultNature = 'none';
                
                if (is_array($controlId) && isset($controlId['result_nature'])) {
                    $resultNature = (string)$controlId['result_nature'];
                } elseif (is_object($controlId) && isset($controlId->result_nature)) {
                    $resultNature = (string)$controlId->result_nature;
                }
                
                if ($id && isset($controlsMap[$id])) {
                    $c = $controlsMap[$id];
                    $currentItems['controls'][] = [
                        'id' => (string)$c['id'],
                        'name' => (string)$c['name'],
                        'preparation' => (string)($c['batch_number'] ?? ''),
                        'expiry' => (string)($c['expiry_date'] ?? ''),
                        'result_nature' => $resultNature
                    ];
                }
            }
        }

        // Diluents: from track data or test stage config
        $currentDiluentIds = [];
        
        // First, try to load from saved diluents_items (new format with metadata) - using diluents_data (plural)
        if ($track->diluents_data && isset($track->diluents_data['diluents_items']) && is_array($track->diluents_data['diluents_items'])) {
            foreach ($track->diluents_data['diluents_items'] as $item) {
                if (isset($item['id']) && $item['id']) {
                    $id = (string)$item['id'];
                    $name = 'Unknown Diluent';
                    
                    // Resolve name from diluentsMap
                    if (isset($diluentsMap[$id])) {
                        $name = (string)$diluentsMap[$id]['name'];
                    }
                    
                    $currentItems['diluents'][] = [
                        'id' => $id,
                        'name' => $name,
                        'preparation_date' => (string)($item['preparation_date'] ?? ''),
                        'preparation_number' => (string)($item['preparation_number'] ?? ''),
                        'expiry' => (string)($item['expiry'] ?? ''),
                        'result_nature' => (string)($item['result_nature'] ?? 'none')
                    ];
                }
            }
        }
        // Fall back to legacy diluent_ids format (singular column)
        elseif ($track->diluent_data && isset($track->diluent_data['diluent_ids']) && is_array($track->diluent_data['diluent_ids'])) {
            $currentDiluentIds = $track->diluent_data['diluent_ids'];
            
            // Helper to safely extract ID from scalar or object
            $extractId = function($item) {
                if (is_scalar($item)) {
                    return (string)$item;
                } elseif (is_object($item) && isset($item->id)) {
                    return (string)$item->id;
                } elseif (is_array($item) && isset($item['id'])) {
                    return (string)$item['id'];
                }
                return null;
            };

            foreach ($currentDiluentIds as $diluentId) {
                $id = $extractId($diluentId);
                if ($id && isset($diluentsMap[$id])) {
                    $d = $diluentsMap[$id];
                    $currentItems['diluents'][] = [
                        'id' => (string)$d['id'],
                        'name' => (string)$d['name'],
                        'preparation' => (string)($d['batch_prepared_date'] ?? '')
                    ];
                }
            }
        }
        // Fall back to test stage config
        elseif ($track->testStage && $track->testStage->diluent_required) {
            $diluentIds = is_string($track->testStage->diluent_required) 
                ? json_decode($track->testStage->diluent_required, true) 
                : (array)$track->testStage->diluent_required;
            if (is_array($diluentIds)) {
                $currentDiluentIds = $diluentIds;
                
                // Helper to safely extract ID from scalar or object
                $extractId = function($item) {
                    if (is_scalar($item)) {
                        return (string)$item;
                    } elseif (is_object($item) && isset($item->id)) {
                        return (string)$item->id;
                    } elseif (is_array($item) && isset($item['id'])) {
                        return (string)$item['id'];
                    }
                    return null;
                };

                foreach ($currentDiluentIds as $diluentId) {
                    $id = $extractId($diluentId);
                    if ($id && isset($diluentsMap[$id])) {
                        $d = $diluentsMap[$id];
                        $currentItems['diluents'][] = [
                            'id' => (string)$d['id'],
                            'name' => (string)$d['name'],
                            'preparation' => (string)($d['batch_prepared_date'] ?? '')
                        ];
                    }
                }
            }
        }

        // Ensure all items have proper string names
        $convertToString = function($value) use (&$convertToString) {
            if (is_array($value)) {
                // Recursively process array values but preserve structure
                return array_map(function($v) use ($convertToString) {
                    return $convertToString($v);
                }, $value);
            } elseif (is_object($value)) {
                // Try __toString first
                if (method_exists($value, '__toString')) {
                    return (string)$value;
                }
                
                // For Eloquent models or objects with attributes
                if (method_exists($value, 'toArray')) {
                    // This will convert models to associative arrays, then recurse
                    $arr = $value->toArray();
                    return $convertToString($arr);
                } elseif (method_exists($value, 'jsonSerialize')) {
                    // For JsonSerializable objects
                    $arr = $value->jsonSerialize();
                    return $convertToString($arr);
                } else {
                    // Fallback: convert object to array and recurse
                    $arr = (array)$value;
                    return $convertToString($arr);
                }
            } elseif (is_string($value) || is_numeric($value) || $value === null || is_bool($value)) {
                // Keep scalar values as-is
                return $value;
            } else {
                // Last resort: stringify uncertain types
                return (string)$value;
            }
        };

        $ensureStringNames = function($items) use ($convertToString) {
            return array_map(function($item) use ($convertToString) {
                if (is_object($item)) {
                    $item = (array)$item;
                }
                if (is_array($item)) {
                    // Convert all string and null values only, keep structure
                    $result = [];
                    foreach ($item as $key => $val) {
                        if ($val === null) {
                            $result[$key] = null;
                        } elseif (is_string($val) || is_numeric($val) || is_bool($val)) {
                            $result[$key] = $val;
                        } elseif (is_array($val)) {
                            $result[$key] = $convertToString($val);
                        } elseif (is_object($val)) {
                            $result[$key] = $convertToString($val);
                        } else {
                            $result[$key] = (string)$val;
                        }
                    }
                    return $result;
                }
                return $item;
            }, $items);
        };

        return response()->json([
            'success' => true,
            'track' => $track->toArray(),
            'equipment' => $ensureStringNames($equipment),
            'media' => $ensureStringNames($media),
            'controls' => $ensureStringNames($controls),
            'diluents' => $ensureStringNames($diluents),
            'current_items' => [
                'equipment' => $ensureStringNames($currentItems['equipment']),
                'media' => $ensureStringNames($currentItems['media']),
                'controls' => $ensureStringNames($currentItems['controls']),
                'diluents' => $ensureStringNames($currentItems['diluents'])
            ],
            'current' => [
                'equipment' => $currentEquipmentIds,
                'media' => $currentMediaIds,
                'controls' => $currentControlIds,
                'diluents' => $currentDiluentIds
            ]
        ]);
    }

    /**
     * Update stage result
     */
    public function updateMethodSequenceStageResult(Request $request, $trackId)
    {
        $request->validate([
            'result' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);
        $track->updateResult($request->result, $request->remarks, auth()->id());

        return response()->json(['success' => true, 'track' => $track->fresh()]);
    }

    /**
     * Get all tracking records with results for a batch
     */
    public function getMethodSequenceTrackingResults(Request $request, $batchId)
    {
        // *** CRITICAL: Only load from FINAL STAGE that is BOTH is_result_stage=true AND is_end_stage=true ***
        $trackingRecords = \App\Models\SampleCapturedTestStagesTrack::whereHas('capturedResult', function ($q) use ($batchId) {
            $q->where('sample_header_id', $batchId);
        })
            ->whereHas('testStage', function ($q) {
                // Filter for final result stage only
                $q->where('is_result_stage', true)
                  ->where('is_end_stage', true);
            })
            ->whereNotNull('result')
            ->where('result', '!=', '')
            ->with([
                'capturedResult.sample',
                'capturedResult.analyte',
                'capturedResult.analysisElement',
                'capturedResult.reportingUnit',
                'testStage',
                'readBy'
            ])
            ->get();

        // Enhance each record with standard information and TrackSampleResult data
        foreach ($trackingRecords as $track) {
            $captured = $track->capturedResult;
            if (!$captured) {
                continue;
            }
            $sample = $captured->sample;
            if (!$sample) {
                continue;
            }

            // *** Load TrackSampleResult data (editable staging) ***
            $trackSampleResult = \App\Models\TrackSampleResult::where('track_id', $track->id)
                ->where('captured_result_id', $captured->id)
                ->first();
            
            if ($trackSampleResult) {
                // Use staged data for display
                $track->result = $trackSampleResult->result ?? $track->result;
                $track->remark = $trackSampleResult->remark ?? '';
                $track->reporting_symbol = $trackSampleResult->reporting_symbol ?? $captured->result_reporting_symbol ?? '';
                $track->is_auto_calculated_remark = $trackSampleResult->remark_is_auto_calculated ?? false;
            } else {
                // Fallback to captured result
                $track->remark = $captured->remark ?? '';
                $track->reporting_symbol = $captured->result_reporting_symbol ?? '';
                $track->is_auto_calculated_remark = false;
            }

            // Get standards (sample may use main_standard_id / different column names)
            $mainStandardId = $sample->main_standard ?? $sample->main_standard_id ?? null;
            $secStandardId = $sample->secondary_standard ?? $sample->secondary_standard_id ?? null;
            $thirdStandardId = $sample->third_standard_id ?? null;

            $mainStandard = $mainStandardId ? \App\Standards::find($mainStandardId) : null;
            $secStandard = $secStandardId ? \App\Standards::find($secStandardId) : null;
            $thirdStandard = $thirdStandardId ? \App\Standards::find($thirdStandardId) : null;

            if ($mainStandard) {
                $track->main_standard_analyte = \App\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                    ->where('standard_id', $mainStandard->id)
                    ->with('standardValue')
                    ->first();
                $track->main_standard = $mainStandard;
            }

            if ($secStandard) {
                $track->sec_standard_analyte = \App\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                    ->where('standard_id', $secStandard->id)
                    ->with('standardValue')
                    ->first();
                $track->sec_standard = $secStandard;
            }

            if ($thirdStandard) {
                $track->third_standard_analyte = \App\StandardAnalytes::where('analyte_id', $captured->analyte_id)
                    ->where('standard_id', $thirdStandard->id)
                    ->with('standardValue')
                    ->first();
                $track->third_standard = $thirdStandard;
            }

            // Get method and reporting unit
            $track->method = $captured->method_id ? \App\AnalysisMethod::find($captured->method_id) : null;
            
            // Resolve Reporting Unit
            if (!$track->reporting_unit) {
                // Check if captured result already has a unit record
                $track->reporting_unit = $captured->reportingUnit;

                // Fallback to AnalysisElement configuration (as string name)
                if (!$track->reporting_unit && $captured->analysisElement && $captured->analysisElement->reporting_unit) {
                    $unitName = $captured->analysisElement->reporting_unit;
                    $track->reporting_unit = \App\ReportingUnit::where('name', $unitName)->first();
                }
            }
        }

        return response()->json([
            'records' => $trackingRecords,
            'all_units' => \App\ReportingUnit::where('active', 1)->orderBy('name')->get()
        ]);
    }

    /**
     * Read the first worksheet of an Excel / CSV upload as raw rows (header row + data) for Post Results import.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function importMethodSequencePostResultsSheet(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:15360'],
        ]);

        try {
            $file = $request->file('file');
            $sheets = Excel::toArray(new class implements ToArray
            {
                public function array(array $array)
                {
                    return $array;
                }
            }, $file);

            $rows = $sheets[0] ?? [];
            $normalized = [];
            foreach ($rows as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $normalized[] = array_map(fn ($cell) => $this->normalizePostResultsImportCell($cell), $row);
            }

            return response()->json([
                'success' => true,
                'rows' => $normalized,
            ]);
        } catch (\Throwable $e) {
            Log::error('Post results sheet import failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Could not read spreadsheet: '.$e->getMessage(),
            ], 422);
        }
    }

    protected function normalizePostResultsImportCell(mixed $cell): string
    {
        if ($cell instanceof \DateTimeInterface) {
            return $cell->format('Y-m-d');
        }
        if (is_bool($cell)) {
            return $cell ? '1' : '0';
        }
        if ($cell === null) {
            return '';
        }
        if (is_numeric($cell)) {
            return (string) $cell;
        }

        return (string) $cell;
    }

    /**
     * Post tracking results to captured_results table
     */
    /**
     * Post results from TrackSampleResult to CapturedResult (official table)
     * ONLY posts from the LAST STAGE that is BOTH is_result_stage=true AND is_end_stage=true
     * 
     * Data flow:
     *   TrackSampleResult (editable staging) → CapturedResult (official locked)
     */
    public function postMethodSequenceResults(Request $request)
    {
        $batchId = $request->batch_id;
        $trackingData = $request->tracking_data;
        $startAnalysisDate = $request->input('start_analysis_date');
        $endAnalysisDate = $request->input('end_analysis_date');
        
        // If batch_id provided and tracking_data is empty, fetch all result-stage tracks with saved results
        if ($batchId && (empty($trackingData) || !is_array($trackingData))) {
            try {
                $trackIds = \App\Models\TrackSampleResult::query()
                    ->whereHas('track.capturedResult', function ($q) use ($batchId) {
                        $q->where('sample_header_id', $batchId);
                    })
                    ->whereHas('track.testStage', function ($q) {
                        $q->where('is_result_stage', true)
                            ->where('is_end_stage', true);
                    })
                    ->distinct()
                    ->pluck('track_id')
                    ->toArray();

                if (empty($trackIds)) {
                    return response()->json(['success' => false, 'message' => 'No saved results found to post for this batch.'], 400);
                }

                // Build tracking data from saved results
                $trackingData = [];
                foreach ($trackIds as $trackId) {
                    $trackingData[] = ['track_id' => $trackId];
                }
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Error fetching results: ' . $e->getMessage()], 500);
            }
        } elseif (!is_array($trackingData) || empty($trackingData)) {
            return response()->json(['success' => false, 'message' => 'No tracking data to post.'], 400);
        }

        try {
            DB::transaction(function () use ($trackingData, $batchId, $startAnalysisDate, $endAnalysisDate) {
                foreach ($trackingData as $data) {
                    $trackId = $data['track_id'] ?? null;
                    if (!$trackId) {
                        throw new \Exception('Missing track_id in tracking data.');
                    }

                    $track = \App\Models\SampleCapturedTestStagesTrack::find($trackId);
                    if (!$track) {
                        throw new \Exception("Track not found: {$trackId}");
                    }

                    $testStage = $track->testStage;
                    if (! $testStage || ! $testStage->is_result_stage || ! $testStage->is_end_stage) {
                        throw new \Exception(
                            "Track {$trackId} is not from the final result stage (is_result_stage and is_end_stage required)."
                        );
                    }

                    $captured = $track->capturedResult;
                    if (!$captured) {
                        throw new \Exception("Captured result not found for track: {$trackId}");
                    }

                    // *** KEY: Load from TrackSampleResult (if exists) or use data from request ***
                    $trackSampleResult = \App\Models\TrackSampleResult::where('track_id', $trackId)
                        ->where('captured_result_id', $captured->id)
                        ->first();

                    if (array_key_exists('result', $data)) {
                        $result = (string) $data['result'];
                    } elseif ($trackSampleResult) {
                        $result = (string) ($trackSampleResult->result ?? '');
                    } else {
                        $result = (string) ($track->result ?? '');
                    }

                    if (array_key_exists('remark', $data)) {
                        $remark = (string) $data['remark'];
                    } elseif ($trackSampleResult) {
                        $remark = (string) ($trackSampleResult->remark ?? '');
                    } else {
                        $remark = (string) ($captured->remark ?? '');
                    }

                    if (array_key_exists('reporting_symbol', $data) && $data['reporting_symbol'] !== null && $data['reporting_symbol'] !== '') {
                        $reportingSymbol = $data['reporting_symbol'];
                    } elseif ($trackSampleResult) {
                        $reportingSymbol = $trackSampleResult->reporting_symbol ?? null;
                    } else {
                        $reportingSymbol = $data['reporting_symbol'] ?? ($captured->result_reporting_symbol ?? null);
                    }

                    // *** UPDATE CapturedResult with posted data ***
                    $captured->result = $result;
                    $captured->remark = $remark;
                    $captured->result_reporting_symbol = $reportingSymbol;

                    // Update method/operator/unit if provided
                    $methodId = isset($data['method_id']) && $data['method_id'] !== '' ? (int)$data['method_id'] : null;
                    $reportingUnitId = isset($data['reporting_unit_id']) && $data['reporting_unit_id'] !== '' ? (int)$data['reporting_unit_id'] : null;
                    
                    if ($methodId) {
                        $captured->method_id = $methodId;
                    }
                    if ($reportingUnitId) {
                        $captured->reporting_unit_id = $reportingUnitId;
                    }
                    $captured->operator_id = $track->read_by ?: auth()->id();
                    $captured->run_id = $track->stage_header_run_id;

                    // Equipment MUST come from stage stepper (track equipment selections)
                    $equipmentId = $this->extractPrimaryEquipmentIdFromTrack($track);
                    if ($equipmentId !== null) {
                        $captured->equipment_id = $equipmentId;
                    }

                    // Handle scientific notation if numeric result
                    if (is_numeric($result)) {
                        $scientific_arr = $this->toScientificNotation((float)$result);
                        $captured->scienctific_result = $scientific_arr['scientific'];
                        $captured->supercsript_base = number_format($scientific_arr['value'], 1);
                        $captured->superscript_number = $scientific_arr['to_power'];
                        $captured->superscript_negative = round((int)$result) >= 1 ? 0 : 1;
                    } else {
                        $captured->scienctific_result = $result;
                    }

                    // Update standard values from modal row
                    $captured->main_value = $data['main_value'] ?? null;
                    $captured->secondary_value = $data['secondary_value'] ?? '';
                    $captured->third_value = $data['third_value'] ?? '';

                    // IMPORTANT: main_standard_id in captured_results should be standards.id
                    if (isset($data['main_standard_id']) && $data['main_standard_id'] !== '') {
                        $captured->main_standard_id = (int) $data['main_standard_id'];
                    }
                    if (isset($data['sec_standard_analyte_id']) && $data['sec_standard_analyte_id'] !== '') {
                        $captured->secondary_standard_id = (int)$data['sec_standard_analyte_id'];
                    }
                    if (isset($data['third_standard_analyte_id']) && $data['third_standard_analyte_id'] !== '') {
                        $captured->third_standard_id = (int)$data['third_standard_analyte_id'];
                    }

                    // *** CRITICAL: Mark as posted ***
                    $captured->save();

                    // Ensure track is marked posted at POST time as well
                    $track->results_posted_at = now();
                    $track->results_posted_by = auth()->id();
                    $track->save();

                    // Update standard limits if edited
                    if (!empty($data['main_standard_analyte_id'])) {
                        $mainStdAnalyte = \App\StandardAnalytes::find($data['main_standard_analyte_id']);
                        if ($mainStdAnalyte && isset($data['main_limit_low'], $data['main_limit_high'])) {
                            $mainStdAnalyte->low = $data['main_limit_low'];
                            $mainStdAnalyte->high = $data['main_limit_high'];
                            $mainStdAnalyte->save();
                        }
                    }
                    if (!empty($data['sec_standard_analyte_id'])) {
                        $secStdAnalyte = \App\StandardAnalytes::find($data['sec_standard_analyte_id']);
                        if ($secStdAnalyte && isset($data['sec_limit_low'], $data['sec_limit_high'])) {
                            $secStdAnalyte->low = $data['sec_limit_low'];
                            $secStdAnalyte->high = $data['sec_limit_high'];
                            $secStdAnalyte->save();
                        }
                    }
                    if (!empty($data['third_standard_analyte_id'])) {
                        $thirdStdAnalyte = \App\StandardAnalytes::find($data['third_standard_analyte_id']);
                        if ($thirdStdAnalyte && isset($data['third_limit_low'], $data['third_limit_high'])) {
                            $thirdStdAnalyte->low = $data['third_limit_low'];
                            $thirdStdAnalyte->high = $data['third_limit_high'];
                            $thirdStdAnalyte->save();
                        }
                    }

                    // *** Update track to mark as posted ***
                    // Results have been saved to captured_results
                }

                // *** Save analysis dates for all posted samples ***
                if ($startAnalysisDate && $endAnalysisDate && $batchId) {
                    // Collect all unique sample_detail_ids from posted results
                    $sampleDetailIds = [];
                    foreach ($trackingData as $data) {
                        $trackId = $data['track_id'] ?? null;
                        if ($trackId) {
                            $track = \App\Models\SampleCapturedTestStagesTrack::find($trackId);
                            if ($track && $track->sample_detail_id) {
                                $sampleDetailIds[] = $track->sample_detail_id;
                            }
                        }
                    }

                    // Save/update analysis dates for each sample
                    foreach (array_unique($sampleDetailIds) as $sampleDetailId) {
                        $analysisDate = \App\SampleAnalysisDates::firstOrCreate(
                            [
                                'sample_detail_id' => $sampleDetailId,
                                'sample_header_id' => $batchId,
                            ],
                            ['analysis_dates' => json_encode([])]
                        );

                        $analysisDate->start_analysis_date = $startAnalysisDate;
                        $analysisDate->end_analysis_date = $endAnalysisDate;
                        $analysisDate->save();
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'Results posted successfully']);
        } catch (\Throwable $e) {
            \Log::error('Error posting method sequence results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?? 'Error posting results',
            ], 500);
        }
    }

    /**
     * Fetch all saved TrackSampleResult records for result-stage tracks in a batch.
     * Returns fully detailed data including standard analytes for modal editing.
     * Mirrors the data structure from Livewire's getTrackingResults() method.
     *
     * @param int $batch - The batch (sample_header_id) to fetch results for
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrackSampleResults(int $batch): \Illuminate\Http\JsonResponse
    {
        try {
            \Log::debug('getTrackSampleResults called', ['batch_id' => $batch]);

            // Get all SampleCapturedTestStagesTrack records with completed results for this batch
            $trackingRecords = \App\Models\SampleCapturedTestStagesTrack::whereHas('capturedResult', function($q) use ($batch) {
                    $q->where('sample_header_id', $batch);
                })
                ->whereHas('testStage', function ($q) {
                    $q->where('is_result_stage', true)
                        ->where('is_end_stage', true);
                })
                ->whereNotNull('result')
                ->where('result', '!=', '')
                ->with([
                    'capturedResult.sample',
                    'capturedResult.analyte',
                    'testStage.stageHeader',
                ])
                ->orderBy('sample_detail_id')
                ->orderBy('captured_result_id')
                ->get();

            \Log::debug('Found tracking records: '.$trackingRecords->count());

            // Build results with full standard analyte details
            $results = $trackingRecords->map(function ($track) use ($batch) {
                try {
                    $captured = $track->capturedResult;
                    if (!$captured) {
                        \Log::warning('No capturedResult for track', ['track_id' => $track->id]);
                        return null;
                    }

                    $sample = $captured->sample;
                    $analyte = $captured->analyte;

                    if (!$sample || !$analyte) {
                        \Log::warning('Missing sample or analyte', ['track_id' => $track->id]);
                        return null;
                    }

                    // Get TrackSampleResult for additional data like reporting_symbol, remark
                    $trackSampleResult = \App\Models\TrackSampleResult::where('track_id', $track->id)
                        ->where('captured_result_id', $captured->id)
                        ->first();

                    // Load standard analytes for all three standards
                    $mainStandardId = $sample->main_standard ?? $sample->main_standard_id ?? null;
                    $secStandardId = $sample->secondary_standard ?? $sample->secondary_standard_id ?? null;
                    $thirdStandardId = $sample->third_standard_id ?? null;

                    // Main standard
                    $mainStdAnalyte = null;
                    $mainStdName = '-';
                    if ($mainStandardId) {
                        $mainStdAnalyte = \App\StandardAnalytes::where('analyte_id', $analyte->id)
                            ->where('standard_id', $mainStandardId)
                            ->first();
                        if ($mainStdAnalyte && $mainStdAnalyte->standard) {
                            $mainStdName = $mainStdAnalyte->standard->displayLabel() ?: '-';
                        }
                    }

                    // Secondary standard
                    $secStdAnalyte = null;
                    $secStdName = '-';
                    if ($secStandardId) {
                        $secStdAnalyte = \App\StandardAnalytes::where('analyte_id', $analyte->id)
                            ->where('standard_id', $secStandardId)
                            ->first();
                        if ($secStdAnalyte && $secStdAnalyte->standard) {
                            $secStdName = $secStdAnalyte->standard->displayLabel() ?: '-';
                        }
                    }

                    // Third standard
                    $thirdStdAnalyte = null;
                    $thirdStdName = '-';
                    if ($thirdStandardId) {
                        $thirdStdAnalyte = \App\StandardAnalytes::where('analyte_id', $analyte->id)
                            ->where('standard_id', $thirdStandardId)
                            ->first();
                        if ($thirdStdAnalyte && $thirdStdAnalyte->standard) {
                            $thirdStdName = $thirdStdAnalyte->standard->displayLabel() ?: '-';
                        }
                    }

                    // Get method and reporting unit
                    $method = $captured->method_id ? \App\AnalysisMethod::find($captured->method_id) : null;
                    $reportingUnitId = $captured->reporting_unit_id;
                    if (empty($reportingUnitId)) {
                        $reportingUnitId = resolveReportingUnitIdFromAnalyte(
                            $captured->analysis_type_id ?? null,
                            $analyte->id,
                            null
                        );
                    }
                    $reportingUnit = $reportingUnitId ? \App\ReportingUnit::find($reportingUnitId) : null;

                    return [
                        'track_id' => $track->id,
                        'captured_result_id' => $captured->id,
                        'sample_code' => $sample->sample_code ?? 'N/A',
                        'analyte_name' => $analyte->name ?? 'N/A',
                        'analyte_id' => $analyte->id,
                        'result' => $track->result ?? '',
                        'reporting_symbol' => $trackSampleResult->reporting_symbol ?? $captured->result_reporting_symbol ?? '=',
                        'remark' => $trackSampleResult->remark ?? '',

                        // Main standard
                        'main_standard_name' => $mainStdName,
                        'main_standard_id' => $mainStandardId,
                        'main_standard_analyte' => $mainStdAnalyte ? [
                            'id' => $mainStdAnalyte->id,
                            'standard_value_type' => $mainStdAnalyte->standard_value_type ?? null,
                            'low' => $mainStdAnalyte->low ?? null,
                            'high' => $mainStdAnalyte->high ?? null,
                            'standard_is_value' => $mainStdAnalyte->standard_is_value ?? null,
                            'value_type' => $mainStdAnalyte->value_type ?? null,
                            'standard_value_id' => $mainStdAnalyte->standard_value_id ?? null,
                        ] : null,

                        // Secondary standard
                        'sec_standard_name' => $secStdName,
                        'sec_standard_id' => $secStandardId,
                        'sec_standard_analyte' => $secStdAnalyte ? [
                            'id' => $secStdAnalyte->id,
                            'standard_value_type' => $secStdAnalyte->standard_value_type ?? null,
                            'low' => $secStdAnalyte->low ?? null,
                            'high' => $secStdAnalyte->high ?? null,
                            'standard_is_value' => $secStdAnalyte->standard_is_value ?? null,
                            'value_type' => $secStdAnalyte->value_type ?? null,
                            'standard_value_id' => $secStdAnalyte->standard_value_id ?? null,
                        ] : null,

                        // Third standard
                        'third_standard_name' => $thirdStdName,
                        'third_standard_id' => $thirdStandardId,
                        'third_standard_analyte' => $thirdStdAnalyte ? [
                            'id' => $thirdStdAnalyte->id,
                            'standard_value_type' => $thirdStdAnalyte->standard_value_type ?? null,
                            'low' => $thirdStdAnalyte->low ?? null,
                            'high' => $thirdStdAnalyte->high ?? null,
                            'standard_is_value' => $thirdStdAnalyte->standard_is_value ?? null,
                            'value_type' => $thirdStdAnalyte->value_type ?? null,
                            'standard_value_id' => $thirdStdAnalyte->standard_value_id ?? null,
                        ] : null,

                        // Method and reporting unit
                        'method_id' => $captured->method_id ?? null,
                        'method_name' => $method?->name ?? 'N/A',
                        'reporting_unit_id' => $reportingUnitId,
                        'reporting_unit_name' => $reportingUnit?->name ?? 'N/A',
                    ];
                } catch (\Throwable $e) {
                    \Log::error('Error mapping tracking result', [
                        'track_id' => $track->id,
                        'error' => $e->getMessage(),
                    ]);
                    return null;
                }
            })->filter()->values();

            \Log::debug('Final results count: ' . $results->count());

            // Load available methods and reporting units for dropdowns
            $methods = \App\AnalysisMethod::orderBy('name')->get()->map(function($m) {
                return ['id' => $m->id, 'name' => $m->name];
            });

            $reportingUnits = \App\ReportingUnit::orderBy('name')->get()->map(function($u) {
                return ['id' => $u->id, 'name' => $u->name];
            });

            // List of reporting symbol options
            $reportingSymbols = ['=', '<', '>', '≤', '≥'];

            return response()->json([
                'success' => true,
                'data' => $results,
                'methods' => $methods,
                'reporting_units' => $reportingUnits,
                'reporting_symbols' => $reportingSymbols,
                'message' => 'Track sample results retrieved successfully',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Error fetching track sample results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'batch_id' => $batch,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving results: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 6: HTML partial for media/control solution results.
     */
    public function getSolutionResultsForStep6(string $trackId): \Illuminate\Http\Response
    {
        $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);
        $solutions = array_merge(
            $this->collectSolutionItemsForStep6($track, 'media'),
            $this->collectSolutionItemsForStep6($track, 'control')
        );

        $html = view('worksheets.partials.method-sequence-solution-results', [
            'solutions' => $solutions,
            'trackId' => $track->id,
        ])->render();

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Step 6: HTML partial for sample results table.
     */
    public function getSampleResultsForStep6(string $trackId): \Illuminate\Http\Response
    {
        $track = \App\Models\SampleCapturedTestStagesTrack::with([
            'stageHeaderRun.trackRecords.capturedResult.sample',
            'stageHeaderRun.trackRecords.capturedResult.my_analyte',
        ])->findOrFail($trackId);

        $samples = [];
        $seenCapturedResultIds = [];

        $runTracks = $track->stageHeaderRun?->trackRecords ?? collect();

        foreach ($runTracks as $runTrack) {
            $cr = $runTrack->capturedResult;
            if (! $cr || in_array($cr->id, $seenCapturedResultIds, true)) {
                continue;
            }

            $seenCapturedResultIds[] = $cr->id;

            $analyteName = $cr->my_analyte?->name ?? $cr->analyte_code ?? 'Unknown';
            $method = $cr->method_id ? \App\AnalysisMethod::find($cr->method_id)?->name : null;
            $unit = $cr->reporting_unit_id ? \App\ReportingUnit::find($cr->reporting_unit_id)?->name : null;
            $sampleCode = $cr->sample_detail_code ?? 'N/A';
            $sampleType = $cr->sample?->type ?? '';

            $standardLimitText = null;
            $standardAnalyte = null;
            $standardValue = null;
            $mainStandardId = $cr->sample?->main_standard ?? null;

            if ($mainStandardId && $cr->analyte_id) {
                $standardAnalyte = \App\StandardAnalytes::where('standard_id', $mainStandardId)
                    ->where('analyte_id', $cr->analyte_id)
                    ->where('is_active', 1)
                    ->first();

                if ($standardAnalyte?->standard_value_id) {
                    $standardValue = \App\StandardValue::find($standardAnalyte->standard_value_id);
                    $standardLimitText = $standardValue?->code;
                }
            }

            $trackSampleResult = \App\Models\TrackSampleResult::firstOrNew([
                'track_id' => $runTrack->id,
                'captured_result_id' => $cr->id,
            ]);

            $trackSampleResult->fill([
                'sample_code' => $sampleCode,
                'parameter' => $analyteName,
                'method' => $method,
                'reporting_unit' => $unit,
            ]);

            if (! $trackSampleResult->exists || $trackSampleResult->standard_limit === null) {
                $trackSampleResult->standard_limit = $standardLimitText;
            }

            $trackSampleResult->save();

            $samples[] = [
                'id' => $cr->id,
                'sample_code' => $sampleCode,
                'sample_type' => $sampleType,
                'analyte_name' => $analyteName,
                'method_name' => $method,
                'unit' => $unit,
                'reporting_symbol' => $trackSampleResult->reporting_symbol ?? '',
                'standard_name' => $standardAnalyte?->standard?->name ?? 'N/A',
                'standard_limit_type' => $standardAnalyte?->standard_value_type ?? 'value',
                'standard_limit_text' => $standardLimitText ?? '-',
                'limit_low' => $standardAnalyte?->low,
                'limit_high' => $standardAnalyte?->high,
                'limit_value' => $standardValue?->code ?? null,
                'result' => $trackSampleResult->result ?? '',
                'remark' => $trackSampleResult->remark ?? '',
                'remark_is_manual' => $this->isRemarkManualForCapturedResult($cr),
                'captured_result_id' => $cr->id,
            ];
        }

        $html = view('worksheets.partials.method-sequence-sample-results', [
            'samples' => $samples,
            'trackId' => $track->id,
        ])->render();

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Step 6: Calculate remark for a sample result row (AJAX).
     */
    public function calculateStep6SampleRemark(Request $request, string $trackId): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'captured_result_id' => 'required|uuid|exists:captured_results,id',
            'result' => 'nullable|string',
            'standard_limit' => 'nullable|string',
            'default_standard_limit' => 'nullable|string',
            'reporting_symbol' => 'nullable|string',
        ]);

        $track = \App\Models\SampleCapturedTestStagesTrack::findOrFail($trackId);
        $capturedResult = CapturedResult::findOrFail($validated['captured_result_id']);

        $belongsToRun = $track->stageHeaderRun
            ->trackRecords()
            ->where('captured_result_id', $capturedResult->id)
            ->exists();

        if (! $belongsToRun) {
            abort(403, 'Captured result does not belong to this run');
        }

        $analysisElement = null;
        if ($capturedResult->analysis_type_id && $capturedResult->analyte_id) {
            $analysisElement = \App\AnalysisElements::where('analysis_type_id', $capturedResult->analysis_type_id)
                ->where('analyte_id', $capturedResult->analyte_id)
                ->first();
        }

        $isAutoCalculated = ! ($analysisElement?->remark_is_manual ?? false);

        $remark = app(\App\Services\ResultRemarkService::class)->calculateRemark(
            $capturedResult,
            $validated['result'] ?? null,
            $validated['standard_limit'] ?? null,
            $validated['default_standard_limit'] ?? null,
            $validated['reporting_symbol'] ?? null
        );

        return response()->json([
            'remark' => $remark,
            'is_auto_calculated' => $isAutoCalculated,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function collectSolutionItemsForStep6(
        \App\Models\SampleCapturedTestStagesTrack $track,
        string $type
    ): array {
        $data = $type === 'media' ? ($track->media_data ?? []) : ($track->controls_data ?? []);
        $itemsKey = $type === 'media' ? 'media_items' : 'controls_items';
        $legacyIdsKey = $type === 'media' ? 'media_ids' : 'controls_ids';
        $legacyNamesKey = $type === 'media' ? 'media_names' : 'controls_names';
        $resultModel = $type === 'media' ? \App\Models\TrackMediaResult::class : \App\Models\TrackControlResult::class;
        $foreignKey = $type === 'media' ? 'media_id' : 'control_id';

        $existingResults = $resultModel::where('track_id', $track->id)->get()->keyBy($foreignKey);
        $solutions = [];

        if (! empty($data[$itemsKey]) && is_array($data[$itemsKey])) {
            foreach ($data[$itemsKey] as $item) {
                $id = $item['id'] ?? null;
                if (! $id) {
                    continue;
                }
                $existing = $existingResults->get((string) $id);
                $solutions[] = [
                    'id' => $id,
                    'name' => $item['name'] ?? 'Unknown',
                    'type' => $type,
                    'result' => $existing?->result ?? ($item['result'] ?? ''),
                ];
            }

            return $solutions;
        }

        $ids = $data[$legacyIdsKey] ?? [];
        $names = $data[$legacyNamesKey] ?? [];
        if (! is_array($ids)) {
            return $solutions;
        }

        foreach ($ids as $index => $id) {
            if (! $id) {
                continue;
            }
            $existing = $existingResults->get((string) $id);
            $solutions[] = [
                'id' => $id,
                'name' => $names[$index] ?? ucfirst($type).' '.($index + 1),
                'type' => $type,
                'result' => $existing?->result ?? '',
            ];
        }

        return $solutions;
    }

    protected function isRemarkManualForCapturedResult(CapturedResult $capturedResult): bool
    {
        if (! $capturedResult->analysis_type_id || ! $capturedResult->analyte_id) {
            return false;
        }

        $analysisElement = \App\AnalysisElements::where('analysis_type_id', $capturedResult->analysis_type_id)
            ->where('analyte_id', $capturedResult->analyte_id)
            ->first();

        return (bool) ($analysisElement?->remark_is_manual ?? false);
    }

    /**
     * Resolve COA analyte accreditation from capture form input.
     *
     * Unchecked HTML checkboxes do not appear in the request; the workflow templates now send a hidden
     * `accredited[id]=0` so saves do not wipe accreditation. When no key is present (legacy), fall back to
     * analysis_elements.non_accredited (1 = accredited in the analysis-type UI).
     */
    private function resolveAnalyteAccreditedFromCaptureRequest(Request $request, int $capturedResultId, CapturedResult $captured): int
    {
        $accredited = $request->input('accredited');
        if (! is_array($accredited)) {
            return $this->analyteAccreditedFromAnalysisElement($captured);
        }

        $keyString = (string) $capturedResultId;
        $value = null;
        if (array_key_exists($keyString, $accredited)) {
            $value = $accredited[$keyString];
        } elseif (array_key_exists($capturedResultId, $accredited)) {
            $value = $accredited[$capturedResultId];
        }

        if ($value === null) {
            return $this->analyteAccreditedFromAnalysisElement($captured);
        }

        if ($value === true || $value === 1 || $value === '1' || $value === 'on') {
            return 1;
        }

        return 0;
    }
}
