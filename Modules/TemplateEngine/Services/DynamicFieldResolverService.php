<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormField;
use Illuminate\Support\Facades\DB;

class DynamicFieldResolverService
{
    /**
     * Resolve options for a dynamic field.
     */
    public function resolveOptions(FormField $field): array
    {
        // Backward compatibility: If field has direct binding
        if ($field->datasetBinding) {
            $bindingData = $field->datasetBinding->toArray();
            // Lists need all columns
            $fetchAllColumns = in_array($field->type, ['ul', 'ol']);
            return $this->resolveBinding($bindingData, $fetchAllColumns);
        }
        
        return [];
    }

    /**
     * Resolve data from a binding configuration array.
     * 
     * @param array $binding
     * @param bool $fetchAllColumns Whether to select * (for lists) or just label/value (for inputs)
     */
    public function resolveBinding(array $binding, bool $fetchAllColumns = false): array
    {
        if (empty($binding['table_name'])) {
            return [];
        }

        $query = DB::table($binding['table_name']);

        if ($fetchAllColumns) {
            $query->select('*');
            if (!empty($binding['column_value'])) {
                $query->addSelect($binding['column_value'] . ' as value');
            }
            if (!empty($binding['column_label'])) {
                $query->addSelect($binding['column_label'] . ' as label');
            }
        } else {
             // For dropdowns, we only need value and label
            $valCol = $binding['column_value'] ?? 'id';
            $lblCol = $binding['column_label'] ?? 'name';
            $query->select("{$valCol} as value", "{$lblCol} as label");
        }

        if (!empty($binding['filters']) && is_array($binding['filters'])) {
            foreach ($binding['filters'] as $filter) {
                if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                    $query->where($filter['column'], $filter['operator'], $filter['value']);
                }
            }
        }

        if (!empty($binding['order_by_column'])) {
            $query->orderBy($binding['order_by_column'], $binding['order_direction'] ?? 'asc');
        }

        return $query->get()->toArray();
    }
}
