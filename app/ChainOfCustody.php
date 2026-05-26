<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ChainOfCustody extends Model implements Auditable
{
	use HasUuids;
	use \OwenIt\Auditing\Auditable;

	public $incrementing = false;

	protected $keyType = 'string';

	public $with = ['started_by', 'completed_by', 'tracking_stage'];

	public function completed_by()
	{
		return $this->belongsTo('App\User', 'moved_out_by');
	}

	public function started_by()
	{
		return $this->belongsTo('App\User', 'moved_in_by');
	}

	public function tracking_stage()
	{
		return $this->belongsTo('App\SampleAnalysisStage', 'tracking_stage_id');
	}

	public function sampleHeader()
	{
		return $this->belongsTo('App\SampleHeader', 'sample_header_id');
	}
}
