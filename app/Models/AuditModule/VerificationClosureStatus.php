<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationClosureStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'verification_closure_statuses';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color_code',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function verificationRecords(): HasMany
    {
        return $this->hasMany(VerificationRecord::class, 'closure_status_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}














