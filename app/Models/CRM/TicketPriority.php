<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketPriority extends Model
{
    protected $table = 'ticket_priorities';

    protected $fillable = [
        'name',
        'value',
        'description',
        'color',
        'order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Complaint::class, 'priority', 'value');
    }
}
