<?php

namespace App\Models\AuditModule\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesAuditConfigurationForCompany
{
    /**
     * Reference/configuration rows: current company or global (null company_id).
     */
    public function scopeForCompany(Builder $query): Builder
    {
        $companyId = getUserCompany();

        return $query->where(function (Builder $q) use ($companyId) {
            $q->whereNull('company_id');
            if ($companyId !== null && $companyId !== '') {
                $q->orWhere('company_id', $companyId);
            }
        });
    }
}
