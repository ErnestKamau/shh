<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabSectionApproverRelationShip extends Model
{
    protected $table = "lab_section_approver_relation";
    protected $fillable = ['lab_section_id','user_id'];

    protected $appends = ['username'];
    public function getUSerNameAttribute(){
        return User::find($this->user_id)->name;
    }
}
