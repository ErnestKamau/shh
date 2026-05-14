<?php

namespace App\Models\Monitoring;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MonitoringVariable extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'variable_type',
        'value',
        'query_builder',
        'allowed_tables',
        'description',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'value' => 'array',
        'query_builder' => 'array',
        'allowed_tables' => 'array',
        'is_active' => 'boolean',
    ];
}
