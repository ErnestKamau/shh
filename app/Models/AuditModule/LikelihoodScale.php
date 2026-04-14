<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LikelihoodScale extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'likelihood_scales';

    protected $fillable = [
        'name',
        'code',
        'score',
        'description',
        'color_code',
        'order_index',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'score' => 'integer',
        'order_index' => 'integer',
    ];

    public function nonConformances(): HasMany
    {
        return $this->hasMany(NonConformance::class, 'likelihood_scale_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index', 'asc')->orderBy('score', 'asc');
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('company_id', 0); // Include global/default records
        });
    }
}
