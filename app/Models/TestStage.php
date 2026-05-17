<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TestStage extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['id'];
    
    protected $casts = [
        'media_required' => 'array',
        'equipment_required' => 'array',
        'controls_required' => 'array',
        'diluents_required' => 'array',
        'is_result_stage' => 'boolean',
        'end_if_pass' => 'boolean',
        'end_if_fail' => 'boolean',
        'is_end_stage' => 'boolean',
    ];

    // public function testStages()
    // {
    //     return $this->hasMany(TestStage::class, 'stage_header_id')->orderBy('order');
    // }
    
    public function stageHeader()
    {
        return $this->belongsTo(StageHeader::class, 'stage_header_id');
    }
    
    public function sampleProgress()
    {
        return $this->hasMany(SampleProgress::class, 'test_stage_id');
    }
    
    public function getNextStage($result)
    {
        $nextDay = $result === 'positive' ? $this->next_day_if_positive : $this->next_day_if_negative;
        
        if ($nextDay) {
            return $this->stageHeader->getStageByDay($nextDay);
        }
        
        return null;
    }
}