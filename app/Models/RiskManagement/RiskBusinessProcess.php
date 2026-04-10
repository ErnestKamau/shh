<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskBusinessProcess extends Model
{
    use SoftDeletes;

    protected $table = 'risk_business_processes';

    protected $fillable = [
        'name',
        'description',
        'company_id',
    ];

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where('company_id', $companyId)->orWhere('company_id', 0);
    }
}
