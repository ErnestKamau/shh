<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class OutboundMessage extends Model
{
    protected $table = 'outbound_messages';

    protected $fillable = [
        'tenant_id',
        'event_code',
        'recipient',
        'provider_template_id',
        'payload_json',
        'status',
        'attempts',
        'provider_message_id',
        'provider_response_json',
        'error',
        'idempotency_key',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'provider_response_json' => 'array',
        'attempts' => 'integer',
    ];
}