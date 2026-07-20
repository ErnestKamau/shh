<?php

namespace App\Services\Lab;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class MethodConfigurationResolver
{
    public const CATEGORY_REFERENCE = 'Reference Method';

    public const CATEGORY_LTM = 'Laboratory Test Method';

    public const CATEGORY_SAMPLING = 'Sampling Method';

    /**
     * Required method categories for import / UI classification.
     *
     * @var list<string>
     */
    private const REQUIRED_METHOD_TYPES = [
        self::CATEGORY_SAMPLING,
        self::CATEGORY_REFERENCE,
        self::CATEGORY_LTM,
    ];

    /**
     * @return Collection<int, SystemConfiguration>
     */
    public function methodTypeOptions(): Collection
    {
        $type = $this->methodTypesConfigurationType();

        if (! $type) {
            return collect();
        }

        return SystemConfiguration::query()
            ->where('configuration_type_id', $type->id)
            ->where('key', 'method_type')
            ->orderBy('value')
            ->get();
    }

    public function methodTypesConfigurationType(): ?SystemConfigurationsType
    {
        return SystemConfigurationsType::query()
            ->whereIn('configuration_type', ['Method Types', 'Methods Types'])
            ->first();
    }

    public function generalConfigurationType(): ?SystemConfigurationsType
    {
        $pointerTypeId = SystemConfiguration::query()
            ->whereIn('key', ['method_ltm_id', 'method_reference_id', 'sampling_method_type_id'])
            ->value('configuration_type_id');

        if ($pointerTypeId) {
            $existing = SystemConfigurationsType::query()->find($pointerTypeId);
            if ($existing) {
                return $existing;
            }
        }

        return SystemConfigurationsType::query()
            ->whereIn('configuration_type', ['General Settings', 'General Configuration'])
            ->orderByRaw("CASE configuration_type WHEN 'General Settings' THEN 0 ELSE 1 END")
            ->first();
    }

    public function resolvePointerConfigValue(string $key): ?string
    {
        $config = SystemConfiguration::query()->where('key', $key)->first();

        if (! $config) {
            return null;
        }

        $value = trim((string) ($config->value ?? ''));

        if ($value === '' || ! $this->isValidMethodTypeId($value)) {
            return null;
        }

        return $value;
    }

    public function resolveTypeIdForCategory(string $categoryLabel): ?string
    {
        $this->ensurePointerConfigurations();

        $normalized = $this->normalizeCategoryLabel($categoryLabel);

        $fromPointer = match ($normalized) {
            'reference' => $this->resolvePointerConfigValue('method_reference_id'),
            'ltm' => $this->resolvePointerConfigValue('method_ltm_id'),
            'sampling' => $this->resolvePointerConfigValue('sampling_method_type_id'),
            default => null,
        };

        if ($fromPointer !== null) {
            return $fromPointer;
        }

        $label = match ($normalized) {
            'reference' => self::CATEGORY_REFERENCE,
            'ltm' => self::CATEGORY_LTM,
            'sampling' => self::CATEGORY_SAMPLING,
            default => $categoryLabel,
        };

        return $this->findMethodTypeIdByValue($label);
    }

    /**
     * Ensure Method Types rows and pointer configs exist with valid UUID IDs.
     */
    public function ensurePointerConfigurations(): void
    {
        $this->ensureRequiredMethodTypes();

        $generalType = $this->generalConfigurationType();
        $methodTypes = $this->methodTypeOptions();

        if (! $generalType || $methodTypes->isEmpty()) {
            return;
        }

        $referenceTypeId = $this->findMethodTypeIdByValue(self::CATEGORY_REFERENCE);
        $ltmTypeId = $this->findMethodTypeIdByValue(self::CATEGORY_LTM);
        $samplingTypeId = $this->findMethodTypeIdByValue(self::CATEGORY_SAMPLING);

        if ($referenceTypeId) {
            $this->upsertPointer('method_reference_id', $referenceTypeId, (string) $generalType->id);
        }

        if ($ltmTypeId) {
            $this->upsertPointer('method_ltm_id', $ltmTypeId, (string) $generalType->id);
        }

        if ($samplingTypeId) {
            $this->upsertPointer('sampling_method_type_id', $samplingTypeId, (string) $generalType->id);
        }
    }

    public function normalizeCategoryLabel(?string $label): ?string
    {
        if ($label === null) {
            return null;
        }

        $normalized = strtolower(trim($label));

        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, ['reference', 'reference method'], true)) {
            return 'reference';
        }

        if (in_array($normalized, ['ltm', 'laboratory test method', 'laboratory test', 'lab test method'], true)) {
            return 'ltm';
        }

        if (in_array($normalized, ['sampling', 'sampling method'], true)) {
            return 'sampling';
        }

        return $normalized;
    }

    /**
     * @return array{is_ltm: int, is_sampling_method: int}
     */
    public function legacyFlagsForTypeId(?string $methodTypeId): array
    {
        $methodTypeId = $methodTypeId !== null ? (string) $methodTypeId : '';

        return [
            'is_ltm' => $methodTypeId !== '' && $methodTypeId === (string) $this->resolvePointerConfigValue('method_ltm_id') ? 1 : 0,
            'is_sampling_method' => $methodTypeId !== '' && $methodTypeId === (string) $this->resolvePointerConfigValue('sampling_method_type_id') ? 1 : 0,
        ];
    }

    public function isLaboratoryTestTypeId(?string $methodTypeId): bool
    {
        $ltmTypeId = $this->resolvePointerConfigValue('method_ltm_id');

        return $ltmTypeId !== null && (string) $methodTypeId === (string) $ltmTypeId;
    }

    private function ensureRequiredMethodTypes(): void
    {
        $type = $this->methodTypesConfigurationType();

        if (! $type) {
            $type = SystemConfigurationsType::query()->create([
                'configuration_type' => 'Method Types',
                'description' => 'Analysis method categories',
                'status' => true,
            ]);
        }

        $existingValues = $this->methodTypeOptions()
            ->map(fn (SystemConfiguration $row) => strtolower(trim((string) $row->value)))
            ->filter()
            ->values()
            ->all();

        foreach (self::REQUIRED_METHOD_TYPES as $categoryName) {
            if (in_array(strtolower($categoryName), $existingValues, true)) {
                continue;
            }

            SystemConfiguration::query()->create([
                'configuration_type_id' => $type->id,
                'key' => 'method_type',
                'value' => $categoryName,
                'status' => true,
            ]);
        }
    }

    private function findMethodTypeIdByValue(string $value): ?string
    {
        $match = $this->methodTypeOptions()->first(
            fn (SystemConfiguration $type) => strcasecmp(trim((string) $type->value), $value) === 0
        );

        return $match ? (string) $match->id : null;
    }

    private function isValidMethodTypeId(string $value): bool
    {
        if (! Str::isUuid($value)) {
            return false;
        }

        return SystemConfiguration::query()
            ->where('id', $value)
            ->where('key', 'method_type')
            ->exists();
    }

    private function upsertPointer(string $key, string $value, string $configurationTypeId): void
    {
        $existing = SystemConfiguration::query()->where('key', $key)->first();

        if ($existing) {
            if ((string) $existing->value !== $value) {
                $existing->update(['value' => $value]);
            }

            return;
        }

        SystemConfiguration::query()->create([
            'configuration_type_id' => $configurationTypeId,
            'key' => $key,
            'value' => $value,
            'status' => true,
        ]);
    }
}
