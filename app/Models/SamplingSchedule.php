<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class SamplingSchedule extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'sampling_schedules';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'crm_customer_id',
        'contact_id',
        'sampling_datetime',
        'location',
        'sample_type_id',
        'analysis_type_id',
        'parameters',
        'sample_details',
        'number_of_samples',
        'frequency',
        'notify_client',
        'personnel_id',
        'description',
        'company_id',
        'is_collected',
    ];

    protected $casts = [
        'sampling_datetime' => 'datetime',
        'notify_client' => 'boolean',
        'number_of_samples' => 'integer',
        'parameters' => 'array',
        'sample_details' => 'array',
        'is_collected' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\CRM\CRMCustomer::class, 'crm_customer_id');
    }

    public function contact()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerContact::class, 'contact_id');
    }

    public function sample_type()
    {
        return $this->belongsTo(\App\SampleType::class, 'sample_type_id');
    }

    public function analysis_type()
    {
        return $this->belongsTo(\App\AnalysisType::class, 'analysis_type_id');
    }

    public function personnel()
    {
        return $this->belongsTo(\App\User::class, 'personnel_id');
    }

    public function testRequestFormInstances()
    {
        return $this->hasMany(\App\Models\TestRequestFormInstance::class, 'sampling_schedule_id');
    }
}

