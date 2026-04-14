<?php

namespace App\Models\RiskManagement;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskProcessLink extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'risk_process_links';

    protected $fillable = [
        'risk_id',
        'business_process_id',
        'description',
        'created_by',
    ];

    public function risk(): BelongsTo
    {
        return $this->belongsTo(Risk::class, 'risk_id');
    }

    public function businessProcess(): BelongsTo
    {
        return $this->belongsTo(RiskBusinessProcess::class, 'business_process_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
