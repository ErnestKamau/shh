<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VerificationResult extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'verification_results';

    protected $fillable = [
        'name',
        'code',
        'description',
        'color_code',
        'requires_reopen',
        'next_workflow_step',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_reopen' => 'boolean',
    ];

    public function verificationRecords(): HasMany
    {
        return $this->hasMany(VerificationRecord::class, 'effectiveness_result_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}











