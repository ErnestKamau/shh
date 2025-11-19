<?php

namespace App\Http\Controllers;

use App\Services\QueryBuilderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QueryBuilderController extends Controller
{
    protected QueryBuilderService $queryBuilder;

    public function __construct(QueryBuilderService $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Get available tables.
     */
    public function getTables(): JsonResponse
    {
        try {
            $tables = $this->queryBuilder->getAvailableTables();
            return response()->json([
                'success' => true,
                'tables' => $tables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get columns for a table.
     */
    public function getColumns(string $table): JsonResponse
    {
        try {
            $columns = $this->queryBuilder->getTableColumns($table);
            return response()->json([
                'success' => true,
                'columns' => $columns
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview query results.
     */
    public function preview(Request $request): JsonResponse
    {
        $request->validate([
            'table' => 'required|string',
            'columns' => 'nullable|array',
            'conditions' => 'nullable|array',
            'order_by' => 'nullable|array',
            'limit' => 'nullable|integer|min:1',
            'raw_sql' => 'nullable|string'
        ]);

        try {
            $config = $request->only(['table', 'columns', 'conditions', 'order_by', 'limit', 'raw_sql']);
            $result = $this->queryBuilder->previewQuery($config);
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
