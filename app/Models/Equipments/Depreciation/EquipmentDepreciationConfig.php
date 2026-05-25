<?php

namespace App\Models\Equipments\Depreciation;

use App\Enums\Equipment\DecliningBalanceType;
use App\Enums\Equipment\DepreciationFrequency;
use App\Enums\Equipment\DepreciationStatus;
use App\Models\Equipments\Equipment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EquipmentDepreciationConfig extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'equipment_depreciation_configs';

    protected $fillable = [
        'equipment_id',
        'company_id',
        'depreciation_method_id',
        'enable_depreciation',
        'currency',
        'freight_cost',
        'capitalized_amount',
        'depreciation_start_date',
        'useful_life_years',
        'salvage_value',
        'frequency',
        'frequencies',
        'depreciation_rate',
        'declining_balance_type',
        'expected_total_units',
        'unit_type',
        'current_units_used',
        'usage_source',
        'initial_book_value',
        'current_book_value',
        'accumulated_depreciation',
        'current_period_depreciation',
        'status',
        'active_schedule_version_id',
        'last_processed_period_date',
    ];

    protected function casts(): array
    {
        return [
            'enable_depreciation' => 'boolean',
            'freight_cost' => 'decimal:2',
            'capitalized_amount' => 'decimal:2',
            'salvage_value' => 'decimal:2',
            'depreciation_rate' => 'decimal:4',
            'expected_total_units' => 'decimal:4',
            'current_units_used' => 'decimal:4',
            'initial_book_value' => 'decimal:2',
            'current_book_value' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'current_period_depreciation' => 'decimal:2',
            'depreciation_start_date' => 'date',
            'last_processed_period_date' => 'date',
            'frequency' => DepreciationFrequency::class,
            'frequencies' => 'array',
            'declining_balance_type' => DecliningBalanceType::class,
            'status' => DepreciationStatus::class,
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(DepreciationMethod::class, 'depreciation_method_id');
    }

    public function activeScheduleVersion(): BelongsTo
    {
        return $this->belongsTo(DepreciationScheduleVersion::class, 'active_schedule_version_id');
    }

    public function scheduleVersions(): HasMany
    {
        return $this->hasMany(DepreciationScheduleVersion::class, 'equipment_depreciation_config_id');
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(DepreciationLedgerEntry::class, 'equipment_depreciation_config_id');
    }

    public function appraisals(): HasMany
    {
        return $this->hasMany(EquipmentAppraisal::class, 'equipment_depreciation_config_id');
    }

    public function depreciableBase(): float
    {
        return max(0, (float) $this->capitalized_amount - (float) $this->salvage_value);
    }

    /**
     * @return array<int, string>
     */
    public function resolvedFrequencies(): array
    {
        $frequencies = $this->frequencies;
        if (is_array($frequencies) && count($frequencies) > 0) {
            return array_values(array_unique($frequencies));
        }

        $primary = $this->frequency;
        if ($primary instanceof DepreciationFrequency) {
            return [$primary->value];
        }

        if (is_string($primary) && $primary !== '') {
            return [$primary];
        }

        return ['monthly'];
    }

    public function frequenciesLabel(): string
    {
        return implode(', ', array_map(
            fn (string $f) => ucfirst($f),
            $this->resolvedFrequencies()
        ));
    }
}
