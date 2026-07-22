<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\CapturedResult;

class SampleAnalysisTypeRelationView extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table="samples_to_analysis_relation_view";

    public function getCapturedResults(){
        return CapturedResult::with(['ltmethod', 'labSection'])
            ->where('sample_detail_id', $this->sample_detail_id)
            ->where('sample_header_id', $this->batch_id)
            ->where('captured_results.analysis_type_id', $this->analysis_type_id)
            ->join('analysis_elements as ae', function ($join) {
                $join->on('ae.analysis_type_id', '=', 'captured_results.analysis_type_id');
                $join->on('ae.analyte_id', '=', 'captured_results.analyte_id');
            })
            ->where('ae.active', 1)
            ->distinct('captured_results.id')
            ->selectRaw('captured_results.*')
            ->get();
    }
}
