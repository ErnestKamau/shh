<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;

class AiManualDocument extends Model
{
    protected $connection = 'pgsql_ai';

    protected $table = 'ai.ai_manual_documents';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'expires_at' => 'datetime',
        'last_indexed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}