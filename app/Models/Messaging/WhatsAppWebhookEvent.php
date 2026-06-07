<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class WhatsAppWebhookEvent extends Model
{
    protected $table = 'whatsapp_webhook_events';

    protected $fillable = [
        'tenant_id',
        'payload_json',
        'event_type',
        'processed',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'processed' => 'boolean',
    ];
}
