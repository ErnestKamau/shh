<?php

namespace App\Services\Lab;

use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Illuminate\Support\Collection;

final class MethodConfigurationResolver
{
    public const CATEGORY_REFERENCE = 'Reference Method';

    public const CATEGORY_LTM = 'Laboratory Test Method';

    public const CATEGORY_SAMPLING = 'Sampling Method';

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
        return SystemConfigurationsType::query()
            ->whereIn('configuration_type', ['General Configuration', 'General Settings'])
            ->first();
    }

    public function resolvePointerConfigValue(string $key): ?string
    {
        $config = SystemConfiguration::query()->where('key', $key)->first();

        if (! $config) {
            return null;
        }

        $value = trim((string) ($config->value ?? ''));

        return $value !== '' ? $value : null;
    }

    public function resolveTypeIdForCategory(string $categoryLabel): ?string
    {
        $normalized = $this->normalizeCategoryLabel($categoryLabel);

        return match ($normalized) {
            'reference' => $this->resolvePointerConfigValue('method_reference_id')
                ?? $this->methodTypeOptions()->firstWhere('value', self::CATEGORY_REFERENCE)?->id,
            'ltm' => $this->resolvePointerConfigValue('method_ltm_id')
                ?? $this->methodTypeOptions()->firstWhere('value', self::CATEGORY_LTM)?->id,
            'sampling' => $this->resolvePointerConfigValue('sampling_method_type_id')
                ?? $this->methodTypeOptions()->firstWhere('value', self::CATEGORY_SAMPLING)?->id,
            default => $this->methodTypeOptions()->first(
                fn (SystemConfiguration $type) => strcasecmp((string) $type->value, $categoryLabel) === 0
            )?->id,
        };
    }

    /**
     * Ensure method_reference_id exists when method types and sibling pointers are present.
     */
    public function ensurePointerConfigurations(): void
    {
        $generalType = $this->generalConfigurationType();
        $methodTypes = $this->methodTypeOptions();

        if (! $generalType || $methodTypes->isEmpty()) {
            return;
        }

        $referenceTypeId = $methodTypes->first(
            fn (SystemConfiguration $type) => strcasecmp((string) $type->value, self::CATEGORY_REFERENCE) === 0
        )?->id;

        $ltmTypeId = $methodTypes->first(
            fn (SystemConfiguration $type) => strcasecmp((string) $type->value, self::CATEGORY_LTM) === 0
        )?->id;

        $samplingTypeId = $methodTypes->first(
            fn (SystemConfiguration $type) => strcasecmp((string) $type->value, self::CATEGORY_SAMPLING) === 0
        )?->id;

        if ($referenceTypeId) {
            $this->upsertPointer('method_reference_id', (string) $referenceTypeId, $generalType->id);
        }

        if ($ltmTypeId) {
            $this->upsertPointer('method_ltm_id', (string) $ltmTypeId, $generalType->id);
        }

        if ($samplingTypeId) {
            $this->upsertPointer('sampling_method_type_id', (string) $samplingTypeId, $generalType->id);
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
