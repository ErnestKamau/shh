<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\SampleAnalysisStage;

class BatchLabSectionApprover extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $table = "batch_labsection_approval";

    protected $keyType = 'string';

    public $incrementing = false;

    protected $appends = ['approvername','labsectionnames','approvertype', 'approvershortname'];

    public function getApproverNameAttribute(){
        return User::find($this->user_id)->name ?? '-';
    }

    public function getLabSectionNamesAttribute(){
        $ids = array_filter(array_map('trim', explode(',', (string)$this->lab_section_ids)), fn($id) => \Illuminate\Support\Str::isUuid($id));
        if (empty($ids)) {
            return '';
        }
        $names = [];
        foreach ($ids as $id) {
            $lab = \App\Lab::find($id);
            if ($lab) {
                $names[] = $lab->name;
            } else {
                $stage = SampleAnalysisStage::find($id);
                if ($stage) {
                    $names[] = $stage->namecode ?? $stage->name;
                }
            }
        }
        return implode(', ', $names);
    }

    /**
     * Pseudo-relationship accessor exposing the lab sections collection.
     *
     * This is used by the approvals UI as `$approver->lab_sections`.
     */
    public function getLabSectionsAttribute()
    {
        if (!$this->lab_section_ids) {
            return collect();
        }

        $ids = array_filter(explode(',', $this->lab_section_ids));

        $items = collect();
        foreach ($ids as $id) {
            $lab = \App\Lab::find($id);
            if ($lab) {
                $items->push((object)[
                    'id' => $lab->id,
                    'name' => $lab->name,
                    'namecode' => $lab->code . ' - ' . $lab->name,
                ]);
            } else {
                $stage = SampleAnalysisStage::find($id);
                if ($stage) {
                    $items->push($stage);
                }
            }
        }
        return $items;
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
        $lname =  isset($user->last_name) &&  $user->last_name ? $user->last_name : (isset($user->middle_name) &&  $user->middle_name ? $user->middle_name : '');
        return $fname . $lname;
    }
    public function getApproverTypeAttribute(){
        $type = '';
        $type = $this->is_prelim == 1 ? 'PRELIM' : $type;
        $type = $this->is_prelim == 2 ? 'DRAFT' : $type;
        return $type;
    }
}
