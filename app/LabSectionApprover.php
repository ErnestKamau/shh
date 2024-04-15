<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class LabSectionApprover extends Model
{
    protected $table = "lab_section_approver_configuration";
    protected $appends = ['approvername','sectionarr','sectionname'];
    public function getApproverNameAttribute(){
        return User::find($this->user_id)->name;
    }

    public function getSectionArrAttribute(){
        return explode(',',$this->lab_section_ids);
    }

    public function getSectionNameAttribute(){
        $data = SampleAnalysisStage::whereIn('id',explode(',',$this->lab_section_ids))->get();
        
        return implode(', ',$data->pluck('namecode')->toArray() ?? []);
    }
}
