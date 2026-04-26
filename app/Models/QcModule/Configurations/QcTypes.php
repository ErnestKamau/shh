<?php

namespace App\Models\QcModule\Configurations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QcTypes extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

   
    protected $fillable = [];
    
    protected $table = 'qc_types';

   
    
    public function creator(){
        return User::find($this->created_by);
    }
   
}
