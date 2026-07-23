<?php

namespace App\Services\Equipment\Depreciation;

use App\Enums\Equipment\DepreciationMethodCode;
use App\Enums\Equipment\DepreciationStatus;
use App\Models\Equipments\Depreciation\DepreciationAuditLog;
use App\Models\Equipments\Depreciation\DepreciationLedgerEntry;
use App\Models\Equipments\Depreciation\DepreciationSchedule;
use App\Models\Equipments\Depreciation\DepreciationScheduleVersion;
use App\Models\Equipments\Depreciation\EquipmentAppraisal;
use App\Models\Equipments\Depreciation\EquipmentDepreciationConfig;
use App\Models\Equipments\Equipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepreciationService
{
    public function __construct(
        protected DepreciationCalculatorFactory $calculatorFactory
    ) {}

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function upsertConfig(Equipment $equipment, array $attributes): EquipmentDepreciationConfig
    {
        $purchasePrice = (float) ($equipment->purchase_price ?? 0);
        $freight = (float) ($attributes['freight_cost'] ?? 0);
        $capitalized = $purchasePrice + $freight;

        if (! empty($attributes['capitalized_amount_override'])) {
            $capitalized = (float) $attributes['capitalized_amount'];
        }

        $previous = EquipmentDepreciationConfig::query()
            ->where('equipment_id', $equipment->id)
            ->first();

        $frequencies = $this->normalizeFrequencies($attributes['frequencies'] ?? $attributes['frequency'] ?? 'monthly');

        $payload = [
            'depreciation_method_id' => $this->nullableString($attributes['depreciation_method_id'] ?? null),
            'enable_depreciation' => (bool) ($attributes['enable_depreciation'] ?? false),
            'currency' => $attributes['currency'] ?? 'USD',
            'freight_cost' => $freight,
            'capitalized_amount' => $capitalized,
            'depreciation_start_date' => $this->nullableString($attributes['depreciation_start_date'] ?? null),
            'useful_life_years' => $attributes['useful_life_years'] ?? null,
            'salvage_value' => (float) ($attributes['salvage_value'] ?? 0),
            'frequencies' => $frequencies,
            'frequency' => $frequencies[0],
            'depreciation_rate' => $this->nullableNumeric($attributes['depreciation_rate'] ?? null),
            'declining_balance_type' => $this->nullableString($attributes['declining_balance_type'] ?? null),
            'expected_total_units' => $this->nullableNumeric($attributes['expected_total_units'] ?? null),
            'unit_type' => $this->nullableString($attributes['unit_type'] ?? null),
            'current_units_used' => (float) ($attributes['current_units_used'] ?? 0),
            'usage_source' => $this->nullableString($attributes['usage_source'] ?? null),
            'status' => ($attributes['enable_depreciation'] ?? false)
                ? DepreciationStatus::Pending->value
                : DepreciationStatus::Disabled->value,
        ];

        $config = EquipmentDepreciationConfig::query()->updateOrCreate(
            ['equipment_id' => $equipment->id],
            $payload
        );

        $this->logAudit($equipment->id, $config->id, 'config_upsert', $previous?->toArray(), $config->toArray());

        return $config->fresh(['method']);
    }

    public function generateSchedule(
        EquipmentDepreciationConfig $config,
        string $reason = 'initial',
        ?string $userId = null
    ): DepreciationScheduleVersion {
        if (! $config->enable_depreciation) {
            return $this->disableConfig($config);
        }

        return DB::transaction(function () use ($config, $reason, $userId) {
            $config->loadMissing(['method', 'equipment']);

            $this->archiveActiveVersion($config);

            $versionNumber = (int) $config->scheduleVersions()->max('version_number') + 1;
            $version = DepreciationScheduleVersion::query()->create([
                'equipment_depreciation_config_id' => $config->id,
                'version_number' => max(1, $versionNumber),
                'reason' => $reason,
                'is_archived' => false,
                'created_by' => $userId ?? Auth::id(),
            ]);

            $calculator = $this->calculatorFactory->forConfig($config);
            $allRows = [];
            $primaryRows = [];
            $periodIndex = 0;
            $primaryFrequency = $config->resolvedFrequencies()[0] ?? 'monthly';

            foreach ($config->resolvedFrequencies() as $frequency) {
                $workingConfig = clone $config;
                $workingConfig->setAttribute('frequency', $frequency);

                $frequencyRows = $calculator->generateSchedule($workingConfig);

                if ($frequency === $primaryFrequency) {
                    $primaryRows = $frequencyRows;
                }

                foreach ($frequencyRows as $row) {
                    $row['period_index'] = $periodIndex++;
                    $row['frequency'] = $frequency;
                    $allRows[] = $row;

                    DepreciationSchedule::query()->create([
                        'depreciation_schedule_version_id' => $version->id,
                        'equipment_id' => $config->equipment_id,
                        'frequency' => $frequency,
                        'period_label' => $row['period_label'],
                        'period_date' => $row['period_date'],
                        'period_index' => $row['period_index'],
                        'opening_book_value' => $row['opening_book_value'],
                        'depreciation_amount' => $row['depreciation_amount'],
                        'accumulated_depreciation' => $row['accumulated_depreciation'],
                        'closing_book_value' => $row['closing_book_value'],
                        'is_posted' => false,
                    ]);
                }
            }

            $firstRow = $primaryRows[0] ?? null;
            $lastRow = $primaryRows !== [] ? $primaryRows[array_key_last($primaryRows)] : null;

            $config->update([
                'initial_book_value' => $firstRow['opening_book_value'] ?? $config->capitalized_amount,
                'active_schedule_version_id' => $version->id,
                'status' => $this->resolveStatus($config, is_array($lastRow) ? $lastRow : null),
            ]);

            $this->syncBookValueSnapshot($config->fresh());

            $this->logAudit(
                $config->equipment_id,
                $config->id,
                'schedule_generated',
                null,
                [
                    'version' => $version->version_number,
                    'periods' => count($allRows),
                    'frequencies' => $config->resolvedFrequencies(),
                ]
            );

            return $version->load('schedules');
        });
    }

    public function recalculateSchedule(
        EquipmentDepreciationConfig $config,
        string $reason = 'recalculation',
        ?string $userId = null
    ): DepreciationScheduleVersion {
        return $this->generateSchedule($config, $reason, $userId);
    }

    public function syncBookValueSnapshot(EquipmentDepreciationConfig $config, ?Carbon $asOf = null): void
    {
        $asOf = ($asOf ?? now())->copy()->startOfDay();
        $config->loadMissing(['activeScheduleVersion']);

        if (! $config->activeScheduleVersion) {
            return;
        }

        $primaryFrequency = $config->resolvedFrequencies()[0] ?? 'monthly';
        $schedules = $config->activeScheduleVersion->schedules()
            ->where('frequency', $primaryFrequency)
            ->orderBy('period_index')
            ->get();

        $currentSchedule = $this->resolveCurrentPeriodSchedule($config, $schedules, $asOf);

        if ($currentSchedule === null) {
            $config->update([
                'current_book_value' => $config->capitalized_amount,
                'accumulated_depreciation' => 0,
                'current_period_depreciation' => 0,
                'last_processed_period_date' => null,
            ]);

            return;
        }

        $config->update([
            'current_book_value' => $currentSchedule->closing_book_value,
            'accumulated_depreciation' => $currentSchedule->accumulated_depreciation,
            'current_period_depreciation' => $currentSchedule->depreciation_amount,
            'last_processed_period_date' => $currentSchedule->period_date,
        ]);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DepreciationSchedule>  $schedules
     */
    public function resolveCurrentPeriodSchedule(
        EquipmentDepreciationConfig $config,
        $schedules,
        Carbon $asOf
    ): ?DepreciationSchedule {
        if ($schedules->isEmpty()) {
            return null;
        }

        if ($config->depreciation_start_date) {
            $start = Carbon::parse($config->depreciation_start_date)->startOfMonth();
            if ($asOf->lt($start)) {
                return null;
            }
        }

        $current = null;
        foreach ($schedules as $schedule) {
            if ($schedule->period_date->copy()->startOfDay()->lte($asOf)) {
                $current = $schedule;
            } else {
                break;
            }
        }

        return $current;
    }

    public function processAppraisal(EquipmentAppraisal $appraisal, ?string $userId = null): void
    {
        DB::transaction(function () use ($appraisal, $userId) {
            $config = $appraisal->config()->with('method')->firstOrFail();

            $this->archiveActiveVersion($config);

            $extendedLife = (int) $config->useful_life_years + (int) $appraisal->useful_life_extension_years;

            $config->update([
                'capitalized_amount' => (float) $appraisal->new_appraised_value,
                'current_book_value' => (float) $appraisal->new_appraised_value,
                'useful_life_years' => max(1, $extendedLife),
                'accumulated_depreciation' => 0,
                'initial_book_value' => (float) $appraisal->new_appraised_value,
            ]);

            $this->generateSchedule($config->fresh(['method']), 'appraisal:' . $appraisal->id, $userId);

            $this->logAudit(
                $config->equipment_id,
                $config->id,
                'appraisal_processed',
                ['prior_book_value' => $appraisal->prior_book_value],
                ['new_value' => $appraisal->new_appraised_value]
            );
        });
    }

    public function postDueLedgerEntries(EquipmentDepreciationConfig $config, ?string $userId = null): int
    {
        $version = $config->activeScheduleVersion;
        if (! $version) {
            return 0;
        }

        $today = now()->startOfDay();
        $posted = 0;
        $methodCode = $config->method?->code;

        $schedules = $version->schedules()
            ->where('is_posted', false)
            ->whereDate('period_date', '<=', $today)
            ->orderBy('period_index')
            ->get();

        foreach ($schedules as $schedule) {
            DepreciationLedgerEntry::query()->create([
                'equipment_id' => $config->equipment_id,
                'equipment_depreciation_config_id' => $config->id,
                'depreciation_schedule_version_id' => $version->id,
                'depreciation_schedule_id' => $schedule->id,
                'period_label' => $schedule->period_label,
                'period_date' => $schedule->period_date,
                'method_code' => $methodCode instanceof DepreciationMethodCode ? $methodCode->value : (string) $methodCode,
                'frequency' => $schedule->frequency,
                'opening_book_value' => $schedule->opening_book_value,
                'depreciation_amount' => $schedule->depreciation_amount,
                'accumulated_depreciation' => $schedule->accumulated_depreciation,
                'closing_book_value' => $schedule->closing_book_value,
                'version_number' => $version->version_number,
                'generated_by' => $userId ?? Auth::id(),
                'calculated_at' => now(),
            ]);

            $schedule->update(['is_posted' => true]);

            $primaryFrequency = $config->resolvedFrequencies()[0] ?? null;
            if ($schedule->frequency === $primaryFrequency) {
                $config->update([
                    'current_book_value' => $schedule->closing_book_value,
                    'accumulated_depreciation' => $schedule->accumulated_depreciation,
                    'current_period_depreciation' => $schedule->depreciation_amount,
                    'last_processed_period_date' => $schedule->period_date,
                    'status' => abs((float) $schedule->closing_book_value - (float) $config->salvage_value) < 0.01
                        ? DepreciationStatus::FullyDepreciated->value
                        : DepreciationStatus::Active->value,
                ]);
            }

            $posted++;
        }

        return $posted;
    }

    /**
     * @param  array<string, mixed>  $newAttributes
     */
    public function hasImpactingChanges(EquipmentDepreciationConfig $config, array $newAttributes): bool
    {
        $newFrequencies = $this->normalizeFrequencies(
            $newAttributes['frequencies'] ?? $newAttributes['frequency'] ?? $config->resolvedFrequencies()
        );
        $oldFrequencies = $config->resolvedFrequencies();
        sort($newFrequencies);
        sort($oldFrequencies);
        if ($newFrequencies !== $oldFrequencies) {
            return true;
        }

        $keys = [
            'depreciation_method_id',
            'freight_cost',
            'capitalized_amount',
            'depreciation_start_date',
            'useful_life_years',
            'salvage_value',
            'depreciation_rate',
            'declining_balance_type',
            'expected_total_units',
            'current_units_used',
            'currency',
        ];

        foreach ($keys as $key) {
            if (! array_key_exists($key, $newAttributes)) {
                continue;
            }
            $old = $config->getAttribute($key);
            $new = $newAttributes[$key];
            if ((string) $old !== (string) $new) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<int, string>|string|null  $frequencies
     * @return array<int, string>
     */
    public function normalizeFrequencies(array|string|null $frequencies): array
    {
        if (is_string($frequencies)) {
            $frequencies = [$frequencies];
        }

        if (! is_array($frequencies)) {
            $frequencies = ['monthly'];
        }

        $allowed = ['monthly', 'quarterly', 'yearly'];
        $normalized = array_values(array_unique(array_filter(
            $frequencies,
            fn ($f) => is_string($f) && in_array($f, $allowed, true)
        )));

        return $normalized !== [] ? $normalized : ['monthly'];
    }

    protected function archiveActiveVersion(EquipmentDepreciationConfig $config): void
    {
        if ($config->active_schedule_version_id) {
            DepreciationScheduleVersion::query()
                ->where('id', $config->active_schedule_version_id)
                ->update(['is_archived' => true]);
        }
    }

    protected function disableConfig(EquipmentDepreciationConfig $config): DepreciationScheduleVersion
    {
        $config->update(['status' => DepreciationStatus::Disabled->value]);

        return new DepreciationScheduleVersion;
    }

    /**
     * @param  array<string, mixed>|null  $lastRow
     */
    protected function resolveStatus(EquipmentDepreciationConfig $config, ?array $lastRow): string
    {
        if (! $lastRow) {
            return DepreciationStatus::Active->value;
        }

        if (abs((float) $lastRow['closing_book_value'] - (float) $config->salvage_value) < 0.01) {
            return DepreciationStatus::FullyDepreciated->value;
        }

        return DepreciationStatus::Active->value;
    }

    /**
     * @param  array<string, mixed>|null  $previous
     * @param  array<string, mixed>|null  $new
     */
    protected function logAudit(
        string $equipmentId,
        ?string $configId,
        string $action,
        ?array $previous,
        ?array $new
    ): void {
        DepreciationAuditLog::query()->create([
            'equipment_id' => $equipmentId,
            'equipment_depreciation_config_id' => $configId,
            'action_type' => $action,
            'previous_values' => $previous,
            'new_values' => $new,
            'user_id' => Auth::id(),
        ]);
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string === '' ? null : $string;
    }

    protected function nullableNumeric(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }
}
