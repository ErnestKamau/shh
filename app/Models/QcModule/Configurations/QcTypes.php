<?php

namespace App\Models\QcModule\Configurations;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcTypes extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

   
    protected $fillable = [];
    
    protected $table = 'qc_types';

   
    
    public function creator(){
        return User::find($this->created_by);
    }
   
}
