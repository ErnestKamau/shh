<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TicketStatus extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'workflow_value',
        'color',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'workflow_value' => 'integer',
    ];

    /**
     * Get tickets with this status
     */
    public function tickets()
    {
        return $this->hasMany(Complaint::class, 'complaint_workflow', 'workflow_value');
    }

    /**
     * Scope to get only active statuses
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
