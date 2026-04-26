<?php

namespace App\Models\RiskManagement;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskSource extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'risk_sources';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function risks(): HasMany
    {
        return $this->hasMany(Risk::class, 'source_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        });
    }
}


