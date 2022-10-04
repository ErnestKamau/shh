<?php

namespace App\Models\Equipments;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Equipment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
  public function calibration_date(){
		$logs = $this->last_logs();
		$days = $this->calibration_days;

		if(!isset($logs['calibration']->type)){
			$result = array("date"=> Carbon::parse( $this->date_purchased )->addDays($days), "remaining_days"=>0);
		}
		else{
			$result = array("date"=> Carbon::parse( $logs['calibration']->date )->addDays($days), "remaining_days"=>0);
		}

		$date = Carbon::parse($result['date']);
		$now = Carbon::now();

		$diff = $date->diffInDays($now);

		$result['remaining_days'] = $diff;

		$result['status'] = $diff < 0 ? 'badge-danger' : ($this->calibration_notification_in_days > $diff ? 'badge-warning' : 'badge-success');

		return $result;
	}

  public function maintainance_date(){
		$logs = $this->last_logs();
		$days = $this->maintainance_days;

		if(!isset($logs['maintainance']->type)){
			$result = array("date"=> Carbon::parse( $this->date_purchased )->addDays($days), "remaining_days"=>0);
		}
		else{
			$result = array("date"=> Carbon::parse( $logs['maintainance']->date )->addDays($days), "remaining_days"=>0);
		}

		$date = Carbon::parse($result['date']);
		$now = Carbon::now();

		$diff = $date->diffInDays($now);

		$result['remaining_days'] = $diff;

		$result['status'] = $diff < 0 ? 'badge-danger' : ($this->maintainance_notification_in_days > $diff ? 'badge-warning' : 'badge-success');

		return $result;
	}

  public function maintainance_Calibration_logs(){
    return $this->hasMany('\App\Models\Equipments\MaintainanceCalibrationLog');
	}

	public function usage_logs(){
		return $this->hasMany('\App\Models\Equipments\EquipmentUsage');
	}

	public function operator_names(){
		return \App\Models\Equipments\EquipmentOperator::where('equipment_id', $this->id)
			->join('users as u', 'u.id', '=', 'equipment_operators.user_id')
			->where('u.active',1)
			->selectRaw('u.name, u.id')->get();
	}

	public function operators(){
		return \App\Models\Equipments\EquipmentOperator::where('equipment_id', $this->id)
			->join('users as u', 'u.id', '=', 'equipment_operators.user_id')
			->where('u.active',1)->get();
	}

  public function last_logs(){
		$mainT = 'maintainance';
		$caliL = 'calibration';

		$lastMaintainanceLog = \App\Models\Equipments\MaintainanceCalibrationLog::where('equipment_id', $this->id)
			->where('type', $mainT)->orderBy('date', 'desc')->first();

		$lastCalibrationLog = \App\Models\Equipments\MaintainanceCalibrationLog::where('equipment_id', $this->id)
			->where('type', $caliL)->orderBy('date', 'desc')->first();

		return array(
			'maintainance' => $lastMaintainanceLog,
			'calibration' => $lastCalibrationLog
		);
	}
}
