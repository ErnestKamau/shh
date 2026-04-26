<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class LabSectionApproverRelationShip extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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
