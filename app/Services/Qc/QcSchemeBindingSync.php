<?php

namespace App\Services\Qc;

use App\AnalysisMethod;
use App\Models\QcModule\QcSchemeBinding;
use App\Standards;
use Illuminate\Support\Facades\DB;

class QcSchemeBindingSync
{
    /**
     * Sync standard-scoped scheme bindings and mirror the standard_qc_scheme pivot + CSV.
     *
     * @param  array<int, string|int|null>  $schemeIds
     * @param  array{mode?: string, priority?: int, conditions?: array<string, mixed>|null}  $options
     */
    public function syncForStandard(Standards $standard, array $schemeIds, array $options = []): void
    {
        $ids = $this->normalizeIds($schemeIds);
        $mode = $options['mode'] ?? QcSchemeBinding::MODE_OVERRIDE;
        $priority = (int) ($options['priority'] ?? 100);
        $conditions = $this->normalizeConditions($options['conditions'] ?? null);

        DB::transaction(function () use ($standard, $ids, $mode, $priority, $conditions): void {
            QcSchemeBinding::query()
                ->where('standard_id', $standard->id)
                ->whereNull('method_id')
                ->delete();

            foreach ($ids as $schemeId) {
                QcSchemeBinding::query()->create([
                    'standard_id' => $standard->id,
                    'method_id' => null,
                    'qc_scheme_id' => $schemeId,
                    'mode' => $mode,
                    'priority' => $priority,
                    'conditions' => $conditions,
                ]);
            }

            $standard->qcSchemes()->sync($ids);
            $standard->forceFill([
                'qc_scheme_ids' => $ids === [] ? null : implode(',', $ids),
            ])->saveQuietly();
        });
    }

    /**
     * Sync method-scoped scheme bindings and mirror the method_qc_scheme pivot.
     *
     * @param  array<int, string|int|null>  $schemeIds
     * @param  array{mode?: string, priority?: int, conditions?: array<string, mixed>|null}  $options
     */
    public function syncForMethod(AnalysisMethod $method, array $schemeIds, array $options = []): void
    {
        $ids = $this->normalizeIds($schemeIds);
        $mode = $options['mode'] ?? QcSchemeBinding::MODE_OVERRIDE;
        $priority = (int) ($options['priority'] ?? 100);
        $conditions = $this->normalizeConditions($options['conditions'] ?? null);

        DB::transaction(function () use ($method, $ids, $mode, $priority, $conditions): void {
            QcSchemeBinding::query()
                ->where('method_id', $method->id)
                ->whereNull('standard_id')
                ->delete();

            foreach ($ids as $schemeId) {
                QcSchemeBinding::query()->create([
                    'standard_id' => null,
                    'method_id' => $method->id,
                    'qc_scheme_id' => $schemeId,
                    'mode' => $mode,
                    'priority' => $priority,
                    'conditions' => $conditions,
                ]);
            }

            $method->qcSchemes()->sync($ids);
        });
    }

    /**
     * @param  array<string, mixed>|null  $conditions
     * @return array<string, string>|null
     */
    private function normalizeConditions(?array $conditions): ?array
    {
        if ($conditions === null || $conditions === []) {
            return null;
        }

        $allowed = ['equipment_id', 'crm_customer_id', 'sample_type_id'];
        $normalized = [];

        foreach ($allowed as $key) {
            $value = $conditions[$key] ?? null;
            if ($value !== null && $value !== '') {
                $normalized[$key] = (string) $value;
            }
        }

        return $normalized === [] ? null : $normalized;
    }

    /**
     * @param  array<int, string|int|null>  $schemeIds
     * @return array<int, string>
     */
    private function normalizeIds(array $schemeIds): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => $id !== null && $id !== '' ? (string) $id : null,
            $schemeIds
        ))));
    }
}
