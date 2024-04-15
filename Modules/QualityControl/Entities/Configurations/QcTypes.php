<?php

namespace Modules\QualityControl\Entities\Configurations;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcTypes extends Model
{
   
    protected $fillable = [];
    
    protected $table = 'qc_types';

   
    
    public function creator(){
        return User::find($this->created_by);
    }
   
}
