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
        
        return collect($this->availableSamples)->filter(function($sample) {
            return stripos($sample['code'], $this->labSearch) !== false;
        })->values()->toArray();
    }
    
    public function getFilteredAnalystsProperty()
    {
        if (empty($this->analystSearch)) {
            return $this->availableAnalysts;
        }
        
        return collect($this->availableAnalysts)->filter(function($analyst) {
            return stripos($analyst->name, $this->analystSearch) !== false;
        })->values();
    }
    
    public function getFilteredMethodsProperty()
    {
        if (empty($this->methodSearch)) {
            return $this->availableMethods;
        }
        
        return collect($this->availableMethods)->filter(function($method) {
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

        $this->availableSamples = collect($samples)->map(function($s) {
            return [
                'id' => $s['id'],
                'code' => $s['sample_code']
            ];
        })->toArray();
        
        // Find existing run if any for the first sample or just based on analysis type/batch? 
        // The requirement implies one run per sample or per batch? 
        // "creates a new tab call the {{sample->analysistype}} worksheet that on click opens a tab content with ability to create a run"
        // "Laboratory Number (searchable dropdown of all the samples tied to the batch)"
        // This suggests one "Run" per "Laboratory Number" (Sample).
        // But the tab is for the whole Analysis Type. 
        // So maybe the tab lists existing runs and allows creating a new one?
        // Or "create run" basically initializes the form for a specific sample?
        // Let's assume the user selects a sample to create a run for.

        $this->availableAnalysts = User::where('active', 1)->get();
        // Assume methods are tied to the captured results which are tied to analysis type
        // In mount, we might not have a selected sample yet, so we load generic methods for this analysis type?
        // "Method Used (searchable dropdown of all methods but autoslect the methods tied to the captured results)"
        $this->availableMethods = AnalysisMethod::all(); // Provide all, refine filter when sample selected

        $this->date_tested = Carbon::now()->format('Y-m-d');
        $this->start_time = Carbon::now()->format('H:i');
    }

    public function createRun()
    {
        $this->isRunCreated = true;
        // Initialize default blank form if no existing data loaded
        if (!$this->headerId) {
            $this->testKits = []; // Start empty? "have an add button... capture as many"
            // Initialize steps from active SerWorksheetStep
            $activeSteps = SerWorksheetStep::where('is_active', 1)->orderBy('id')->get();
            $this->steps = $activeSteps->map(function($step) {
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
                
                foreach($sampleIds as $sId) {
                    $capturedResults = CapturedResult::where('sample_detail_id', $sId)->get();
                    foreach($capturedResults as $res) {
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

        // Create Header
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
        
        $this->headerId = true; // Just a flag to switch view or show done
        session()->flash('message', 'Run created successfully.');
    }

    public function render()
    {
        return view('livewire.worksheets.ser-worksheet', [
            // Fetch measurands relevant to the active steps + analysis type
            'measurands' => \App\AnalysisElements::where('analysis_type_id', $this->analysisTypeId)
                                ->orWhereIn('id', collect($this->steps)->pluck('measurand_id')->filter())
                                ->get(),
            // "searchable dropdowns with already auto select the default configurations"
            // I'll pass necessary lookups.
            'equipments' => Equipment::where('status', 'Active')->get(),
        ]);
    }
}
