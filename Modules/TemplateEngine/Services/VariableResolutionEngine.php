<?php

namespace Modules\TemplateEngine\Services;

use Modules\TemplateEngine\Models\FormTemplate;
use Modules\TemplateEngine\Models\FormTemplateVariable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;

class VariableResolutionEngine
{
    protected $resolvedVariables = [];
    protected $context = [];

    /**
     * Resolve all variables for a given template using the provided context.
     * 
     * @param FormTemplate $template
     * @param array $context (e.g., ['user_id' => 1, 'record_id' => 5])
     * @return array Resolved variables [name => value]
     */
    public function resolveVariables(FormTemplate $template, array $context = []): array
    {
        $this->context = $context;
        $this->resolvedVariables = [];

        $variables = $template->variables;

        // 1. Resolve System Variables first (as others might depend on them)
        foreach ($variables->where('type', 'system') as $var) {
            $this->resolvedVariables[$var->name] = $this->resolveSystemVariable($var);
        }

        // 2. Resolve Static Variables
        foreach ($variables->where('type', 'static') as $var) {
            $this->resolvedVariables[$var->name] = $this->resolveStaticVariable($var);
        }

        // 3. Resolve Database Variables (can depend on above)
        foreach ($variables->where('type', 'database') as $var) {
            $this->resolvedVariables[$var->name] = $this->resolveDatabaseVariable($var);
        }

        return $this->resolvedVariables;
    }

    protected function resolveSystemVariable(FormTemplateVariable $var)
    {
        $source = $var->config['source'] ?? '';
        
        switch ($source) {
            case 'auth_user':
                return Auth::user();
            case 'auth_user_name':
                return Auth::user()->name ?? 'Guest';
            case 'current_date':
                return Carbon::now()->toDateString();
            case 'current_datetime':
                return Carbon::now()->toDateTimeString();
            default:
                return null;
        }
    }

    protected function resolveStaticVariable(FormTemplateVariable $var)
    {
        $value = $var->config['value'] ?? null;
        
        // Type casting
        if ($var->data_type === 'number') return is_numeric($value) ? $value + 0 : 0;
        if ($var->data_type === 'boolean') return (bool) $value;
        if ($var->data_type === 'date') return $value; // Carbon::parse($value)?
        
        return (string) $value;
    }

    protected function resolveDatabaseVariable(FormTemplateVariable $var)
    {
        $config = $var->config;
        $table = $config['table'] ?? null;
        
        if (!$table) return null;

        try {
            $query = DB::table($table);

            // Select
            if (!empty($config['select'])) {
                $query->select($config['select']);
            }

            // Filters
            if (!empty($config['filters']) && is_array($config['filters'])) {
                foreach ($config['filters'] as $filter) {
                    $field = $filter['field'];
                    $operator = $filter['operator'] ?? '=';
                    $val = $filter['value'];

                    // Interpolate value if it refers to another variable {{ var }}
                    $val = $this->interpolateValue($val);

                    $query->where($field, $operator, $val);
                }
            }

            // Sort
            if (!empty($config['sort']['field'])) {
                $query->orderBy($config['sort']['field'], $config['sort']['direction'] ?? 'asc');
            }

            // Limit
            if (!empty($config['limit'])) {
                $query->limit($config['limit']);
            }

            // Return Type
            if (($config['return_type'] ?? 'collection') === 'single') {
                return $query->first();
            } else {
                return $query->get();
            }

        } catch (Exception $e) {
            // Log error? Return empty?
            return []; // Fail gracefully for now
        }
    }

    protected function interpolateValue($value)
    {
        if (is_string($value) && preg_match('/^\{\{\s*(.+?)\s*\}\}$/', $value, $matches)) {
            $varName = $matches[1];
            // Check context or previously resolved variables
            if (isset($this->resolvedVariables[$varName])) {
                return $this->resolvedVariables[$varName];
            }
            if (isset($this->context[$varName])) {
                return $this->context[$varName];
            }
        }
        return $value;
    }
}
