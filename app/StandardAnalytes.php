<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\Analyte;

class StandardAnalytes extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'standards_analytes';
    protected $fillable = [
        'standard_id', 'analyte_id', 'standard_value_id', 'standard_value_type',
        'low', 'high', 'standard_is_value', 'comments', 'recommendations',
        'expected_value', 'absolute_tolerance', 'is_active', 'mean_value',
        'rel_std_dev', 'tolerance_1', 'tolerance_2', 'value_type',
        'matrix_operator', 'matrix_value'
    ];
    protected $appends = ['analytename'];

    public function getAnalyte(){
        return Analyte::find($this->analyte_id);
    }
    public function getAnalyteNameAttribute(){
        return Analyte::find($this->analyte_id)->name ?? '';
    }

    public function analyte(){
        return $this->belongsTo('App\Analyte');
    }

    public function standardValue(){
        return $this->belongsTo('App\StandardValue');
    }

    public function standard(){
        return $this->belongsTo('App\Standards');
    }
}
