<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Result extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    //

    protected $fillable = ['lab_section_id','remark_is_manual','sample_detail_code','sample_header_id','sample_detail_id','result','remarks','captured_result_id','is_pesticide','analyte_status_contracted','analyte_accredited','unit_code','guide','seond_guide','reporting_symbol'];

    public function captured(){
        return $this->belongsTo(CapturedResult::class,'captured_result_id');
    }
}
