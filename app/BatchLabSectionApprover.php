<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BatchLabSectionApprover extends Model
{
    protected $table = "batch_labsection_approval";

    protected $appends = ['approvername','labsectionnames','approvertype', 'approvershortname'];

    public function getApproverNameAttribute(){
        return User::find($this->user_id)->name ?? '-';
    }
    public function getLabSectionNamesAttribute(){
        $stages = SampleAnalysisStage::whereIn('id',explode(',',$this->lab_section_ids))->get();
        return implode(', ',$stages->pluck('namecode')->toArray());
    }
    public function getApproverDetails(){
        return User::find($this->user_id);
    }
    public function getApproverPositionDetails(){
        $user = User::find($this->user_id);
        $position = ModulePreConfigs::find($user->position);
        return isset($position->id) ? $position->name : '-';
    }
    public function getApproverShortNameAttribute(){
        $user = User::find($this->user_id);
        $fname = isset($user->first_name) &&  $user->first_name ? $user->first_name[0]. '. ' : '';
        $lname =  isset($user->last_name) &&  $user->last_name ? $user->last_name : isset($user->middle_name) &&  $user->middle_name ? $user->middle_name : '';
        return $fname . $lname;
    }
    public function getApproverTypeAttribute(){
        $type = '';
        $type = $this->is_prelim == 1 ? 'PRELIM' : $type;
        $type = $this->is_prelim == 2 ? 'DRAFT' : $type;
        return $type;
    }
}
