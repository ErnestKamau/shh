<?php

namespace App\Models\CRM;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAssignment extends Model
{
    protected $table = 'ticket_assignments';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'assigned_date',
        'assigned_by',
        'notes',
        'tat_start_at',
        'tat_due_at',
        'tat_hours',
        'tat_breached',
        'tat_breached_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_date' => 'date',
            'tat_start_at' => 'datetime',
            'tat_due_at' => 'datetime',
            'tat_hours' => 'decimal:2',
            'tat_breached' => 'boolean',
            'tat_breached_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Complaint::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
