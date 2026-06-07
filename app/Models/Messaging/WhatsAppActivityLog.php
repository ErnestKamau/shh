<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class WhatsAppActivityLog extends Model
{
    protected $table = 'whatsapp_activity_logs';

    protected $fillable = [
        'tenant_id',
        'activity_type',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
