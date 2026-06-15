<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    protected $table = 'message_templates';

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'language',
        'header_type',
        'header_content',
        'body_content',
        'footer_content',
        'buttons_json',
        'variables_json',
        'meta_template_id',
        'status',
        'rejection_reason',
        'active',
    ];

    protected $casts = [
        'buttons_json' => 'array',
        'variables_json' => 'array',
        'active' => 'boolean',
    ];
}
