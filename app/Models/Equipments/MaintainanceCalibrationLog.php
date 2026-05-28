<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class MaintainanceCalibrationLog extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'equipment_id',
		'description',
		'service_provider',
		'notes',
		'correction_factor',
		'uncertainty_of_measure',
		'type',
		'date',
		'certificate',
		'overseen_by',
		'edit_by',
		'maintainance_notification_in_days',
		'calibration_notification_in_days',
		'replacement_date',
		'reference_number',
		'maintenance_type',
		'operator_id',
		'supplier_id',
		'employee_id',
		'maintainance_type',
		'cause',
		'start_time',
		'end_time',
		'remedy',
		'anomalies',
		'diagnosis',
		'operation_carried',
		'comments',
		'labour',
		'samaco_no',
		'barcode_no',
		'area_code',
		'stage',
		'operator_approve',
		'proccess_owner_approve',
	];

	protected $casts = [
		'date' => 'date',
		'correction_factor' => 'decimal:6',
		'uncertainty_of_measure' => 'decimal:6',
	];

	public function overseer(){
		return \App\User::find($this->overseen_by);
	}

	public function editor(){
		return \App\User::find($this->edited_by);
	}
}
