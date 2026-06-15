<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class EventMessageMapping extends Model
{
    protected $table = 'event_message_mappings';

    protected $fillable = [
        'tenant_id',
        'event_code',
        'template_id',
        'active',
    ];

    protected $casts = [
        'template_id' => 'integer',
        'active' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(MessageTemplate::class, 'template_id');
    }
}
