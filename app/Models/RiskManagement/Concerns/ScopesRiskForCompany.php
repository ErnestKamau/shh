<?php

namespace App\Models\RiskManagement\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesRiskForCompany
{
    /**
     * Reference/configuration rows: current company or global (null company_id).
     */
    public function scopeForCompany(Builder $query, ?string $companyId = null): Builder
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
