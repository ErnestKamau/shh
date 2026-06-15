<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class MessageAudience extends Model
{
    protected $table = 'message_audiences';

    protected $fillable = [
        'tenant_id',
        'name',
        'rules_json',
        'active',
    ];

    protected $casts = [
        'rules_json' => 'array',
        'active' => 'boolean',
    ];
}
