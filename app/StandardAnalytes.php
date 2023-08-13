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

    public function getAnalyte(){
        return Analyte::find($this->analyte_id);
    }
    public function getAnalyteNameAttribute(){
        return Analyte::find($this->analyte_id)->name ?? '';
    }
    
}
