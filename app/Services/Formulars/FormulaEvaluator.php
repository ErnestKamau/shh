<?php

namespace App\Services\Formulars;

use App\Models\Formulars\FormulaVersion;
use App\Models\Formulars\FormulaStep;
use App\Models\Formulars\GlobalVariable;
use App\Services\Formulars\LookupService;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Exception;

class FormulaEvaluator
{
    protected ExpressionLanguage $expressionLanguage;
    protected LookupService $lookupService;

    public function __construct(LookupService $lookupService)
    {
        $this->expressionLanguage = new ExpressionLanguage();
        $this->lookupService = $lookupService;
    }

    /**
     * Execute a formula version with given inputs.
     */
    public function execute(FormulaVersion $formulaVersion, array $inputs = [], ?int $sampleId = null, ?int $batchId = null): array
    {
        $steps = $formulaVersion->formulaSteps;
        $variables = [];
        $executionData = [
            'inputs' => $inputs,
            'derived' => [],
            'lookups' => [],
            'final_result' => null,
        ];

        foreach ($steps as $step) {
            $value = $this->executeStep($step, $variables, $inputs, $sampleId, $batchId);
            $variables[$step->variable_name] = $value;

            // Store in appropriate execution data category
            switch ($step->step_type) {
                case 'input':
                    $executionData['inputs'][$step->variable_name] = $value;
                    break;
                case 'derived':
                    $executionData['derived'][$step->variable_name] = $value;
                    break;
                case 'lookup':
                    $executionData['lookups'][$step->variable_name] = $value;
                    break;
            }
        }

        // Set final result (last variable)
        if (!empty($variables)) {
            $lastVariable = array_key_last($variables);
            $executionData['final_result'] = $variables[$lastVariable];
        }

        return [
            'variables' => $variables,
            'execution_data' => $executionData,
        ];
    }

    /**
     * Execute a single formula step.
     */
    protected function executeStep(FormulaStep $step, array $variables, array $inputs, ?int $sampleId, ?int $batchId): mixed
    {
        switch ($step->step_type) {
            case 'input':
                return $inputs[$step->variable_name] ?? null;

            case 'derived':
                return $this->evaluateExpression($step->expression, $variables, $inputs);

            case 'lookup':
                return $this->executeLookup($step, $variables, $inputs, $sampleId, $batchId);

            default:
                throw new Exception("Unknown step type: {$step->step_type}");
        }
    }

    /**
     * Evaluate a mathematical expression.
     */
    protected function evaluateExpression(string $expression, array $variables, array $inputs): mixed
    {
        try {
            // Merge variables and inputs for expression evaluation
            $context = array_merge($variables, $inputs, $this->getGlobalVariables());
            
            return $this->expressionLanguage->evaluate($expression, $context);
        } catch (SyntaxError $e) {
            throw new Exception("Expression syntax error: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Expression evaluation error: " . $e->getMessage());
        }
    }

    /**
     * Execute a lookup step.
     */
    protected function executeLookup(FormulaStep $step, array $variables, array $inputs, ?int $sampleId, ?int $batchId): mixed
    {
        $config = $step->lookup_config;
        
        if (!$config || !isset($config['lookup_table_id'])) {
            throw new Exception("Lookup configuration missing for step: {$step->variable_name}");
        }

        // Build lookup keys from expression or direct values
        $keys = $this->buildLookupKeys($config, $variables, $inputs, $sampleId, $batchId);
        
        return $this->lookupService->getValue($config['lookup_table_id'], $keys);
    }

    /**
     * Build lookup keys from configuration.
     */
    protected function buildLookupKeys(array $config, array $variables, array $inputs, ?int $sampleId, ?int $batchId): array
    {
        $keys = [];
        
        if (isset($config['key_expressions'])) {
            // Evaluate key expressions
            foreach ($config['key_expressions'] as $keyName => $expression) {
                $keys[$keyName] = $this->evaluateExpression($expression, $variables, $inputs);
            }
        } elseif (isset($config['key_values'])) {
            // Use direct key values
            $keys = $config['key_values'];
        } else {
            throw new Exception("No key configuration found for lookup step");
        }

        return $keys;
    }

    /**
     * Get all global variables as an associative array.
     */
    protected function getGlobalVariables(): array
    {
        return GlobalVariable::active()
            ->pluck('value', 'name')
            ->toArray();
    }

    /**
     * Validate a formula expression.
     */
    public function validateExpression(string $expression, array $availableVariables = []): array
    {
        try {
            // Add global variables to available variables
            $context = array_merge($availableVariables, $this->getGlobalVariables());
            
            // Try to compile the expression
            $this->expressionLanguage->compile($expression, array_keys($context));
            
            return [
                'valid' => true,
                'message' => 'Expression is valid',
            ];
        } catch (SyntaxError $e) {
            return [
                'valid' => false,
                'message' => "Syntax error: " . $e->getMessage(),
            ];
        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => "Error: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available variables for a formula version.
     */
    public function getAvailableVariables(FormulaVersion $formulaVersion): array
    {
        $variables = [];
        
        foreach ($formulaVersion->formulaSteps as $step) {
            $variables[$step->variable_name] = $step->label;
        }
        
        return $variables;
    }
}
