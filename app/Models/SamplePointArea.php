<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SamplePointArea extends Model
{
    use SoftDeletes;
    
    protected $table = 'sample_point_area';
    
    protected $fillable = [
        'name',
        'code',
        'description',
        'crm_customer_id',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
        'deleted_at' => 'datetime',
    ];

    public function crmCustomer()
    {
        return $this->belongsTo('App\Models\CRM\CRMCustomer', 'crm_customer_id');
    }

    public function samplePoints()
    {
        return $this->hasMany('App\Models\CRM\SamplePoint', 'sample_point_area_id');
    }
} 