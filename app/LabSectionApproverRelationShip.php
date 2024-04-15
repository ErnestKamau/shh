<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LabSectionApproverRelationShip extends Model
{
    protected $table = "lab_section_approver_relation";
    protected $fillable = ['lab_section_id','user_id'];

    protected $appends = ['username','labsection'];
    public function getUSerNameAttribute(){
        return User::find($this->user_id)->name;
    }
    public function getLabSectionAttribute(){
        return SampleAnalysisStage::find($this->lab_section_id)->name ?? '';
    }
}
