<?php

namespace App\Services\Qc;

use App\CapturedResult;
use App\SampleHeader;

class QcBindingConditionEvaluator
{
    /**
     * Supported condition keys (all optional; empty conditions always match):
     * - equipment_id: must match captured_results.equipment_id
     * - crm_customer_id: must match sample_headers.crm_customer_id
     * - sample_type_id: must match sample_headers.sample_type_id
     *
     * @param  array<string, mixed>|null  $conditions
     */
    public function matches(?array $conditions, CapturedResult $capturedResult, ?SampleHeader $batch = null): bool
    {
        if ($conditions === null || $conditions === []) {
            return true;
        }

        $batch ??= $capturedResult->relationLoaded('sampleHeader')
            ? $capturedResult->sampleHeader
            : SampleHeader::query()->find($capturedResult->sample_header_id);

        foreach ($conditions as $key => $expected) {
            if ($expected === null || $expected === '') {
                continue;
            }

            $expected = (string) $expected;

            $actual = match ((string) $key) {
                'equipment_id' => $capturedResult->equipment_id !== null
                    ? (string) $capturedResult->equipment_id
                    : null,
                'crm_customer_id' => $batch?->crm_customer_id !== null && $batch->crm_customer_id !== ''
                    ? (string) $batch->crm_customer_id
                    : null,
                'sample_type_id' => $batch?->sample_type_id !== null && $batch->sample_type_id !== ''
                    ? (string) $batch->sample_type_id
                    : null,
                default => null,
            };

            // Unknown keys are ignored (forward-compatible).
            if (! in_array((string) $key, ['equipment_id', 'crm_customer_id', 'sample_type_id'], true)) {
                continue;
            }

            if ($actual === null || $actual !== $expected) {
                return false;
            }
        }

        return true;
    }
}
