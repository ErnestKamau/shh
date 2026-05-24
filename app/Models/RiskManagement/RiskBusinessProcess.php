<?php

namespace App\Models\RiskManagement;

use App\Models\RiskManagement\Concerns\ScopesRiskForCompany;
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

    use ScopesRiskForCompany;
    use SoftDeletes;

    protected $table = 'risk_business_processes';

    protected $fillable = [
        'name',
        'description',
        'company_id',
    ];

}
