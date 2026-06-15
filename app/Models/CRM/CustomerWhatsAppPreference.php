<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class CustomerWhatsAppPreference extends Model
{
    protected $table = 'customer_whatsapp_preferences';

    protected $fillable = [
        'customer_id',
        'tenant_id',
        'opted_in',
        'opted_in_at',
        'opted_out_at',
        'source',
    ];

    protected $casts = [
        'opted_in' => 'boolean',
        'opted_in_at' => 'datetime',
        'opted_out_at' => 'datetime',
    ];
}
