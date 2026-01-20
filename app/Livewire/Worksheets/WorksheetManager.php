<?php

namespace App\Livewire\Worksheets;

use App\CapturedResult;
use App\Models\Formulars\Formula;
use App\Models\MethodSequences\MethodSequence;
use App\Models\Worksheets\SampleCapturedWorksheetFormula;
use App\SampleHeader;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class WorksheetManager extends Component
{
    public SampleHeader $batch;
    public $activeTab = 'formulas';
    
    // Formulas and Method Sequences for this batch
    public $formulas = [];

    public $methodSequences = [];
    public $noCaptureSamples = [];
    public $hasNoCaptureSamples = false;
    public $groupedNoCaptureSamples = [];
    
    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
        $this->loadWorksheetData();
    }

    public function loadWorksheetData(): void
    {
        // Get all captured results for this batch that have formular_id or method_sequence_id
        $capturedResults = CapturedResult::where('sample_header_id', $this->batch->id)
            ->where(function ($query) {
                $query->whereNotNull('formular_id')
                      ->orWhereNotNull('method_sequence_id');
            })
            ->with(['analysisElement', 'formular', 'methodSequence', 'sample'])
            ->get();

        // Group by formular_id
        $formulaIds = $capturedResults->whereNotNull('formular_id')
            ->pluck('formular_id')
            ->unique()
            ->values();
        
        $this->formulas = Formula::whereIn('id', $formulaIds)
            ->with('activeVersion')
            ->get();

        // Group by method_sequence_id
        $sequenceIds = $capturedResults->whereNotNull('method_sequence_id')
            ->pluck('method_sequence_id')
            ->unique()
            ->values();
        
        $this->methodSequences = MethodSequence::whereIn('id', $sequenceIds)
            ->with('activeVersion.stages')
            ->get();

        // Check for samples with no result capture
        // We look for CapturedResults for this batch that have 'has_no_result_capture' = 1
        $noCaptureResults = CapturedResult::where('sample_header_id', $this->batch->id)
            ->where('has_no_result_capture', 1)
            ->with(['sample', 'analysis_type'])
            ->get();

        $this->groupedNoCaptureSamples = [];

        foreach ($noCaptureResults as $result) {
            $analysisId = $result->analysis_type_id;
            
            // Initialize group if not exists
            if (!isset($this->groupedNoCaptureSamples[$analysisId])) {
                $this->groupedNoCaptureSamples[$analysisId] = [
                    'name' => $result->analysis_type->name ?? 'Unknown Analysis',
                    'samples' => []
                ];
            }

            // Check if sample is already added to this group
            $existingSampleIds = array_map(function($s) { return $s->id; }, $this->groupedNoCaptureSamples[$analysisId]['samples']);
            
            if (!in_array($result->sample->id, $existingSampleIds)) {
                $this->groupedNoCaptureSamples[$analysisId]['samples'][] = $result->sample;
            }
        }

        $this->hasNoCaptureSamples = count($this->groupedNoCaptureSamples) > 0;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function postAllResults(): void
    {
        // Dispatch event to trigger post results modal on the active formula worksheet
        $this->dispatch('triggerPostResults');
    }

    public function render()
    {
        return view('livewire.worksheets.worksheet-manager');
    }
}
