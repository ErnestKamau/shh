<?php

namespace App\Services\Equipment;

use App\Models\Equipments\Logbook\EquipmentLogbookColumn;
use App\Models\Formulars\GlobalVariable;
use Illuminate\Support\Collection;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class EquipmentLogbookEvaluator
{
    /**
     * @param  Collection<int, EquipmentLogbookColumn>  $columns
     * @param  array<string, string|null>  $valuesByKey
     * @return array<string, string|null>
     */
    public function evaluateDerived(Collection $columns, array $valuesByKey): array
    {
        $result = $valuesByKey;
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
