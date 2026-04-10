<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TicketCategory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // Relationships
    public function tickets()
    {
        return $this->hasMany(Complaint::class, 'ticket_category_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

