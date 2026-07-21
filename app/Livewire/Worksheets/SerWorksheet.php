<?php

namespace App\Livewire\Worksheets;

use Livewire\Component;
use App\Models\Worksheets\SerHeaderWorksheetSampleRelation;
use App\Models\Worksheets\SerStepWorksheetSampleRelation;
use App\Models\Worksheets\SerTestkitWorksheetSampleRelation;
use App\Models\SerWorksheetStep;
use App\Models\Equipments\Equipment;
use App\User;
use App\SampleDetails;
use App\AnalysisType;
use App\AnalysisMethod;
use App\CapturedResult;
use App\Services\Worksheets\WorksheetMetaResolver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SerWorksheet extends Component
{
    public $batch;
    public $analysisTypeId;
    public $samples = [];
    public $analysisTypeName;

    public $isRunCreated = false;
    public $headerId = null;

    // Header Fields
    public $lab_numbers = []; // searchable dropdown of samples
    public $sample_type; // readonly
    public $analyst_ids = []; // multi select
    public $dilution_used;
    public $date_received; // readonly
    public $date_tested;
    public $test_analyte_codes = []; // readonly display
    public $method_id;
    public $room_temperature;
    public $start_time;

    // Tables
    public $steps = [];
    public $testKits = [];

    // Valid options
    public $availableMethods = [];
    public $availableAnalysts = [];
    public $availableSamples = [];
    public $availableRuns = [];
    public $selectedRunId = null;

    // Searchable Dropdown State
    public $showLabDropdown = false;
    public $labSearch = '';

    public $showAnalystDropdown = false;
    public $analystSearch = '';

    public $showMethodDropdown = false;
    public $methodSearch = '';

    public function getFilteredSamplesProperty()
    {
        if (empty($this->labSearch)) {
            return $this->availableSamples;
        }

        return collect($this->availableSamples)->filter(function ($sample) {
            return stripos($sample['code'], $this->labSearch) !== false;
        })->values()->toArray();
    }

    public function getFilteredAnalystsProperty()
    {
        if (empty($this->analystSearch)) {
            return $this->availableAnalysts;
        }

        return collect($this->availableAnalysts)->filter(function ($analyst) {
            return stripos($analyst->name, $this->analystSearch) !== false;
        })->values();
    }

    public function getFilteredMethodsProperty()
    {
        if (empty($this->methodSearch)) {
            return $this->availableMethods;
        }

        return collect($this->availableMethods)->filter(function ($method) {
            return stripos($method->name, $this->methodSearch) !== false;
        })->values();
    }

    public function selectLab($id)
    {
        if (!in_array($id, $this->lab_numbers)) {
            $this->lab_numbers[] = $id;
            $this->updatedLabNumbers($this->lab_numbers);
        }
        $this->labSearch = '';
        // Don't close dropdown immediately for multiselect convenience? Or close it? 
        // Reference usually keeps open or closes. Let's keep open for easier multi-selection, 
        // or user can click away. User request didn't specify, but standard practice for multi is keep open.
        // matches 'selectAnalyte' behavior in reference if it was multi (reference is mixed).
    }

    public function removeLab($id)
    {
        $this->lab_numbers = array_values(array_diff($this->lab_numbers, [$id]));
        $this->updatedLabNumbers($this->lab_numbers);
    }

    public function selectAnalyst($id)
    {
        if (!in_array($id, $this->analyst_ids)) {
            $this->analyst_ids[] = $id;
        }
        $this->analystSearch = '';
    }

    public function removeAnalyst($id)
    {
        $this->analyst_ids = array_values(array_diff($this->analyst_ids, [$id]));
    }

    public function selectMethod($id)
    {
        $this->method_id = $id;
        $this->showMethodDropdown = false;
        $this->methodSearch = '';
    }

    public function clearMethod()
    {
        $this->method_id = null;
    }

    public function mount($batch, $analysisTypeId, $samples, $analysisTypeName)
    {
        $this->batch = $batch;
        $this->analysisTypeId = $analysisTypeId;
        $this->samples = $samples;
        $this->analysisTypeName = $analysisTypeName;

        $this->availableSamples = collect($samples)->map(function ($s) {
            return [
                'id' => $s['id'],
                'code' => $s['sample_code']
            ];
        })->toArray();

        $this->availableAnalysts = User::where('active', 1)->get();
        $this->availableMethods = AnalysisMethod::all(); // Provide all, refine filter when sample selected

        $this->date_tested = Carbon::now()->format('Y-m-d');
        $this->start_time = Carbon::now()->format('H:i');

        // Initial check for existing runs
        $this->loadExistingRuns();

        $this->dispatch('worksheet-print-context-updated', context: [
            'analysis_type_id' => (string) $this->analysisTypeId,
        ]);
    }

    public function loadExistingRuns()
    {
        // Find existing runs for these samples and this analysis type
        $sampleIds = collect($this->samples)->pluck('id');
        $existingHeaders = SerHeaderWorksheetSampleRelation::whereIn('sample_detail_id', $sampleIds)
            ->where('analysis_type_id', $this->analysisTypeId)
            ->with(['sample'])
            ->get();

        $this->availableRuns = $existingHeaders;
    }

    public function selectRun($headerId)
    {
        $header = SerHeaderWorksheetSampleRelation::with(['steps', 'testKits'])->find($headerId);
        if ($header) {
            $this->headerId = $header->id;
            $this->lab_numbers = [$header->sample_detail_id];
            $this->analyst_ids = $header->analyst_ids ?? [];
            $this->dilution_used = $header->dilution_used;
            $this->date_received = $header->date_received ? $header->date_received->format('Y-m-d') : null;
            $this->date_tested = $header->date_tested ? $header->date_tested->format('Y-m-d') : null;
            $this->room_temperature = $header->room_temperature;
            $this->start_time = $header->start_time ? $header->start_time->format('H:i') : null;
            $this->method_id = $header->method_id;

            // Load steps
            $this->steps = $header->steps->map(function ($step) {
                // Find step name from config if possible
                $configStep = SerWorksheetStep::find($step->ser_worksheet_step_id);
                return [
                    'id' => $step->id,
                    'ser_worksheet_step_id' => $step->ser_worksheet_step_id,
                    'step_name' => $configStep->step ?? 'Step',
                    'measurand_id' => $step->measurand_id,
                    'equipment_id' => $step->equipment_id,
                    'analyst_id' => $step->analyst_id,
                ];
            })->toArray();

            // Load test kits
            $this->testKits = $header->testKits->map(function ($kit) {
                return [
                    'id' => $kit->id,
                    'test_name' => $kit->test_name,
                    'kit_lot_number' => $kit->kit_lot_number,
                    'wells_used' => $kit->wells_used,
                    'expiry_date' => $kit->expiry_date ? $kit->expiry_date->format('Y-m-d') : null,
                ];
            })->toArray();

            $this->updatedLabNumbers($this->lab_numbers);
            $this->isRunCreated = true;
        }
    }

    public function createRun()
    {
        $this->isRunCreated = true;
        // Initialize default blank form if no existing data loaded
        if (!$this->headerId) {
            $this->testKits = []; // Start empty? "have an add button... capture as many"
            // Initialize steps from active SerWorksheetStep
            $activeSteps = SerWorksheetStep::where('is_active', 1)->orderBy('id')->get();
            $this->steps = $activeSteps->map(function ($step) {
                return [
                    'ser_worksheet_step_id' => $step->id,
                    'step_name' => $step->step,
                    'measurand_id' => $step->default_measurand_ids[0] ?? null, // Auto select default
                    'equipment_id' => $step->default_equipment_id,
                    'analyst_id' => $step->default_analyst_id ?? Auth::id(),
                ];
            })->toArray();
        }
    }

    public function updatedLabNumbers($sampleIds)
    {
        // Populate readonly fields
        if (is_array($sampleIds) && count($sampleIds) > 0) {
            // Use the first sample for general info
            $firstSampleId = $sampleIds[0];
            $sample = SampleDetails::find($firstSampleId);
            if ($sample) {
                $this->sample_type = $this->batch->sample_type->name ?? '';
                $this->date_received = $this->batch->receipt_date;

                // Aggregate codes from all selected samples
                $codes = [];
                $methodIds = [];

                foreach ($sampleIds as $sId) {
                    $capturedResults = CapturedResult::where('sample_detail_id', $sId)->get();
                    foreach ($capturedResults as $res) {
                        // Check if this result belongs to current analysis type context
                        if ($res->analysisElement && $res->analysisElement->analysis_type_id == $this->analysisTypeId) {
                            $codes[] = $res->analysisElement->analyte->code ?? '';
                            if ($res->analysisElement->method) {
                                $methodIds[] = $res->analysisElement->method;
                            }
                        }
                    }
                }

                $this->test_analyte_codes = array_unique($codes);

                if (count($methodIds) > 0) {
                    // Unique methods? Or just pick first?
                    // "autoslect the methods tied to the captured results"
                    $this->method_id = $methodIds[0];
                }
            }
        } else {
            $this->test_analyte_codes = [];
        }
    }

    public function addTestKit()
    {
        $this->testKits[] = [
            'test_name' => '',
            'kit_lot_number' => '',
            'wells_used' => '',
            'expiry_date' => '',
        ];
    }

    public function removeTestKit($index)
    {
        unset($this->testKits[$index]);
        $this->testKits = array_values($this->testKits);
    }

    public function save()
    {
        $this->validate([
            'lab_numbers' => 'required|array',
            'analyst_ids' => 'required|array',
            'date_tested' => 'required|date',
        ]);

        if ($this->headerId && is_numeric($this->headerId)) {
            // Update Existing Header
            $header = SerHeaderWorksheetSampleRelation::find($this->headerId);
            if ($header) {
                $header->update([
                    'dilution_used' => $this->dilution_used,
                    'date_tested' => $this->date_tested,
                    'room_temperature' => $this->room_temperature,
                    'start_time' => $this->start_time,
                    'method_id' => $this->method_id,
                    'analyst_ids' => $this->analyst_ids,
                ]);

                // Sync Steps
                foreach ($this->steps as $stepData) {
                    if (isset($stepData['id'])) {
                        SerStepWorksheetSampleRelation::where('id', $stepData['id'])->update([
                            'measurand_id' => $stepData['measurand_id'] ?? null,
                            'equipment_id' => $stepData['equipment_id'] ?? null,
                            'analyst_id' => $stepData['analyst_id'] ?? null,
                        ]);
                    }
                }

                // Sync Test Kits (simplest is to delete and recreate for this many-to-one)
                $header->testKits()->delete();
                foreach ($this->testKits as $kitData) {
                    SerTestkitWorksheetSampleRelation::create([
                        'ser_header_id' => $header->id,
                        'test_name' => $kitData['test_name'],
                        'kit_lot_number' => $kitData['kit_lot_number'],
                        'wells_used' => $kitData['wells_used'],
                        'expiry_date' => $kitData['expiry_date'] ?: null,
                    ]);
                }
            }
            session()->flash('message', 'Run updated successfully.');
        } else {
            // Create Header for each selected sample
            foreach ($this->lab_numbers as $sampleId) {
                $header = SerHeaderWorksheetSampleRelation::create([
                    'sample_detail_id' => $sampleId,
                    'analysis_type_id' => $this->analysisTypeId,
                    'dilution_used' => $this->dilution_used,
                    'date_received' => $this->date_received,
                    'date_tested' => $this->date_tested,
                    'room_temperature' => $this->room_temperature,
                    'start_time' => $this->start_time,
                    'method_id' => $this->method_id,
                    'analyst_ids' => $this->analyst_ids,
                ]);

                // Create Steps
                foreach ($this->steps as $index => $stepData) {
                    SerStepWorksheetSampleRelation::create([
                        'ser_header_id' => $header->id,
                        'ser_worksheet_step_id' => $stepData['ser_worksheet_step_id'] ?? null,
                        'step_number' => $index + 1,
                        'measurand_id' => $stepData['measurand_id'] ?? null,
                        'equipment_id' => $stepData['equipment_id'] ?? null,
                        'analyst_id' => $stepData['analyst_id'] ?? null,
                    ]);
                }

                // Create Test Kits
                foreach ($this->testKits as $kitData) {
                    SerTestkitWorksheetSampleRelation::create([
                        'ser_header_id' => $header->id,
                        'test_name' => $kitData['test_name'],
                        'kit_lot_number' => $kitData['kit_lot_number'],
                        'wells_used' => $kitData['wells_used'],
                        'expiry_date' => $kitData['expiry_date'] ?: null,
                    ]);
                }
            }
            session()->flash('message', 'Runs created successfully.');
        }

        $this->isRunCreated = false;
        $this->headerId = null;
        $this->loadExistingRuns();
    }

    public function render()
    {
        $metaResolver = app(WorksheetMetaResolver::class);
        $capturedResults = CapturedResult::query()
            ->where('sample_header_id', $this->batch->id)
            ->where('analysis_type_id', $this->analysisTypeId)
            ->where('has_no_result_capture', true)
            ->with(WorksheetMetaResolver::EAGER)
            ->get();

        $analystNames = collect($this->analyst_ids)
            ->map(fn ($id) => User::query()->find($id)?->name)
            ->filter()
            ->implode(', ');

        $methodName = $this->method_id
            ? AnalysisMethod::query()->find($this->method_id)?->name
            : null;

        $metaContext = array_filter([
            'analyst_name' => $analystNames !== '' ? $analystNames : null,
        ]);

        $worksheetMetaSummary = $metaResolver->summaryForMany($capturedResults, $metaContext);
        if ($methodName) {
            $worksheetMetaSummary['method'] = $methodName;
        }

        return view('livewire.worksheets.ser-worksheet', [
            'measurands' => \App\AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                ->orWhereIn('id', collect($this->steps)->pluck('measurand_id')->filter())
                ->get(),
            'equipments' => Equipment::where('status', 'Active')->get(),
            'worksheetMetaSummary' => $worksheetMetaSummary,
            'worksheetMetaRows' => $metaResolver->forMany($capturedResults, $metaContext),
        ]);
    }
}
