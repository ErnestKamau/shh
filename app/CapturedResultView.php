<?php

namespace App;
use App\AnalysisMethod;

use Illuminate\Database\Eloquent\Model;

class CapturedResultView extends Model
{
    protected $table = "captured_results_view";
    protected $appends = ['standardRemarkValue','analyteMethods'];

    public function getStandardRemarkValueAttribute(){
        $remark = 'NS';
        $remark = $this->standard_value_code == 'IsValue' ? $this->standard_is_value : $remark;
        $remark = $this->standard_value_type == 'is_range' ? $this->low.'-'.$this->high : $remark;
        $remark = $this->standard_value_code != 'IsValue' && $this->standard_value_code != '' ? $this->standard_value_code : $remark;
        return $remark;
    }
    public function getAnalyteMethodsAttribute(){
        return AnalysisMethod::whereIn('id',explode(',',$this->analyte_method) ?? [])->get();
    }

}
