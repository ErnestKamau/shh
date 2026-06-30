<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class CrmDashboardWidget extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'ui_config' => 'array',
        'is_active' => 'boolean',
    ];
}
