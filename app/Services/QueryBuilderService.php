<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QueryBuilderService
{
    /**
     * Build SQL from visual builder config.
     */
    public function buildQuery(array $config): string
    {
        $table = $config['table'] ?? '';
        $columns = $config['columns'] ?? ['*'];
        $conditions = $config['conditions'] ?? [];
        $orderBy = $config['order_by'] ?? [];
        $limit = $config['limit'] ?? null;

        if (empty($table)) {
            throw new \InvalidArgumentException('Table name is required');
        }

        $query = DB::table($table);

        // Select columns
        if ($columns !== ['*']) {
            $query->select($columns);
        }

        // Apply conditions
        foreach ($conditions as $condition) {
            $field = $condition['field'] ?? '';
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? '';

            if (empty($field)) {
                continue;
            }

            switch (strtoupper($operator)) {
                case '=':
                case '!=':
                case '<>':
                case '<':
                case '<=':
                case '>':
                case '>=':
                    $query->where($field, $operator, $value);
                    break;
                case 'LIKE':
                    $query->where($field, 'like', $value);
                    break;
                case 'IN':
                    if (is_array($value)) {
                        $query->whereIn($field, $value);
                    }
                    break;
                case 'NOT IN':
                    if (is_array($value)) {
                        $query->whereNotIn($field, $value);
                    }
                    break;
                case 'IS NULL':
                    $query->whereNull($field);
                    break;
                case 'IS NOT NULL':
                    $query->whereNotNull($field);
                    break;
            }
        }

        // Apply ordering
        foreach ($orderBy as $order) {
            $field = $order['field'] ?? '';
            $direction = strtoupper($order['direction'] ?? 'ASC');
            if ($field && in_array($direction, ['ASC', 'DESC'])) {
                $query->orderBy($field, $direction);
            }
        }

        // Apply limit
        if ($limit && is_numeric($limit) && $limit > 0) {
            $query->limit((int)$limit);
        }

        return $query->toSql();
    }

    /**
     * Validate SQL syntax (sandboxed).
     */
    public function validateQuery(string $sql): array
    {
        $errors = [];
        
        // Only allow SELECT queries
        $sqlUpper = strtoupper(trim($sql));
        if (!Str::startsWith($sqlUpper, 'SELECT')) {
            $errors[] = 'Only SELECT queries are allowed';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check for dangerous keywords
        $dangerousKeywords = [
            'DROP', 'DELETE', 'UPDATE', 'INSERT', 'ALTER', 'CREATE',
            'TRUNCATE', 'EXEC', 'EXECUTE', 'CALL', 'GRANT', 'REVOKE'
        ];
        
        foreach ($dangerousKeywords as $keyword) {
            if (Str::contains($sqlUpper, $keyword)) {
                $errors[] = "Dangerous keyword '{$keyword}' is not allowed";
            }
        }

        // Check for semicolons (prevent multiple statements)
        if (substr_count($sql, ';') > 1) {
            $errors[] = 'Multiple statements are not allowed';
        }

        // Try to parse the query
        try {
            // Use a test connection to validate
            DB::connection()->getPdo()->prepare($sql);
        } catch (\PDOException $e) {
            $errors[] = 'SQL syntax error: ' . $e->getMessage();
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Execute query safely (sandboxed).
     */
    public function executeQuery(string $sql, array $bindings = []): array
    {
        // Validate first
        $validation = $this->validateQuery($sql);
        if (!$validation['valid']) {
            throw new \InvalidArgumentException('Invalid query: ' . implode(', ', $validation['errors']));
        }

        try {
            $results = DB::select($sql, $bindings);
            return [
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Preview query results.
     */
    public function previewQuery(array $config): array
    {
        try {
            if (isset($config['raw_sql']) && !empty($config['raw_sql'])) {
                // Raw SQL mode
                $validation = $this->validateQuery($config['raw_sql']);
                if (!$validation['valid']) {
                    return [
                        'success' => false,
                        'errors' => $validation['errors']
                    ];
                }
                return $this->executeQuery($config['raw_sql'], $config['bindings'] ?? []);
            } else {
                // Visual builder mode - check if we have table/columns
                if (empty($config['table'])) {
                    return [
                        'success' => false,
                        'error' => 'Table is required'
                    ];
                }
                $sql = $this->buildQuery($config);
                return $this->executeQuery($sql);
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * List available database tables.
     */
    public function getAvailableTables(): array
    {
        $tables = [];
        $connection = DB::connection();
        $databaseName = $connection->getDatabaseName();
        
        try {
            $tableList = DB::select("SHOW TABLES");
            $key = "Tables_in_{$databaseName}";
            
            foreach ($tableList as $table) {
                $tableName = $table->$key ?? null;
                if ($tableName && !Str::startsWith($tableName, 'migrations')) {
                    $tables[] = [
                        'name' => $tableName,
                        'label' => Str::title(str_replace('_', ' ', $tableName))
                    ];
                }
            }
        } catch (\Exception $e) {
            // Fallback: use Schema facade
            $tables = collect(Schema::getAllTables())
                ->map(function ($table) {
                    $name = is_array($table) ? ($table['name'] ?? $table['table_name'] ?? null) : $table;
                    if ($name && !Str::startsWith($name, 'migrations')) {
                        return [
                            'name' => $name,
                            'label' => Str::title(str_replace('_', ' ', $name))
                        ];
                    }
                    return null;
                })
                ->filter()
                ->values()
                ->toArray();
        }

        return $tables;
    }

    /**
     * Get columns for a table.
     */
    public function getTableColumns(string $tableName): array
    {
        try {
            $columns = Schema::getColumnListing($tableName);
            $columnDetails = [];
            
            foreach ($columns as $column) {
                $type = Schema::getColumnType($tableName, $column);
                $columnDetails[] = [
                    'name' => $column,
                    'label' => Str::title(str_replace('_', ' ', $column)),
                    'type' => $type
                ];
            }
            
            return $columnDetails;
        } catch (\Exception $e) {
            return [];
        }
    }
}

