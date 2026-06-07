<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class MessageCampaign extends Model
{
    protected $table = 'message_campaigns';

    protected $fillable = [
        'tenant_id',
        'name',
        'template_id',
        'audience_id',
        'schedule_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'template_id' => 'integer',
        'audience_id' => 'integer',
        'schedule_id' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }

    public function audience()
    {
        return $this->belongsTo(MessageAudience::class, 'audience_id');
    }

    public function schedule()
    {
        return $this->belongsTo(MessageSchedule::class, 'schedule_id');
    }
}
