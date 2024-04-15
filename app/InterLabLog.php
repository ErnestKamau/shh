<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class InterLabLog extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    protected $table = "sample_interlab_log";
    protected $fillable = ['sample_id','to_lab_section_id','from_lab_section_id','quantity','submited_by','expected_date','status','date_submitted','date_received','received_by'];
}
