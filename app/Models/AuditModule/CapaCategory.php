<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CapaCategory extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'capa_categories';

    protected $fillable = [
        'name',
        'code',
        'description',
        'action_type',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class, 'capa_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
