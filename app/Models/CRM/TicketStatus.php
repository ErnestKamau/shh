<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketStatus extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'ticket_statuses';

    protected $fillable = [
        'name',
        'description',
        'workflow_value',
        'active',
        'color',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'workflow_value' => 'integer',
            'active' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Complaint::class, 'complaint_workflow', 'workflow_value');
    }
}
