<?php

use App\Models\RiskManagement\Risk;
use App\Models\RiskManagement\RiskConfigurationOption;
use App\Models\RiskManagement\RiskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

if (! function_exists('riskCompanyId')) {
    /**
     * Current company UUID for risk module queries, or null when unset.
     */
    function riskCompanyId(): ?string
    {
        $companyId = getUserCompany();

        if ($companyId === null || $companyId === '' || $companyId === 0 || $companyId === '0') {
            return null;
        }

        return (string) $companyId;
    }
}

if (! function_exists('riskApplyCompanyScope')) {
    /**
     * Limit query to global (null company_id) and/or current company rows.
     */
    function riskApplyCompanyScope(Builder $query, ?string $companyId = null): Builder
    {
        $companyId = $companyId ?? riskCompanyId();

        return $query->where(function (Builder $q) use ($companyId) {
            $q->whereNull('company_id');

            if ($companyId !== null && $companyId !== '') {
                $q->orWhere('company_id', $companyId);
            }
        });
    }
}

if (! function_exists('riskConfigurationForCompany')) {
    /**
     * Scope risk configuration rows to global (null / 0) or current company.
     *
     * @param  Builder  $query
     * @return Builder
     */
    function riskConfigurationForCompany(Builder $query): Builder
    {
        return riskApplyCompanyScope($query);
    }
}

if (! function_exists('normalizeRiskConfigurationMetadata')) {
    /**
     * Normalize configuration metadata (handles legacy double-encoded JSON).
     *
     * @param  mixed  $metadata
     * @return array<string, mixed>
     */
    function normalizeRiskConfigurationMetadata(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }

        if (! is_string($metadata) || $metadata === '') {
            return [];
        }

        $decoded = json_decode($metadata, true);

        // Double-encoded: first decode yields another JSON string
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}

if (! function_exists('defaultRiskConfigurationOptionDefinitions')) {
    /**
     * Built-in dropdown defaults when none exist for the current company.
     *
     * @return array<int, array<string, mixed>>
     */
    function defaultRiskConfigurationOptionDefinitions(?string $optionType = null): array
    {
        $all = [
            ['option_type' => 'implementation_status', 'code' => 'planned', 'name' => 'Planned', 'color_code' => '#6c757d', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'in_progress', 'name' => 'In Progress', 'color_code' => '#17a2b8', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'completed', 'name' => 'Completed', 'color_code' => '#28a745', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'on_hold', 'name' => 'On Hold', 'color_code' => '#ffc107', 'order_index' => 4, 'metadata' => []],
            ['option_type' => 'implementation_status', 'code' => 'cancelled', 'name' => 'Cancelled', 'color_code' => '#dc3545', 'order_index' => 5, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'high', 'name' => 'High', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'treatment_priority', 'code' => 'critical', 'name' => 'Critical', 'color_code' => '#dc3545', 'order_index' => 4, 'metadata' => []],
            ['option_type' => 'review_type', 'code' => 'scheduled', 'name' => 'Scheduled', 'color_code' => '#17a2b8', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'review_type', 'code' => 'triggered', 'name' => 'Triggered', 'color_code' => '#fd7e14', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'review_type', 'code' => 'periodic', 'name' => 'Periodic', 'color_code' => '#6f42c1', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'review_decision', 'code' => 'continue_monitoring', 'name' => 'Continue Monitoring', 'color_code' => '#17a2b8', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'review_decision', 'code' => 'close_risk', 'name' => 'Close Risk', 'color_code' => '#28a745', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'review_decision', 'code' => 'additional_controls', 'name' => 'Additional Controls Needed', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'closure_type', 'code' => 'eliminated', 'name' => 'Eliminated', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'closure_type', 'code' => 'controlled', 'name' => 'Controlled', 'color_code' => '#17a2b8', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'closure_type', 'code' => 'accepted', 'name' => 'Accepted', 'color_code' => '#ffc107', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'low', 'name' => 'Low', 'color_code' => '#28a745', 'order_index' => 1, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'medium', 'name' => 'Medium', 'color_code' => '#ffc107', 'order_index' => 2, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'high', 'name' => 'High', 'color_code' => '#fd7e14', 'order_index' => 3, 'metadata' => []],
            ['option_type' => 'risk_level', 'code' => 'critical', 'name' => 'Critical', 'color_code' => '#dc3545', 'order_index' => 4, 'metadata' => []],
            ...collect(range(1, 5))->flatMap(function (int $score): array {
                return [
                    [
                        'option_type' => 'likelihood_score',
                        'code' => (string) $score,
                        'name' => (string) $score,
                        'color_code' => '#17a2b8',
                        'order_index' => $score,
                        'metadata' => [],
                    ],
                    [
                        'option_type' => 'severity_score',
                        'code' => (string) $score,
                        'name' => (string) $score,
                        'color_code' => '#fd7e14',
                        'order_index' => $score,
                        'metadata' => [],
                    ],
                ];
            })->all(),
        ];

        if ($optionType === null) {
            return $all;
        }

        return array_values(array_filter(
            $all,
            fn (array $row): bool => $row['option_type'] === $optionType
        ));
    }
}

if (! function_exists('ensureRiskConfigurationOptionsSeeded')) {
    /**
     * Seed built-in defaults for the current company when none exist yet.
     */
    function ensureRiskConfigurationOptionsSeeded(string $optionType): void
    {
        $exists = riskConfigurationForCompany(
            RiskConfigurationOption::query()->forType($optionType)
        )->exists();

        if ($exists) {
            return;
        }

        $definitions = defaultRiskConfigurationOptionDefinitions($optionType);

        if ($definitions === []) {
            return;
        }

        $companyId = riskCompanyId();

        foreach ($definitions as $row) {
            $metadata = $row['metadata'] ?? [];
            unset($row['metadata']);

            RiskConfigurationOption::updateOrCreate(
                [
                    'option_type' => $optionType,
                    'code' => $row['code'],
                    'company_id' => $companyId,
                ],
                array_merge($row, [
                    'option_type' => $optionType,
                    'description' => $row['description'] ?? null,
                    'is_active' => true,
                    'company_id' => $companyId,
                    'metadata' => ! empty($metadata) ? $metadata : null,
                ])
            );
        }
    }
}

if (! function_exists('getRiskConfigurationOptions')) {
    /**
     * @return Collection<int, RiskConfigurationOption>
     */
    function getRiskConfigurationOptions(string $optionType): Collection
    {
        ensureRiskConfigurationOptionsSeeded($optionType);

        return riskConfigurationForCompany(
            RiskConfigurationOption::query()
                ->forType($optionType)
                ->active()
                ->ordered()
        )->get();
    }
}

if (! function_exists('getActiveRiskStatuses')) {
    function getActiveRiskStatuses(): Collection
    {
        return RiskStatus::active()->forCompany()->ordered()->get();
    }
}

if (! function_exists('canonicalRiskWorkflowStepName')) {
    /**
     * Canonical label for a risk record workflow step (2–8).
     */
    function canonicalRiskWorkflowStepName(int $recordStep): ?string
    {
        $statusStep = mapRiskRecordWorkflowStepToStatusStep($recordStep);

        if ($statusStep === null) {
            return null;
        }

        return getRiskWorkflowStepDefinitions()[$statusStep] ?? null;
    }
}

if (! function_exists('getRiskStatusForRecordStep')) {
    /**
     * Resolve risk_statuses row for a risk-record workflow step (2–8).
     */
    function getRiskStatusForRecordStep(int $recordStep): ?RiskStatus
    {
        $statusStep = mapRiskRecordWorkflowStepToStatusStep($recordStep);

        if ($statusStep === null) {
            return null;
        }

        return RiskStatus::active()
            ->forCompany()
            ->where('workflow_step', $statusStep)
            ->ordered()
            ->first();
    }
}

if (! function_exists('getClosedRiskStatus')) {
    function getClosedRiskStatus(): ?RiskStatus
    {
        return getRiskStatusForRecordStep(8);
    }
}

if (! function_exists('applyRiskRecordWorkflowStep')) {
    /**
     * Persist risk-record workflow step (2–8) and sync status_id / status_name.
     *
     * @param  array<string, mixed>  $extra
     */
    function applyRiskRecordWorkflowStep(Risk $risk, int $recordStep, array $extra = []): void
    {
        $status = getRiskStatusForRecordStep($recordStep);
        $data = array_merge(['workflow_step' => $recordStep], $extra);

        if ($status) {
            $data['status_id'] = $status->id;
        }

        $data['status_name'] = canonicalRiskWorkflowStepName($recordStep)
            ?? $status?->name
            ?? $risk->status_name;

        $risk->update($data);
    }
}

if (! function_exists('getEvaluationResults')) {
    function getEvaluationResults(): Collection
    {
        return getRiskConfigurationOptions('evaluation_result');
    }
}

if (! function_exists('getEvaluationResultByRPN')) {
    function getEvaluationResultByRPN(int $rpn): ?RiskConfigurationOption
    {
        foreach (getEvaluationResults() as $option) {
            $metadata = normalizeRiskConfigurationMetadata($option->metadata);
            $min = isset($metadata['rpn_min']) ? (int) $metadata['rpn_min'] : null;
            $max = isset($metadata['rpn_max']) ? (int) $metadata['rpn_max'] : null;

            if ($min !== null && $max !== null && $rpn >= $min && $rpn <= $max) {
                return $option;
            }
        }

        return null;
    }
}

if (! function_exists('getEvaluationResultRpnRange')) {
    /**
     * @return array{min: int|null, max: int|null}|null
     */
    function getEvaluationResultRpnRange(string $code): ?array
    {
        $option = getEvaluationResults()->firstWhere('code', strtolower($code));

        if (! $option) {
            return null;
        }

        $metadata = normalizeRiskConfigurationMetadata($option->metadata);

        return [
            'min' => isset($metadata['rpn_min']) ? (int) $metadata['rpn_min'] : null,
            'max' => isset($metadata['rpn_max']) ? (int) $metadata['rpn_max'] : null,
        ];
    }
}

if (! function_exists('getImplementationStatuses')) {
    function getImplementationStatuses(): Collection
    {
        return getRiskConfigurationOptions('implementation_status');
    }
}

if (! function_exists('getTreatmentPriorities')) {
    function getTreatmentPriorities(): Collection
    {
        return getRiskConfigurationOptions('treatment_priority');
    }
}

if (! function_exists('getReviewTypes')) {
    function getReviewTypes(): Collection
    {
        return getRiskConfigurationOptions('review_type');
    }
}

if (! function_exists('getReviewDecisions')) {
    function getReviewDecisions(): Collection
    {
        return getRiskConfigurationOptions('review_decision');
    }
}

if (! function_exists('getClosureTypes')) {
    function getClosureTypes(): Collection
    {
        return getRiskConfigurationOptions('closure_type');
    }
}

if (! function_exists('getRiskLevels')) {
    function getRiskLevels(): Collection
    {
        return getRiskConfigurationOptions('risk_level');
    }
}

if (! function_exists('getRiskScores')) {
    function getRiskScores(): Collection
    {
        $likelihood = getRiskConfigurationOptions('likelihood_score');
        $severity = getRiskConfigurationOptions('severity_score');

        if ($likelihood->isNotEmpty() || $severity->isNotEmpty()) {
            return $likelihood->merge($severity)->sortBy('order_index')->values();
        }

        return collect(range(1, 5))->map(function (int $score) {
            return (object) [
                'code' => (string) $score,
                'name' => (string) $score,
                'order_index' => $score,
                'metadata' => null,
            ];
        });
    }
}
