<?php

namespace App\Services\Monitoring;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

class FormulaEngineService
{
    protected ExpressionLanguage $expressionLanguage;

    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }

    public function evaluate(string $expression, array $variables = []): mixed
    {
        return $this->expressionLanguage->evaluate($expression, $variables);
    }

    public function evaluateSafe(string $expression, array $variables = [], mixed $default = null): mixed
    {
        try {
            return $this->evaluate($expression, $variables);
        } catch (Throwable) {
            return $default;
        }
    }

    public function normalizeBooleanResult(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        return in_array(strtolower((string) $value), ['true', 'pass', 'ok', 'in_range'], true);
    }

    public function validateExpression(string $expression, array $availableVariableNames = []): array
    {
        try {
            $this->expressionLanguage->parse($expression, $availableVariableNames);
            return [
                'valid' => true,
                'message' => 'Expression is valid.'
            ];
        } catch (Throwable $e) {
            return [
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
