<?php

namespace App\Services\Commercial;

use App\Models\Billing\Pricelist;
use App\Models\Billing\PricelistItem;
use App\Services\Sampleworkflow\AcceptanceFormPricingService;
use App\Services\Sampleworkflow\AcceptanceFormSampleConfigService;

final class CommercialEnquiryConfigSyncService
{
    public function __construct(
        private AcceptanceFormPricingService $pricingService,
        private AcceptanceFormSampleConfigService $configService,
    ) {}

    /**
     * Merge TRF/enquiry line seeds into sample configs.
     *
     * Pricelist is a price book for tests — it must not invent physical sample rows.
     * Pass $includeAllContractParameters = true only for explicit legacy tooling;
     * Process Enquiry Step 2 should keep this false and resolve prices on Step 3.
     *
     * @param  list<array<string, mixed>>  $configs
     * @param  list<array<string, mixed>>  $trfLineSeeds
     * @return list<array<string, mixed>>
     */
    public function mergePricelistIntoSampleConfigs(
        string $customerId,
        array $configs,
        array $trfLineSeeds,
        bool $includeAllContractParameters = false,
        ?Pricelist $assignedPricelist = null,
    ): array {
        $seeds = $trfLineSeeds;

        if ($includeAllContractParameters) {
            // Legacy: expand every customer-pricelist parameter into config seeds.
            // Prefer request/TRF seeds only; pricing belongs on quotation lines.
            $params = $assignedPricelist !== null
                ? $this->parametersForPricelist($assignedPricelist)
                : $this->pricingService->pricelistParametersForCustomer($customerId);

            foreach ($params as $param) {
                $seeds[] = [
                    'sample_type_id'      => $param['sample_type_id'] ?? null,
                    'analysis_type_id'    => $param['analysis_type_id'] ?? null,
                    'analysis_element_id' => $param['analysis_element_id'] ?? null,
                    'parameter_label'     => $param['label'] ?? 'Parameter',
                    'number_of_samples'   => 1,
                ];
            }
        }

        $seeds = $this->deduplicateLineSeeds($seeds);

        if ($configs === []) {
            return $this->configService->buildConfigsFromPrefill($seeds);
        }

        $mergedPrefill = $this->configsToPrefillLines($configs);
        foreach ($seeds as $seed) {
            $mergedPrefill = $this->appendSeedIfNew($mergedPrefill, $seed);
        }

        return $this->configService->buildConfigsFromPrefill($mergedPrefill);
    }

    /**
     * Load pricelist parameters directly from a specific Pricelist model.
     * Returns the same shape as AcceptanceFormPricingService::pricelistParametersForCustomer().
     *
     * @return list<array<string, mixed>>
     */
    private function parametersForPricelist(Pricelist $pricelist): array
    {
        return PricelistItem::query()
            ->where('pricelist_id', $pricelist->id)
            ->where('active', 1)
            ->with(['sampleType:id,name', 'analysisType:id,name', 'analysisElement.analyte:id,name'])
            ->get()
            ->map(function (PricelistItem $item): array {
                $label = $item->analysisElement?->analyte?->name
                    ?? $item->analysisType?->name
                    ?? 'Parameter';

                return [
                    'sample_type_id'      => $item->sample_type_id ? (string) $item->sample_type_id : null,
                    'analysis_type_id'    => $item->analysis_id ? (string) $item->analysis_id : null,
                    'analysis_element_id' => $item->analysis_element_id ? (string) $item->analysis_element_id : null,
                    'label'               => $label,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $seeds
     * @return list<array<string, mixed>>
     */
    private function deduplicateLineSeeds(array $seeds): array
    {
        $seen = [];
        $unique = [];

        foreach ($seeds as $seed) {
            $key = $this->lineSeedKey($seed);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $unique[] = $seed;
        }

        return $unique;
    }

    /**
     * @param  list<array<string, mixed>>  $configs
     * @return list<array<string, mixed>>
     */
    private function configsToPrefillLines(array $configs): array
    {
        $lines = [];
        $lineNo = 1;

        foreach ($configs as $config) {
            $parameterKeys = is_array($config['parameter_keys'] ?? null) ? $config['parameter_keys'] : [];
            $rowIndex = $config['row_index'] ?? null;
            $details = $this->configService->sampleDetailsFromConfig($config);

            if ($parameterKeys === []) {
                $lines[] = [
                    'line_no' => $lineNo++,
                    'row_index' => $rowIndex,
                    'sample_type_id' => $config['sample_type_id'] ?? null,
                    'analysis_type_id' => $config['analysis_type_id'] ?? null,
                    'analysis_element_id' => null,
                    'parameter_label' => 'Parameter',
                    'number_of_samples' => 1,
                    'sample_condition_id' => $config['sample_condition_id'] ?? null,
                    'customer_sample_id' => $details['customer_sample_id'] !== '' ? $details['customer_sample_id'] : null,
                ];

                continue;
            }

            foreach ($parameterKeys as $paramKey) {
                $lines[] = [
                    'line_no' => $lineNo++,
                    'row_index' => $rowIndex,
                    'sample_type_id' => $config['sample_type_id'] ?? null,
                    'analysis_type_id' => $config['analysis_type_id'] ?? null,
                    'analysis_element_id' => (string) $paramKey,
                    'parameter_label' => 'Parameter',
                    'number_of_samples' => 1,
                    'sample_condition_id' => $config['sample_condition_id'] ?? null,
                    'customer_sample_id' => $details['customer_sample_id'] !== '' ? $details['customer_sample_id'] : null,
                ];
            }
        }

        return $lines;
    }

    /**
     * @param  list<array<string, mixed>>  $prefillLines
     * @param  array<string, mixed>  $seed
     * @return list<array<string, mixed>>
     */
    private function appendSeedIfNew(array $prefillLines, array $seed): array
    {
        $key = $this->lineSeedKey($seed);

        foreach ($prefillLines as $existing) {
            if ($this->lineSeedKey($existing) === $key) {
                return $prefillLines;
            }
        }

        $prefillLines[] = array_merge($seed, [
            'line_no' => count($prefillLines) + 1,
        ]);

        return $prefillLines;
    }

    /**
     * @param  array<string, mixed>  $seed
     */
    private function lineSeedKey(array $seed): string
    {
        return implode('::', [
            (string) ($seed['sample_type_id'] ?? ''),
            (string) ($seed['analysis_type_id'] ?? ''),
            (string) ($seed['analysis_element_id'] ?? ''),
        ]);
    }
}
