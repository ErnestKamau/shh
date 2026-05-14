<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class InterLabLog extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = "sample_interlab_log";
    protected $fillable = ['sample_id','to_lab_section_id','from_lab_section_id','quantity','submited_by','expected_date','status','date_submitted','date_received','received_by'];
    
    public function sample()
    {
        return $this->belongsTo('App\SampleDetails', 'sample_id');
    }
    
    public function from_lab()
    {
        return $this->belongsTo('App\Lab', 'from_lab_section_id');
    }
    
    public function to_lab()
    {
        return $this->belongsTo('App\Lab', 'to_lab_section_id');
    }
    
    public function submitter()
    {
        return $this->belongsTo('App\User', 'submited_by');
    }
}

