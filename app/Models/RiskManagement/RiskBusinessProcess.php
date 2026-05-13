<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskBusinessProcess extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_business_processes';

    protected $fillable = [
        'name',
        'description',
        'company_id',
    ];

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany();

        return $query->where(function ($q) use ($companyId) {
            if ($companyId) {
                $q->where('company_id', $companyId)->orWhereNull('company_id');
                return;
            }

            $q->whereNull('company_id');
        });
    }
}
