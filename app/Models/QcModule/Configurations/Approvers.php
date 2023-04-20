<?php

namespace App\Models\QcModule\Configurations;

use App\User;
use Illuminate\Database\Eloquent\Model;

class Approvers extends Model
{
    protected $table = 'qc_approvers_config';
    protected $appends = ['name','creator'];

    public function getNameAttribute(){
        return User::find($this->personnel_id)->name;
    }
    public function getCreatorAttribute(){
        return User::find($this->created_by)->name;
    }
}
