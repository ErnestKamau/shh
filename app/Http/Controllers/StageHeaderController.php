<?php

namespace App\Http\Controllers;

use App\Models\StageHeader;
use App\Models\TestStage;
use App\Models\SampleProgress;
use App\Models\System\SystemConfiguration;
use App\SampleDetails;
use Illuminate\Http\Request;
use App\Services\AnalyteProgressService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StageHeaderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of stage headers
     */
    public function index()
    {
        $stageHeaders = StageHeader::with(['method', 'analyte', 'sampleType', 'testStages'])->get();
        
        return view('layouts.lab.stage-headers.index', compact('stageHeaders'));
    }

    /**
     * Show the form for creating a new stage header
     */
    public function create()
    {
        $methods = \App\AnalysisMethod::where('active', 1)->get();
        $analytes = \App\Analyte::all();
        
        return view('layouts.lab.stage-headers.create', compact('methods', 'analytes'));
    }

    /**
     * Store a newly created stage header
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'method_id' => 'required|exists:analysis_methods,id',
            'analyte_id' => 'required|exists:analytes,id',
        ]);

        $stageHeader = StageHeader::create($this->stageHeaderPayload($request));

        return redirect()->route('stage-headers.show', $stageHeader->id)
            ->with('success', 'Stage header created successfully. Now add the stages.');
    }

    /**
     * Display the specified stage header with its stages
     */
    public function show(StageHeader $stageHeader)
    {
        $stageHeader->load('testStages');
        
        // Fetch media items from system configuration
        $mediaCategoryId = SystemConfiguration::where('key', 'media_solution_type_id')->value('value');
        $mediaItems = $mediaCategoryId 
            ? \App\LabSubCategory::where('category_id', $mediaCategoryId)->where('active', 1)->get() 
            : collect();
        
        // Fetch control items from system configuration
        $controlCategoryId = SystemConfiguration::where('key', 'control_solution_type_id')->value('value');
        $controlItems = $controlCategoryId 
            ? \App\LabSubCategory::where('category_id', $controlCategoryId)->where('active', 1)->get() 
            : collect();

        // return response()->json($controlCategoryId);
        
        // Fetch equipment using helper function
        $equipments = getEquipment();
        // return response()->json($equipment);
        // 
        return view('layouts.lab.stage-headers.show', compact('stageHeader', 'mediaItems', 'controlItems', 'equipments'));
    }

    /**
     * Show the form for editing the stage header
     */
    public function edit(StageHeader $stageHeader)
    {
        $methods = \App\AnalysisMethod::where('active', 1)->get();
        $analytes = \App\Analyte::all();
        
        return view('layouts.lab.stage-headers.edit', compact('stageHeader', 'methods', 'analytes'));
    }

    /**
     * Update the specified stage header
     */
    public function update(Request $request, StageHeader $stageHeader)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'method_id' => 'required|exists:analysis_methods,id',
            'analyte_id' => 'required|exists:analytes,id',
        ]);

        $stageHeader->update($this->stageHeaderPayload($request));

        return redirect()->route('stage-headers.index')
            ->with('success', 'Stage header updated successfully.');
    }

    /**
     * Remove the specified stage header
     */
    public function destroy(StageHeader $stageHeader)
    {
        $stageHeader->delete();

        return redirect()->route('stage-headers.index')
            ->with('success', 'Stage header deleted successfully.');
    }

        /**
     * Add a stage to a stage header
     */
    public function addStage(Request $request, StageHeader $stageHeader)
    {
        $request->validate([
            'stage_name' => 'required|string|max:255',
            'duration_hours' => 'required|integer|min:1',
            'safe_duration' => 'nullable|integer|min:1',
            'media_required' => 'nullable|array',
            'equipment_required' => 'nullable|array',
            'controls_required' => 'nullable|array',
            'diluents_required' => 'nullable|array',
            'instructions' => 'nullable|string',
            // Remove 'order' from required validation since we'll auto-generate it
        ]);

        try {
            // Auto-determine the next order number
            $lastStage = TestStage::where('stage_header_id', $stageHeader->id)
                ->orderBy('order', 'desc')
                ->first();
            
            $nextOrder = $lastStage ? $lastStage->order + 1 : 1;

            $stageData = $request->except(['_token', '_method']);
            $stageData['stage_header_id'] = $stageHeader->id;
            $stageData['order'] = $nextOrder; // Use auto-generated order
            
            // Handle array fields (TestStage model casts will automatically json_encode these)
            $stageData['equipment_required'] = $request->input('equipment_required', null);
            $stageData['media_required'] = $request->input('media_required', null);
            $stageData['controls_required'] = $request->input('controls_required', null);
            $stageData['diluents_required'] = $request->input('diluents_required', null);
            
            // Handle boolean fields
            $stageData['is_result_stage'] = $request->has('is_result_stage') ? 1 : 0;
            $stageData['end_if_pass'] = $request->has('end_if_pass') ? 1 : 0;
            $stageData['end_if_fail'] = $request->has('end_if_fail') ? 1 : 0;
            $stageData['is_end_stage'] = $request->has('is_end_stage') ? 1 : 0;

            TestStage::create($stageData);

            $this->syncStageHeaderTotalDays($stageHeader);

            return redirect()->back()->with('success', "Stage added successfully as order #{$nextOrder}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error adding stage: ' . $e->getMessage());
        }
    }

    /**
     * Remove a stage from a stage header
     */
    public function removeStage(TestStage $testStage)
    {
        $stageHeader = StageHeader::findOrFail($testStage->stage_header_id);
        $testStage->delete();
        $this->syncStageHeaderTotalDays($stageHeader);

        return redirect()->route('stage-headers.show', $stageHeader->id)
            ->with('success', 'Stage removed successfully.');
    }

    /**
     * Remove a stage via POST (for form submissions with stage_id in body)
     */
    public function removeStagePost(Request $request)
    {
        $request->validate([
            'stage_id' => 'required|exists:test_stages,id',
        ]);

        $testStage = TestStage::findOrFail($request->stage_id);
        $stageHeader = StageHeader::findOrFail($testStage->stage_header_id);
        $testStage->delete();
        $this->syncStageHeaderTotalDays($stageHeader);

        return redirect()->route('stage-headers.show', $stageHeader->id)
            ->with('success', 'Stage removed successfully.');
    }

    /**
     * Update a stage
     */
    public function updateStage(Request $request, StageHeader $stageHeader, TestStage $testStage)
    {
        $request->validate([
            'order' => 'required|integer|min:1',
            'stage_name' => 'required|string|max:255',
            'duration_hours' => 'required|integer|min:1',
            'safe_duration' => 'nullable|integer|min:1',
            'media_required' => 'nullable|array',
            'equipment_required' => 'nullable|array',
            'controls_required' => 'nullable|array',
            'diluents_required' => 'nullable|array',
            'instructions' => 'nullable|string',
        ]);

        try {
            $stageData = $request->except(['_token', '_method']);
            
            // Handle array fields (TestStage model casts will automatically json_encode these)
            $stageData['equipment_required'] = $request->input('equipment_required', null);
            $stageData['media_required'] = $request->input('media_required', null);
            $stageData['controls_required'] = $request->input('controls_required', null);
            $stageData['diluents_required'] = $request->input('diluents_required', null);
            
            // Handle boolean fields
            $stageData['is_result_stage'] = $request->has('is_result_stage') ? 1 : 0;
            $stageData['end_if_pass'] = $request->has('end_if_pass') ? 1 : 0;
            $stageData['end_if_fail'] = $request->has('end_if_fail') ? 1 : 0;
            $stageData['is_end_stage'] = $request->has('is_end_stage') ? 1 : 0;

            $testStage->update($stageData);

            return redirect()->back()->with('success', 'Stage updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating stage: ' . $e->getMessage());
        }
    }

    /**
     * Reorder stages via AJAX
     */
    public function reorderStages(Request $request, StageHeader $stageHeader)
    {
        try {
            $stages = $request->input('stages');
            
            if (!$stages || !is_array($stages)) {
                return response()->json(['success' => false, 'message' => 'Invalid stages data'], 400);
            }
            
            // Use database transaction to ensure atomicity
            DB::transaction(function () use ($stages, $stageHeader) {
                // First, set all orders to negative values to avoid unique constraint conflicts
                foreach ($stages as $index => $stage) {
                    if (isset($stage['id'])) {
                        TestStage::where('id', $stage['id'])
                            ->where('stage_header_id', $stageHeader->id)
                            ->update(['order' => -($index + 1)]);
                    }
                }
                
                // Then update to the correct positive values
                foreach ($stages as $stage) {
                    if (isset($stage['id']) && isset($stage['order'])) {
                        TestStage::where('id', $stage['id'])
                            ->where('stage_header_id', $stageHeader->id)
                            ->update(['order' => $stage['order']]);
                    }
                }
            });

            $this->syncStageHeaderTotalDays($stageHeader);

            return response()->json(['success' => true, 'message' => 'Stages reordered successfully']);

        } catch (\Exception $e) {
            Log::error('Error reordering stages: ' . $e->getMessage(), [
                'stage_header_id' => $stageHeader->id,
                'stages' => $request->input('stages'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Display progress tracking dashboard
     */

    public function progress()
    {
        try {
            // Ultra simple - just get basic data
            $todaysTests = collect([]); // Empty for now
            $stageHeaders = StageHeader::with(['method', 'analyte', 'testStages'])->get();
            $availableSamples = collect([]); // Empty for now

            return view('layouts.lab.stage-headers.progress', compact(
                'todaysTests', 
                'stageHeaders', 
                'availableSamples'
            ));

        } catch (\Exception $e) {
            // Absolute fallback
            return view('layouts.lab.stage-headers.progress', [
                'todaysTests' => collect([]),
                'stageHeaders' => collect([]),
                'availableSamples' => collect([]),
            ]);
        }
    }

    /**
     * Start a new test for a sample
     */
    public function startTest(Request $request, StageHeader $stageHeader)
    {
        $request->validate([
            'sample_detail_id' => 'required|exists:sample_details,id',
            'start_date' => 'required|date',
        ]);

        try {
            $sample = SampleDetails::findOrFail($request->sample_detail_id);
            
            // Check if this sample already has an in-progress test for this analyte
            $existingProgress = SampleProgress::where('sample_detail_id', $sample->id)
                ->where('analyte_id', $stageHeader->analyte_id)
                ->whereIn('status', ['in_progress', 'not_started'])
                ->first();

            if ($existingProgress) {
                return redirect()->back()->with('error', 
                    "Sample #{$sample->id} already has an in-progress test for {$stageHeader->analyte->name}");
            }

            $service = new AnalyteProgressService();
            $progress = $service->startProgressTracking($sample, $stageHeader);

            // Update with custom start date if provided
            if ($request->start_date) {
                $progress->update([
                    'start_date' => $request->start_date,
                    'current_day_date' => $request->start_date,
                ]);
            }

            return redirect()->route('stage-headers.progress')
                ->with('success', 
                    "Started {$stageHeader->analyte->name} test for Sample #{$sample->id}. First stage: {$progress->testStage->stage_name}");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to start test: ' . $e->getMessage());
        }
    }

    /**
     * Start an existing progress record
     */
    public function startProgress(Request $request, SampleProgress $progress)
    {
        try {
            if ($progress->status !== 'not_started') {
                return response()->json([
                    'success' => false,
                    'message' => 'Test is already in progress or completed'
                ]);
            }

            $progress->startTest();

            return response()->json([
                'success' => true,
                'message' => 'Test started successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error starting test: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Record result and advance to next stage
     */
    public function recordResult(Request $request, SampleProgress $progress)
    {
        $request->validate([
            'result' => 'required|in:positive,negative,inconclusive',
            'observations' => 'nullable|string',
        ]);

        try {
            if ($progress->status !== 'in_progress') {
                return redirect()->back()->with('error', 'This test is not in progress');
            }

            $service = new AnalyteProgressService();
            $nextProgress = $service->advanceToNextStage($progress, $request->result);

            // Update observations
            $progress->update([
                'observations' => $request->observations ?: $progress->observations
            ]);

            if ($nextProgress) {
                $message = "Result recorded. Advanced to next stage: {$nextProgress->testStage->stage_name}";
            } else {
                $message = "Test completed successfully!";
            }

            return redirect()->route('stage-headers.progress')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error recording result: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a test
     */
    public function cancelTest(Request $request, SampleProgress $progress)
    {
        try {
            if ($progress->status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel a completed test'
                ]);
            }

            $progress->cancelTest();

            return response()->json([
                'success' => true,
                'message' => 'Test cancelled successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling test: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function stageHeaderPayload(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'method_id' => $request->input('method_id'),
            'analyte_id' => $request->input('analyte_id'),
            'sample_type_id' => $request->input('sample_type_id'),
            'is_multi_stage' => $request->boolean('is_multi_stage'),
            'total_days' => $request->input('total_days', 0),
        ];
    }

    private function syncStageHeaderTotalDays(StageHeader $stageHeader): void
    {
        $maxOrder = TestStage::query()
            ->where('stage_header_id', $stageHeader->id)
            ->max('order');

        $stageHeader->update([
            'total_days' => $maxOrder ? (int) $maxOrder : 0,
        ]);
    }
}