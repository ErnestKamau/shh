<?php

namespace App\Models\AuditModule\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait ScopesAuditTenantForCompany
{
    /**
     * Tenant/transactional rows: scoped strictly to the current company.
     */
    public function scopeForCompany(Builder $query): Builder
    {
        $companyId = getUserCompany();
        if ($companyId === null || $companyId === '') {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('company_id', $companyId);
    }
}
