<?php

namespace App\Services\Sampleworkflow;

use App\AnalysisType;
use App\Models\CustomerAnalysisTypeStandard;
use Illuminate\Support\Facades\Schema;

class CustomerAnalysisTypeStandardService
{
    /**
     * Resolve the specification/standard to prefill for a customer + analysis type.
     * Preference (when it differs from the type default) wins; otherwise type default.
     */
    public function resolvePrefillStandardId(?string $crmCustomerId, ?string $analysisTypeId): ?string
    {
        $analysisTypeId = $this->normalizeId($analysisTypeId);
        if ($analysisTypeId === null) {
            return null;
        }

        $typeDefaultId = $this->analysisTypeDefaultStandardId($analysisTypeId);

        $crmCustomerId = $this->normalizeId($crmCustomerId);
        if ($crmCustomerId !== null && $this->preferenceTableReady()) {
            $preferenceId = CustomerAnalysisTypeStandard::query()
                ->where('crm_customer_id', $crmCustomerId)
                ->where('analysis_type_id', $analysisTypeId)
                ->value('standard_id');

            $preferenceId = $this->normalizeId($preferenceId !== null ? (string) $preferenceId : null);
            if ($preferenceId !== null) {
                return $preferenceId;
            }
        }

        return $typeDefaultId;
    }

    /**
     * Apply prefill standards onto acceptance sample configs when main_standard_id is empty.
     *
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    public function applyPrefillToConfigs(array $configs, ?string $crmCustomerId): array
    {
        foreach ($configs as $index => $config) {
            if (! empty($config['main_standard_id'])) {
                continue;
            }

            $resolved = $this->resolvePrefillStandardId(
                $crmCustomerId,
                isset($config['analysis_type_id']) ? (string) $config['analysis_type_id'] : null,
            );

            if ($resolved !== null) {
                $configs[$index]['main_standard_id'] = $resolved;
            }
        }

        return $configs;
    }

    /**
     * Persist customer preferences from accepted sample configs.
     * Saves last-used standard only when it differs from the analysis type default.
     * For multiple rows of the same analysis type, the last non-default wins.
     *
     * @param  list<array<string, mixed>>  $configs
     */
    public function syncPreferencesFromConfigs(?string $crmCustomerId, array $configs): void
    {
        $crmCustomerId = $this->normalizeId($crmCustomerId);
        if ($crmCustomerId === null || ! $this->preferenceTableReady()) {
            return;
        }

        /** @var array<string, string> $lastChosenByAnalysisType */
        $lastChosenByAnalysisType = [];

        foreach ($configs as $config) {
            $analysisTypeId = $this->normalizeId(
                isset($config['analysis_type_id']) ? (string) $config['analysis_type_id'] : null
            );
            $standardId = $this->normalizeId(
                isset($config['main_standard_id']) ? (string) $config['main_standard_id'] : null
            );

            if ($analysisTypeId === null || $standardId === null) {
                continue;
            }

            $lastChosenByAnalysisType[$analysisTypeId] = $standardId;
        }

        foreach ($lastChosenByAnalysisType as $analysisTypeId => $standardId) {
            $typeDefaultId = $this->analysisTypeDefaultStandardId($analysisTypeId);

            // Last-used wins. Persist only when it differs from the analysis-type default.
            if ($standardId === $typeDefaultId) {
                CustomerAnalysisTypeStandard::query()
                    ->where('crm_customer_id', $crmCustomerId)
                    ->where('analysis_type_id', $analysisTypeId)
                    ->delete();

                continue;
            }

            CustomerAnalysisTypeStandard::query()->updateOrCreate(
                [
                    'crm_customer_id' => $crmCustomerId,
                    'analysis_type_id' => $analysisTypeId,
                ],
                [
                    'standard_id' => $standardId,
                ]
            );
        }
    }

    private function analysisTypeDefaultStandardId(string $analysisTypeId): ?string
    {
        if (! Schema::hasColumn('analysis_types', 'default_standard_id')) {
            return null;
        }

        $default = AnalysisType::query()
            ->whereKey($analysisTypeId)
            ->value('default_standard_id');

        return $this->normalizeId($default !== null ? (string) $default : null);
    }

    private function preferenceTableReady(): bool
    {
        return Schema::hasTable('customer_analysis_type_standards')
            && Schema::hasColumn('customer_analysis_type_standards', 'standard_id')
            && Schema::hasColumn('customer_analysis_type_standards', 'crm_customer_id')
            && Schema::hasColumn('customer_analysis_type_standards', 'analysis_type_id');
    }

    private function normalizeId(?string $id): ?string
    {
        $id = trim((string) $id);

        return $id !== '' ? $id : null;
    }
}
