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
        $binding = $field->datasetBinding;

        if (!$binding) {
            return [];
        }

        $query = DB::table($binding->table_name)
            ->select($binding->column_value . ' as value', $binding->column_label . ' as label');

        if ($binding->filters) {
            foreach ($binding->filters as $filter) {
                // Assuming filter structure: ['column' => 'col', 'operator' => '=', 'value' => 'val']
                if (isset($filter['column'], $filter['operator'], $filter['value'])) {
                    $query->where($filter['column'], $filter['operator'], $filter['value']);
                }
            }
        }

        if ($binding->order_by_column) {
            $query->orderBy($binding->order_by_column, $binding->order_direction ?? 'asc');
        }

        return $query->get()->toArray();
    }
}
