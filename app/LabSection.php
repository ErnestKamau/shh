<?php

namespace App;

use App\Models\Equipments\Equipment;
use App\ReportingUnit;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class LabSection extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'lab_sections';

    protected $fillable = [
        'lab_id',
        'name',
        'code',
        'description',
        'does_environmental_analysis',
        'equipment_id',
        'expected_value_type',
        'expected_value',
        'expected_min',
        'expected_max',
        'optimum_level',
        'result_nature',
        'reading_frequency',
        'reading_frequency_interval',
        'reading_frequency_schedule',
        'reporting_unit',
        'active',
        'company_id',
    ];

    protected $casts = [
        'does_environmental_analysis' => 'boolean',
        'active' => 'boolean',
        'expected_min' => 'float',
        'expected_max' => 'float',
        'reading_frequency' => 'integer',
        'reading_frequency_interval' => 'float',
        'reading_frequency_schedule' => 'array',
    ];

    public function lab(): BelongsTo
    {
        return $this->belongsTo(Lab::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function reportingUnit(): BelongsTo
    {
        return $this->belongsTo(ReportingUnit::class, 'reporting_unit');
    }

    public function decontaminationAreas(): HasMany
    {
        return $this->hasMany(LabDecontaminationArea::class);
    }

    public function formattedOptimumLevel(): string
    {
        $unitSuffix = filled($this->reportingUnit?->name)
            ? ' '.$this->reportingUnit->name
            : '';

        if ($this->expected_value_type === 'range') {
            if ($this->expected_min !== null || $this->expected_max !== null) {
                $min = $this->formatOptimumNumber($this->expected_min) ?? '…';
                $max = $this->formatOptimumNumber($this->expected_max) ?? '…';

                return "{$min}-{$max}{$unitSuffix}";
            }
        }

        if ($this->expected_value_type === 'constant' && $this->expected_value !== null) {
            return $this->formatOptimumNumber($this->expected_value).$unitSuffix;
        }

        if (filled($this->optimum_level)) {
            return (string) $this->optimum_level;
        }

        return '—';
    }

    protected function formatOptimumNumber(float|int|null $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $value, 4, '.', ''), '0'), '.');
    }

    public function readingFrequencyLabel(): string
    {
        return match ((int) ($this->reading_frequency ?? 1)) {
            1 => 'Once Daily',
            2 => 'Twice Daily',
            3 => 'Three Times Daily',
            4 => 'Four Times Daily',
            5 => 'Five Times Daily',
            default => 'As Set ('.(int) $this->reading_frequency.'×)',
        };
    }

    /**
     * @return list<array{frequency: int, interval: float|null, label: string}>
     */
    public function normalizedReadingFrequencySchedule(): array
    {
        $stored = $this->reading_frequency_schedule;
        if (is_array($stored) && $stored !== []) {
            return collect($stored)
                ->filter(fn ($row) => is_array($row))
                ->map(function (array $row): array {
                    $frequency = (int) ($row['frequency'] ?? $row['slot'] ?? 0);
                    $interval = $row['interval'] ?? null;

                    return [
                        'frequency' => $frequency,
                        'interval' => $frequency > 1 && $interval !== null && $interval !== ''
                            ? (float) $interval
                            : null,
                        'label' => trim((string) ($row['label'] ?? '')),
                    ];
                })
                ->filter(fn (array $row) => $row['frequency'] > 0)
                ->sortBy('frequency')
                ->values()
                ->all();
        }

        $count = max(1, min(5, (int) ($this->reading_frequency ?? 1)));
        $legacyInterval = $this->reading_frequency_interval;
        $schedule = [];

        for ($slot = 1; $slot <= $count; $slot++) {
            $schedule[] = [
                'frequency' => $slot,
                'interval' => $slot > 1 && $legacyInterval !== null ? (float) $legacyInterval : null,
                'label' => '',
            ];
        }

        return $schedule;
    }

    public function formattedReadingFrequencySchedule(): string
    {
        $rows = $this->normalizedReadingFrequencySchedule();

        if ($rows === []) {
            return '—';
        }

        return collect($rows)
            ->map(function (array $row): string {
                $label = filled($row['label']) ? $row['label'] : 'Reading '.$row['frequency'];
                $interval = $row['interval'];

                if ($row['frequency'] === 1 || $interval === null) {
                    return $label;
                }

                return $label.' (every '.$this->formatOptimumNumber($interval).' h)';
            })
            ->implode(' · ');
    }
}
