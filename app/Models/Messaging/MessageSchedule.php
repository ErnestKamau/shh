<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class MessageSchedule extends Model
{
    protected $table = 'message_schedules';

    protected $fillable = [
        'tenant_id',
        'template_id',
        'name',
        'frequency',
        'cron_expression',
        'start_date',
        'end_date',
        'next_run_at',
        'active',
    ];

    protected $casts = [
        'template_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'next_run_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }
}
