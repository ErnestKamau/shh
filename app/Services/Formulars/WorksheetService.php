<?php

namespace App\Services\Formulars;

use App\Models\Formulars\FormulaVersion;
use App\Models\Formulars\WorksheetExecution;
use App\Models\User;
use Exception;

class WorksheetService
{
    protected FormulaEvaluator $formulaEvaluator;

    public function __construct(FormulaEvaluator $formulaEvaluator)
    {
        $this->formulaEvaluator = $formulaEvaluator;
    }

    /**
     * Execute a worksheet and optionally save the results.
     */
    public function executeWorksheet(
        FormulaVersion $formulaVersion,
        array $inputs,
        User $executedBy,
        string $executionMode = 'standalone',
        ?int $sampleId = null,
        ?int $batchId = null,
        bool $save = false
    ): array {
        // Execute the formula
        $result = $this->formulaEvaluator->execute($formulaVersion, $inputs, $sampleId, $batchId);

        // Create execution record if saving
        if ($save) {
            $execution = WorksheetExecution::create([
                'formula_version_id' => $formulaVersion->id,
                'sample_id' => $sampleId,
                'batch_id' => $batchId,
                'executed_by' => $executedBy->id,
                'execution_data' => $result['execution_data'],
                'final_result' => $result['execution_data']['final_result'],
                'execution_mode' => $executionMode,
                'is_saved' => true,
            ]);

            $result['execution_id'] = $execution->id;
        }

        return $result;
    }

    /**
     * Get worksheet execution history.
     */
    public function getExecutionHistory(
        ?int $formulaVersionId = null,
        ?int $sampleId = null,
        ?int $batchId = null,
        ?int $executedBy = null,
        ?string $executionMode = null,
        bool $savedOnly = true
    ) {
        $query = WorksheetExecution::with(['formulaVersion.formula', 'executor', 'sample', 'batch']);

        if ($formulaVersionId) {
            $query->where('formula_version_id', $formulaVersionId);
        }

        if ($sampleId) {
            $query->where('sample_id', $sampleId);
        }

        if ($batchId) {
            $query->where('batch_id', $batchId);
        }

        if ($executedBy) {
            $query->where('executed_by', $executedBy);
        }

        if ($executionMode) {
            $query->where('execution_mode', $executionMode);
        }

        if ($savedOnly) {
            $query->where('is_saved', true);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Get a specific worksheet execution.
     */
    public function getExecution(int $executionId): ?WorksheetExecution
    {
        return WorksheetExecution::with(['formulaVersion.formula', 'executor', 'sample', 'batch'])
            ->find($executionId);
    }

    /**
     * Delete a worksheet execution.
     */
    public function deleteExecution(int $executionId): bool
    {
        $execution = WorksheetExecution::find($executionId);
        
        if (!$execution) {
            return false;
        }

        return $execution->delete();
    }

    /**
     * Get execution statistics for a formula version.
     */
    public function getExecutionStats(int $formulaVersionId): array
    {
        $executions = WorksheetExecution::where('formula_version_id', $formulaVersionId)
            ->where('is_saved', true);

        return [
            'total_executions' => $executions->count(),
            'workflow_executions' => $executions->where('execution_mode', 'workflow')->count(),
            'standalone_executions' => $executions->where('execution_mode', 'standalone')->count(),
            'last_execution' => $executions->latest()->first()?->created_at,
            'unique_users' => $executions->distinct('executed_by')->count('executed_by'),
        ];
    }

    /**
     * Get execution statistics for a sample.
     */
    public function getSampleExecutionStats(int $sampleId): array
    {
        $executions = WorksheetExecution::where('sample_id', $sampleId)
            ->where('is_saved', true);

        return [
            'total_executions' => $executions->count(),
            'formulas_used' => $executions->distinct('formula_version_id')->count('formula_version_id'),
            'last_execution' => $executions->latest()->first()?->created_at,
        ];
    }

    /**
     * Get execution statistics for a batch.
     */
    public function getBatchExecutionStats(int $batchId): array
    {
        $executions = WorksheetExecution::where('batch_id', $batchId)
            ->where('is_saved', true);

        return [
            'total_executions' => $executions->count(),
            'formulas_used' => $executions->distinct('formula_version_id')->count('formula_version_id'),
            'samples_affected' => $executions->distinct('sample_id')->count('sample_id'),
            'last_execution' => $executions->latest()->first()?->created_at,
        ];
    }

    /**
     * Validate worksheet inputs against formula requirements.
     */
    public function validateInputs(FormulaVersion $formulaVersion, array $inputs): array
    {
        $inputSteps = $formulaVersion->formulaSteps()->where('step_type', 'input')->get();
        $errors = [];

        foreach ($inputSteps as $step) {
            if (!isset($inputs[$step->variable_name]) || $inputs[$step->variable_name] === '') {
                $errors[] = "Input required for: {$step->label}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get available formulas for a sample.
     */
    public function getAvailableFormulasForSample(int $sampleId): array
    {
        // This would typically involve checking which formulas are linked to the sample's analysis types
        // For now, return all active formulas
        return FormulaVersion::with('formula')
            ->where('is_active', true)
            ->get()
            ->map(function ($version) {
                return [
                    'id' => $version->id,
                    'formula_name' => $version->formula->name,
                    'version_number' => $version->version_number,
                    'description' => $version->formula->description,
                ];
            })
            ->toArray();
    }

    /**
     * Get available formulas for a batch.
     */
    public function getAvailableFormulasForBatch(int $batchId): array
    {
        // This would typically involve checking which formulas are linked to the batch's analysis types
        // For now, return all active formulas
        return $this->getAvailableFormulasForSample($batchId);
    }
}
