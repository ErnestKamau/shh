<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\FormulaVersion;
use App\Models\Formulars\WorksheetExecution;
use App\Services\Formulars\WorksheetService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class WorksheetExecutor extends Component
{
    public $formulaVersion;
    public $executionMode = 'standalone';
    public $sampleId = null;
    public $batchId = null;
    public $inputs = [];
    public $executionResult = null;
    public $isExecuting = false;
    public $showSaveModal = false;
    public $saveExecution = false;

    // Messages
    public $message = '';
    public $messageType = '';

    public function mount(FormulaVersion $formulaVersion, string $executionMode = 'standalone', ?int $sampleId = null, ?int $batchId = null)
    {
        $this->formulaVersion = $formulaVersion;
        $this->executionMode = $executionMode;
        $this->sampleId = $sampleId;
        $this->batchId = $batchId;
        
        $this->initializeInputs();
    }

    public function render()
    {
        $inputSteps = $this->formulaVersion->formulaSteps()
            ->where('step_type', 'input')
            ->orderBy('step_number')
            ->get();

        return view('livewire.formulars.worksheet-executor', [
            'inputSteps' => $inputSteps,
        ]);
    }

    public function initializeInputs()
    {
        $inputSteps = $this->formulaVersion->formulaSteps()
            ->where('step_type', 'input')
            ->get();

        foreach ($inputSteps as $step) {
            $this->inputs[$step->variable_name] = '';
        }
    }

    public function executeWorksheet()
    {
        $this->isExecuting = true;
        $this->message = '';
        $this->messageType = '';

        try {
            $worksheetService = app(WorksheetService::class);
            
            // Validate inputs
            $validation = $worksheetService->validateInputs($this->formulaVersion, $this->inputs);
            if (!$validation['valid']) {
                $this->setMessage('Validation errors: ' . implode(', ', $validation['errors']), 'error');
                $this->isExecuting = false;
                return;
            }

            // Execute the worksheet
            $result = $worksheetService->executeWorksheet(
                $this->formulaVersion,
                $this->inputs,
                Auth::user(),
                $this->executionMode,
                $this->sampleId,
                $this->batchId,
                false // Don't save yet
            );

            // Transform result structure to match view expectations
            $this->executionResult = [
                'inputs' => $result['execution_data']['inputs'] ?? [],
                'derived_values' => $result['execution_data']['derived'] ?? [],
                'lookup_results' => $result['execution_data']['lookups'] ?? [],
                'final_result' => $result['execution_data']['final_result'] ?? null,
                'execution_details' => [
                    'steps_executed' => count($this->formulaVersion->formulaSteps),
                    'execution_time' => 'N/A',
                ],
                'raw_result' => $result, // Keep raw result for saving
            ];
            
            $this->setMessage('Worksheet executed successfully!', 'success');
            
            // Show save modal if in workflow mode
            if ($this->executionMode === 'workflow') {
                $this->showSaveModal = true;
            }
        } catch (\Exception $e) {
            $this->setMessage('Error executing worksheet: ' . $e->getMessage(), 'error');
        } finally {
            $this->isExecuting = false;
        }
    }

    public function saveExecution()
    {
        if (!$this->executionResult || !isset($this->executionResult['raw_result'])) {
            $this->setMessage('No execution result to save', 'error');
            return;
        }

        try {
            $worksheetService = app(WorksheetService::class);
            
            // Use the raw result from the previous execution to save
            $result = $worksheetService->executeWorksheet(
                $this->formulaVersion,
                $this->inputs,
                Auth::user(),
                $this->executionMode,
                $this->sampleId,
                $this->batchId,
                true // Save the execution
            );

            $this->showSaveModal = false;
            $this->setMessage('Worksheet execution saved successfully! Execution ID: ' . ($result['execution_id'] ?? 'N/A'), 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error saving execution: ' . $e->getMessage(), 'error');
        }
    }

    public function resetWorksheet()
    {
        $this->initializeInputs();
        $this->executionResult = null;
        $this->message = '';
        $this->messageType = '';
    }

    public function updateInput($variableName, $value)
    {
        $this->inputs[$variableName] = $value;
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function closeSaveModal()
    {
        $this->showSaveModal = false;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}