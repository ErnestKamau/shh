<?php

namespace App\Services\LogEntryWorksheets;

use App\Models\Formulars\GlobalVariable;
use App\Models\LogEntryWorksheets\SampleLogEntryWorksheetRow;
use Illuminate\Support\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class LogEntryColumnEvaluator
{
    public function __construct(
        protected LogEntryRowContextBuilder $contextBuilder,
    ) {}

    /**
     * Evaluate all derived columns for a row in column order.
     *
     * @param  Collection<int, \App\Models\LogEntryWorksheets\LogEntryWorksheetColumn>  $columns
     * @param  array<string, string|null>  $cellValues  keyed by column key
     * @return array<string, string|null>
     */
    public function evaluateRow(
        Collection $columns,
        SampleLogEntryWorksheetRow $row,
        array $cellValues,
    ): array {
        $context = $this->contextBuilder->buildForRow($row);
        $result = array_merge($context, $cellValues);
        $language = new ExpressionLanguage();
        $globals = GlobalVariable::active()->pluck('value', 'name')->toArray();

        foreach ($columns->sortBy('order') as $column) {
            if ($column->column_type !== 'derived' || ! $column->expression) {
                continue;
            }

            try {
                $evalContext = array_merge($result, $globals);
                $value = $language->evaluate($column->expression, $evalContext);
                $result[$column->key] = $value !== null ? (string) $value : null;
            } catch (\Throwable) {
                $result[$column->key] = null;
            }
        }

        return $result;
    }
}
