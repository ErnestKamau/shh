<?php

namespace App\Models\Formulars;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LookupTable extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'key_columns',
        'value_column',
        'lookup_type',
        'range_variable_name',
        'value_interpretation_column',
        'key_label',
        'value_label',
        'is_active',
        'is_standard',
        'show_on_report',
    ];

    protected $casts = [
        'key_columns' => 'array',
        'is_active' => 'boolean',
        'is_standard' => 'boolean',
        'show_on_report' => 'boolean',
    ];

    /**
     * Get the lookup table entries for this table.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(LookupTableEntry::class);
    }

    /**
     * Scope for active lookup tables.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get a value from the lookup table by keys.
     */
    public function getValue(array $keys): ?string
    {
        // Sort keys for consistent comparison
        ksort($keys);
        
        // Use JSON comparison - MySQL will handle the JSON column properly
        $entry = $this->entries()
            ->whereRaw('JSON_CONTAINS(`keys`, ?) AND JSON_CONTAINS(?, `keys`)', [
                json_encode($keys),
                json_encode($keys)
            ])
            ->first();

        return $entry ? $entry->value : null;
    }

    /**
     * Check if this is a range-based lookup table.
     */
    public function isRangeBased(): bool
    {
        return $this->lookup_type === 'range_based';
    }

    /**
     * Check if this is a key-value comparison lookup table.
     */
    public function isKeyValueComparison(): bool
    {
        return $this->lookup_type === 'key_value_comparison' || $this->lookup_type === null;
    }

    /**
     * Get compatible lookup tables that can be used as alternatives.
     * Compatible tables must have:
     * - Same lookup_type (range_based vs key_value_comparison)
     * - Same key structure
     * - Active status
     */
    public function getCompatibleTables(): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('id', '!=', $this->id)
            ->where('is_active', true);

        if ($this->isRangeBased()) {
            // For range-based: same type and similar structure
            return $query->where('lookup_type', 'range_based')->get();
        } else {
            // For key-value: same type and exact key columns match
            $query->where(function($q) {
                $q->where('lookup_type', 'key_value_comparison')
                  ->orWhereNull('lookup_type');
            });

            // Get all candidates and filter by matching key_columns structure
            $thisKeyColumns = $this->key_columns ?? [];
            sort($thisKeyColumns);

            return $query->get()->filter(function($table) use ($thisKeyColumns) {
                $tableKeyColumns = $table->key_columns ?? [];
                sort($tableKeyColumns);
                return $tableKeyColumns === $thisKeyColumns;
            });
        }
    }

    /**
     * Check if another lookup table is compatible with this one.
     */
    public function isCompatibleWith(LookupTable $other): bool
    {
        // Must be active
        if (!$other->is_active) {
            return false;
        }

        // Must have same lookup type
        if ($this->isRangeBased() !== $other->isRangeBased()) {
            return false;
        }

        // For range-based, types must match
        if ($this->isRangeBased()) {
            return $other->isRangeBased();
        }

        // For key-value, key columns must match exactly
        $thisKeyColumns = $this->key_columns ?? [];
        $otherKeyColumns = $other->key_columns ?? [];
        sort($thisKeyColumns);
        sort($otherKeyColumns);

        return $thisKeyColumns === $otherKeyColumns;
    }
}
