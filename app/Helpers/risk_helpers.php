<?php

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

if (! function_exists('getRiskConfigurationOptions')) {
    /**
     * @return Collection<int, RiskConfigurationOption>
     */
    function getRiskConfigurationOptions(string $optionType): Collection
    {
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
            $metadata = is_array($option->metadata) ? $option->metadata : [];
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

        $metadata = is_array($option->metadata) ? $option->metadata : [];

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
            ];
        });
    }
}
