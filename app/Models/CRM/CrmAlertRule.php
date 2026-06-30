<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class CrmAlertRule extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'is_active' => 'boolean',
    ];
}
