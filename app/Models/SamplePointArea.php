<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SamplePointArea extends Model
{
    protected $table = 'sample_point_area';
    
    protected $fillable = [
        'name',
        'crm_customer_id'
    ];

    public function crmCustomer()
    {
        return $this->belongsTo('App\Models\CRM\CRMCustomer', 'crm_customer_id');
    }

    public function samplePoints()
    {
        return $this->hasMany('App\SamplePoint', 'sample_point_area_id');
    }
} 