<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class StageHeader extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];
    
    public function testStages()
    {
        return $this->hasMany(TestStage::class, 'stage_header_id')->orderBy('order'); 
    }
    
    public function method()
    {
        return $this->belongsTo(\App\AnalysisMethod::class, 'method_id');
    }
    
    public function analyte()
    {
        return $this->belongsTo(\App\Analyte::class, 'analyte_id');
    }
    
    public function sampleType()
    {
        return $this->belongsTo(\App\SampleType::class, 'sample_type_id');
    }
    
    public function sampleProgress()
    {
        return $this->hasMany(SampleProgress::class, 'stage_header_id');
    }
    
    public function getStageByDay($orderNumber)
    {
        return $this->testStages->where('order', $orderNumber)->first();
    }

    public function runs()
    {
        return $this->hasMany(StageHeaderRun::class, 'stage_header_id');
    }

    public function capturedResults()
    {
        return $this->hasMany(\App\CapturedResult::class, 'stage_header_id');
    }

    public function getCapturedResultsForBatch($batchId)
    {
        return $this->capturedResults()
            ->whereHas('sample', function($q) use ($batchId) {
                $q->where('sample_header_id', $batchId);
            })
            ->with('sample')
            ->get();
    }
}