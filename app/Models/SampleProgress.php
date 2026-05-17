<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SampleProgress extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];
    
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'current_day_date' => 'date',
    ];
    
    public function sampleDetail()
    {
        return $this->belongsTo(\App\SampleDetails::class, 'sample_detail_id');
    }
    
    public function stageHeader()
    {
        return $this->belongsTo(StageHeader::class, 'stage_header_id');
    }
    
    public function testStage()
    {
        return $this->belongsTo(TestStage::class, 'test_stage_id');
    }
    
    public function analyte()
    {
        return $this->belongsTo(\App\Analyte::class, 'analyte_id');
    }
    
    public function method()
    {
        return $this->belongsTo(\App\AnalysisMethod::class, 'method_id');
    }
    
    public function scopeForToday($query)
    {
        return $query->where('current_day_date', today())->where('status', 'in_progress');
    }
    
    public function scopePending($query)
    {
        return $query->where('status', 'in_progress')->where('current_day_date', '<=', now());
    }

    public function startTest()
    {
        $this->update([
            'status' => 'in_progress',
            'start_date' => now(),
            'current_day_date' => now(),
        ]);
        
        return $this;
    }

    public function cancelTest()
    {
        $this->update([
            'status' => 'cancelled',
            'end_date' => now(),
            'remarks' => 'Test cancelled by user',
        ]);
        
        return $this;
    }

    public function getSample()
    {
        return $this->belongsTo(\App\SampleDetails::class, 'sample_detail_id');
    }

    public function getCurrentStage()
    {
        return $this->belongsTo(TestStage::class, 'test_stage_id');
    }

    public function getAnalyte()
    {
        return $this->belongsTo(\App\Analyte::class, 'analyte_id');
    }

    public function getMethod()
    {
        return $this->belongsTo(\App\AnalysisMethod::class, 'method_id');
    }

    public function sampleProgress()
    {
        return $this->hasMany(\App\Models\SampleProgress::class, 'sample_detail_id');
    }

    public function activeProgress()
    {
        return $this->hasMany(\App\Models\SampleProgress::class, 'sample_detail_id')
                    ->whereIn('status', ['in_progress', 'not_started']);
    }

}   