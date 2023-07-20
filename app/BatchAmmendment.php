<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class BatchAmmendment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $table = 'batch_ammendments';
    protected $appends = ['creator'];
    
    public function getCreatorAttribute(){
        return User::find($this->created_by_id)->name ?? '-';
    }
}
