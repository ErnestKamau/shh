<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class TenantMessageMapping extends Model
{
    protected $table = 'tenant_message_mappings';

    protected $fillable = [
        'tenant_id',
        'event_code',
        'provider_template_id',
        'provider_template_name',
        'language',
        'wa_number',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}