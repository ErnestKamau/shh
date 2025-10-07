<?php

namespace App\Services\Formulars;

use App\Models\Formulars\LookupTable;
use App\Models\Formulars\LookupTableEntry;
use Exception;

class LookupService
{
    /**
     * Get a value from a lookup table by keys.
     */
    public function getValue(int $lookupTableId, array $keys): ?string
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        if (!$lookupTable->is_active) {
            throw new Exception("Lookup table is not active: {$lookupTable->name}");
        }

        // Normalize keys for consistent JSON encoding
        ksort($keys);
        
        $entry = LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->where('keys', json_encode($keys, JSON_SORT_KEYS))
            ->first();

        return $entry ? $entry->value : null;
    }

    /**
     * Get all values from a lookup table.
     */
    public function getAllValues(int $lookupTableId): array
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        return $lookupTable->entries()
            ->get()
            ->map(function ($entry) {
                return [
                    'keys' => $entry->keys,
                    'value' => $entry->value,
                ];
            })
            ->toArray();
    }

    /**
     * Add or update a lookup table entry.
     */
    public function setValue(int $lookupTableId, array $keys, string $value): LookupTableEntry
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        // Normalize keys for consistent JSON encoding
        ksort($keys);
        
        $entry = LookupTableEntry::updateOrCreate(
            [
                'lookup_table_id' => $lookupTableId,
                'keys' => json_encode($keys, JSON_SORT_KEYS),
            ],
            [
                'value' => $value,
            ]
        );

        return $entry;
    }

    /**
     * Delete a lookup table entry.
     */
    public function deleteValue(int $lookupTableId, array $keys): bool
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        // Normalize keys for consistent JSON encoding
        ksort($keys);
        
        return LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->where('keys', json_encode($keys, JSON_SORT_KEYS))
            ->delete() > 0;
    }

    /**
     * Clear all entries from a lookup table.
     */
    public function clearTable(int $lookupTableId): int
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        return $lookupTable->entries()->delete();
    }

    /**
     * Validate lookup table structure.
     */
    public function validateTableStructure(int $lookupTableId, array $sampleData): array
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            return [
                'valid' => false,
                'message' => "Lookup table not found: {$lookupTableId}",
            ];
        }

        $requiredColumns = array_merge($lookupTable->key_columns, [$lookupTable->value_column]);
        $errors = [];

        foreach ($sampleData as $index => $row) {
            foreach ($requiredColumns as $column) {
                if (!isset($row[$column]) || $row[$column] === '') {
                    $errors[] = "Row " . ($index + 1) . ": Missing required column '{$column}'";
                }
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Import data into a lookup table.
     */
    public function importData(int $lookupTableId, array $data): array
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                // Extract keys and value
                $keys = [];
                foreach ($lookupTable->key_columns as $keyColumn) {
                    $keys[$keyColumn] = $row[$keyColumn] ?? '';
                }
                
                $value = $row[$lookupTable->value_column] ?? '';
                
                // Create or update entry
                $this->setValue($lookupTableId, $keys, $value);
                $imported++;
                
            } catch (Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
            }
        }

        return [
            'imported' => $imported,
            'errors' => $errors,
            'total' => count($data),
        ];
    }

    /**
     * Export data from a lookup table.
     */
    public function exportData(int $lookupTableId): array
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        $data = [];
        
        foreach ($lookupTable->entries as $entry) {
            $row = $entry->keys;
            $row[$lookupTable->value_column] = $entry->value;
            $data[] = $row;
        }

        return $data;
    }
}
