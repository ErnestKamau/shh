<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Analyte;

class StandardAnalytes extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'standards_analytes';
    protected $appends = ['analytename'];
    public $with = ['standard_values'];

    public function getAnalyte(){
        return Analyte::find($this->analyte_id);
    }
    public function getAnalyteNameAttribute(){
        return Analyte::find($this->analyte_id)->name ?? '';
    }

    public function standard_value(){
        return $this->hasOne(StandardValue::class);
    }
}
