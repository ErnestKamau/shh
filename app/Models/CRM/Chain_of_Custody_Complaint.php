<?php

namespace App\Models\CRM;

use App\User;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Chain_of_Custody_Complaint extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'chain_of_custody_complaints';

    protected $fillable = [
        'complaint_id',
        'action',
        'action_taker_id',
        'comments',
        'workflow_stage',
        'move_out_date',
    ];

    protected function casts(): array
    {
        return [
            'move_out_date' => 'datetime',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function actionTaker()
    {
        return $this->belongsTo(User::class, 'action_taker_id');
    }
}
