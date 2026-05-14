<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\User;

class LabSectionApprover extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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
        $ids = array_filter(array_map('trim', explode(',', (string)$this->lab_section_ids)), fn($id) => \Illuminate\Support\Str::isUuid($id));
        if (empty($ids)) {
            return '';
        }
        $data = SampleAnalysisStage::whereIn('id', $ids)->get();
        return implode(', ', $data->pluck('namecode')->toArray() ?? []);
    }
}
