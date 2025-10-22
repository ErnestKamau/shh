<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class LabSectionApprover extends Model
{
    protected $table = "lab_section_approver_configuration";
    protected $appends = ['approvername','sectionarr','sectionname'];
    
    protected $fillable = [
        'user_id',
        'lab_section_ids',
        'title',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function getApproverNameAttribute(){
        return User::find($this->user_id)->name ?? 'N/A';
    }

    public function getSectionArrAttribute(){
        return explode(',',$this->lab_section_ids);
    }

    public function getSectionNameAttribute(){
        $data = SampleAnalysisStage::whereIn('id',explode(',',$this->lab_section_ids))->get();
        
        return implode(', ',$data->pluck('namecode')->toArray() ?? []);
    }
}
