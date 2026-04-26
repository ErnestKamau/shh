<?php

namespace App\Models\QcModule\Configurations;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Approvers extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'qc_approvers_config';
    protected $appends = ['name','creator'];

    protected $guarded = ['id'];

    public function getNameAttribute(){
        return User::find($this->personnel_id)->name ?? '';
    }
    public function getCreatorAttribute(){
        return User::find($this->created_by)->name ?? '';
    }
}
