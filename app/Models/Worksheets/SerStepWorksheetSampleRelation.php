<?php

namespace App\Models\Worksheets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Models\Equipments\Equipment;
use App\Models\SerWorksheetStep;

class SerStepWorksheetSampleRelation extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $guarded = ['id'];

    public function header()
    {
        return $this->belongsTo(SerHeaderWorksheetSampleRelation::class, 'ser_header_id');
    }

    public function parentStep()
    {
        return $this->belongsTo(SerWorksheetStep::class, 'ser_worksheet_step_id');
    }

    public function measurand()
    {
        // Assuming measurand refers to AnalysisElements or similar based on user description "pick the paramentes ie the analyte code"
        // But the user also said "already auto select the default configurations" from SerWorksheetStep which has default_measurand_ids
        return $this->belongsTo(\App\AnalysisElements::class, 'measurand_id');
    }

    public function equipment()
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function analyst()
    {
        return $this->belongsTo(User::class, 'analyst_id');
    }
}
