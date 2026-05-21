<?php

namespace App\Models\Registry\Concerns;

trait ScopesByCompany
{
    public function scopeForCompany($query)
    {
        $companyId = getUserCompany();

        return $query->where(function ($q) use ($companyId) {
            if ($companyId) {
                $q->where('company_id', $companyId)
                    ->orWhereNull('company_id');

                return;
            }

            $q->whereNull('company_id');
        });
    }
}
