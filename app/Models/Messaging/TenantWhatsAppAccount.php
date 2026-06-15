<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class TenantWhatsAppAccount extends Model
{
    protected $table = 'tenant_whatsapp_accounts';

    protected $fillable = [
        'tenant_id',
        'business_name',
        'phone_number',
        'phone_number_id',
        'waba_id',
        'business_id',
        'access_token',
        'webhook_verify_token',
        'status',
        'quality_rating',
        'messaging_limit',
        'active',
    ];

    protected $casts = [
        'access_token' => 'encrypted',
        'active' => 'boolean',
    ];
}
