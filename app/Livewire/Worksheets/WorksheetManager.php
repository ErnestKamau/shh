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
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function postAllResults(): void
    {
        try {
            DB::beginTransaction();
            
            // Get all worksheets for this batch and formula
            $formulas = $this->formulas;
            if ($formulas instanceof \Illuminate\Database\Eloquent\Collection) {
                $formulaIds = $formulas->pluck('id')->toArray();
            } else {
                $formulaIds = collect($formulas)->pluck('id')->toArray();
            }
            $worksheets = SampleCapturedWorksheetFormula::where('sample_header_id', $this->batch->id)
                ->whereIn('formular_id', $formulaIds)
                ->get();
            
            $updatedCount = 0;
            foreach ($worksheets as $worksheet) {
                if ($worksheet->final_result && $worksheet->captured_result_id) {
                    $capturedResult = CapturedResult::find($worksheet->captured_result_id);
                    if ($capturedResult) {
                        $capturedResult->result = $worksheet->final_result;
                        $capturedResult->save();
                        $updatedCount++;
                    }
                }
            }
            
            DB::commit();
            session()->flash('message', "Successfully posted {$updatedCount} results to captured results!");
            session()->flash('messageType', 'success');
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('message', 'Error posting results: ' . $e->getMessage());
            session()->flash('messageType', 'error');
        }
    }

    public function render()
    {
        return view('livewire.worksheets.worksheet-manager');
    }
}
