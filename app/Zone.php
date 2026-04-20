<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'key',
        'value',
        'description',
        'module',
        'inventory_location_id',
    ];
}
