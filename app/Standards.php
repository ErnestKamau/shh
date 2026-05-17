<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;
use Modules\QualityControl\Entities\Configurations\QcSchemes;
use Modules\QualityControl\Entities\Configurations\QcTypes as ConfigurationsQcTypes;

class Standards extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'standards';
    protected $fillable = [
        'name', 'code', 'main_standard', 'is_qc_standard', 
        'qc_type_id', 'qc_scheme_ids', 'status', 'edited_by'
    ];
    protected $appends = ['qcschemeidsarr','qcschemenames'];

    public function getQcType(){
        return ConfigurationsQcTypes::find($this->qc_type_id);
    }
    public function creator(){
        return User::find($this->edited_by);
    }
    
    public function getQcSchemeIdsArrAttribute(){
        return explode(',',$this->qc_scheme_ids);
    }
    public function getQcSchemeNamesAttribute(){
        if (empty($this->qc_scheme_ids)) {
            return '';
        }
        $qcIds = array_filter(explode(',', $this->qc_scheme_ids));
        if (empty($qcIds)) {
            return '';
        }
        return implode(', ', QcSchemes::whereIn('id', $qcIds)->pluck('code')->toArray());
    }

    public function standardAnalytes(){
        return $this->hasMany('App\StandardAnalytes', 'standard_id');
    }
}
