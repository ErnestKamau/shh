<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;
use Modules\QualityControl\Entities\Configurations\QcTypes as ConfigurationsQcTypes;

class Standards extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'standards';
    protected $fillables = ['is_active'];

    public function getQcType(){
        return ConfigurationsQcTypes::find($this->qc_type_id);
    }
    public function creator(){
        return User::find($this->edited_by);
    }
}
