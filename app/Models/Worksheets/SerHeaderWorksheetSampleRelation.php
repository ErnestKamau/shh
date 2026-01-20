<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Model;
use App\Models\CRM\SamplePoint;
use App\User;
use App\Models\Equipments\Equipment;
use App\CapturedResult;
use App\AnalysisType;
use App\SampleDetails;

class SerHeaderWorksheetSampleRelation extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'analyst_ids' => 'array',
        'date_received' => 'date',
        'date_tested' => 'date',
        'start_time' => 'datetime',
    ];

    public function sample()
    {
        return $this->belongsTo(SampleDetails::class, 'sample_detail_id');
    }

    public function analysisType()
    {
        return $this->belongsTo(AnalysisType::class, 'analysis_type_id');
    }

    public function steps()
    {
        return $this->hasMany(SerStepWorksheetSampleRelation::class, 'ser_header_id');
    }

    public function testKits()
    {
        return $this->hasMany(SerTestkitWorksheetSampleRelation::class, 'ser_header_id');
    }

    public function method()
    {
        // Assuming method refers to AnalysisMethod or similar
        return $this->belongsTo(\App\AnalysisMethod::class, 'method_id');
    }
}
