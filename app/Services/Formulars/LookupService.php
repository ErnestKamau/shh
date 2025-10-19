<?php

namespace App\Services\Formulars;

use App\Models\Formulars\LookupTable;
use App\Models\Formulars\LookupTableEntry;
use Exception;

class LookupService
{
    /**
     * Get a value from a lookup table by keys (supports both types).
     */
    public function getValue(int $lookupTableId, $input): mixed
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable) {
            throw new Exception("Lookup table not found: {$lookupTableId}");
        }

        if (!$lookupTable->is_active) {
            throw new Exception("Lookup table is not active: {$lookupTable->name}");
        }

        // Route based on lookup type
        if ($lookupTable->isRangeBased()) {
            $result = $this->getRangeValue($lookupTableId, (float)$input);
            return $result ? $result['value'] : null;
        } else {
            return $this->getKeyValue($lookupTableId, $input);
        }
    }

    /**
     * Get a value from a key-value comparison lookup table.
     */
    protected function getKeyValue(int $lookupTableId, array $keys): ?string
    {
        // Sort keys for consistent comparison
        ksort($keys);
        
        $entry = LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->whereKeys($keys)
            ->first();

        return $entry ? $entry->value : null;
    }

    /**
     * Get a value from a range-based lookup table.
     */
    public function getRangeValue(int $lookupTableId, float $inputValue): ?array
    {
        $lookupTable = LookupTable::find($lookupTableId);
        
        if (!$lookupTable || !$lookupTable->isRangeBased()) {
            throw new Exception("Invalid range-based lookup table");
        }

        // Find entry where low <= inputValue < high (or high is null)
        // Sort entries by low value to ensure we check in order
        $entries = LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->get()
            ->sortBy(function($entry) {
                return $entry->keys['low'] ?? PHP_INT_MAX;
            });
        
        foreach ($entries as $entry) {
            $low = isset($entry->keys['low']) ? (float)$entry->keys['low'] : null;
            $high = isset($entry->keys['high']) ? (float)$entry->keys['high'] : null;
            
            if ($low === null) {
                continue;
            }
            
            // Check if value falls in range
            if ($high === null) {
                // Open-ended range (low and above)
                if ($inputValue >= $low) {
                    return [
                        'value' => $entry->value,
                        'interpretation' => $entry->keys['value_interpretation'] ?? null,
                        'matched_range' => ['low' => $low, 'high' => 'infinite']
                    ];
                }
            } else {
                // Bounded range: low <= input < high
                if ($inputValue >= $low && $inputValue < $high) {
                    return [
                        'value' => $entry->value,
                        'interpretation' => $entry->keys['value_interpretation'] ?? null,
                        'matched_range' => ['low' => $low, 'high' => $high]
                    ];
                }
            }
        }
        
        return null; // No matching range found
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

        // Sort keys for consistent comparison
        ksort($keys);
        
        $entry = LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->whereKeys($keys)
            ->first();
            
        if ($entry) {
            $entry->update(['value' => $value]);
        } else {
            $entry = LookupTableEntry::create([
                'lookup_table_id' => $lookupTableId,
                'keys' => $keys,
                'value' => $value,
            ]);
        }

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

        // Sort keys for consistent comparison
        ksort($keys);
        
        return LookupTableEntry::where('lookup_table_id', $lookupTableId)
            ->whereKeys($keys)
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
                if ($lookupTable->isRangeBased()) {
                    // Range-based import
                    $keys = [
                        'low' => isset($row['low']) ? (float)$row['low'] : null,
                        'high' => isset($row['high']) && $row['high'] !== null && $row['high'] !== '' ? (float)$row['high'] : null,
                    ];
                    
                    // Add interpretation if configured and present
                    if ($lookupTable->value_interpretation_column && isset($row['value_interpretation'])) {
                        $keys['value_interpretation'] = $row['value_interpretation'];
                    }
                    
                    $value = $row[$lookupTable->value_column] ?? '';
                } else {
                    // Key-value import
                    $keys = [];
                    foreach ($lookupTable->key_columns as $keyColumn) {
                        $keys[$keyColumn] = $row[$keyColumn] ?? '';
                    }
                    
                    $value = $row[$lookupTable->value_column] ?? '';
                }
                
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
            if ($lookupTable->isRangeBased()) {
                // Range-based export
                $row = [
                    'low' => $entry->keys['low'] ?? '',
                    'high' => $entry->keys['high'] ?? null,
                    $lookupTable->value_column => $entry->value,
                ];
                
                if ($lookupTable->value_interpretation_column && isset($entry->keys['value_interpretation'])) {
                    $row['value_interpretation'] = $entry->keys['value_interpretation'];
                }
            } else {
                // Key-value export
                $row = $entry->keys;
                $row[$lookupTable->value_column] = $entry->value;
            }
            
            $data[] = $row;
        }

        return $data;
    }
}

