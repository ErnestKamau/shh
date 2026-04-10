<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TicketPriority extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'value',
        'description',
        'color',
        'order',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Get tickets with this priority
     */
    public function tickets()
    {
        return $this->hasMany(Complaint::class, 'priority', 'value');
    }

    /**
     * Scope to get only active priorities
     */
    public function scopeActive($query)
    {
        return $query->where('active', true)->orderBy('order');
    }
}
