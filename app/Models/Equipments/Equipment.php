<?php

namespace App\Models\Equipments;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Equipment extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'name',
		'equipment_number',
		'description',
		'make',
		'model',
		'serial_number',
		'barcode_number',
		'manufacturer',
		'status',
		'condition',
		'assigned_department',
		'assigned_employee_id',
		'warranty_date',
		'date_purchased',
		'maintainance_days',
		'maintainance_notification_in_days',
		'calibration_days',
		'calibration_notification_in_days',
		'asset_type_id',
		'asset_location_id',
		'active',
		'picture',
		'company_id',
		'employee_dispose_id',
		'dispose_date',
		'comment',
		'is_disposal',
	];

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

	/**
	 * Get the user who created this equipment
	 */
	public function creator()
	{
		return $this->belongsTo(\App\User::class, 'created_by');
	}

	/**
	 * Get the department this equipment is assigned to
	 */
	public function department()
	{
		return $this->belongsTo(\App\InventoryDepartment::class, 'assigned_department');
	}

	/**
	 * Get the employee this equipment is assigned to
	 */
	public function assignedEmployee()
	{
		return $this->belongsTo(\App\User::class, 'assigned_employee_id');
	}

	/**
	 * Get the employee who disposed this equipment
	 */
	public function disposingEmployee()
	{
		return $this->belongsTo(\App\User::class, 'employee_dispose_id');
	}

	/**
	 * Get all disposal requests for this equipment
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function disposals()
	{
		return $this->hasMany(EquipmentDisposal::class);
	}

	/**
	 * Get active disposal request (if any)
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function activeDisposal()
	{
		return $this->hasOne(EquipmentDisposal::class)
			->whereIn('status', ['draft', 'pending', 'approved', 'executed']);
	}

	/**
	 * Get all evaluations for this equipment
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function evaluations()
	{
		return $this->hasMany(EquipmentEvaluation::class);
	}

	/**
	 * Get the latest evaluation for this equipment
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function latestEvaluation()
	{
		return $this->hasOne(EquipmentEvaluation::class)->latest();
	}

	/**
	 * Check if equipment can be disposed (no active disposal requests)
	 *
	 * @return bool
	 */
	public function canBeDisposed(): bool
	{
		return !$this->activeDisposal()->exists();
	}

	/**
	 * Check if equipment requires evaluation before disposal
	 *
	 * @return bool
	 */
	public function requiresEvaluationForDisposal(): bool
	{
		$evaluation = $this->latestEvaluation;
		
		if (!$evaluation) {
			return true; // Requires evaluation if none exists
		}

		// Evaluation must be recent (within 6 months)
		return !$evaluation->isRecent();
	}

	/**
	 * Check if equipment is electronic (requires data wiping)
	 *
	 * @return bool
	 */
	public function isElectronic(): bool
	{
		// Check if equipment type/category indicates electronic equipment
		// This can be customized based on your asset type structure
		$electronicKeywords = ['computer', 'electronic', 'digital', 'server', 'laptop', 'pc', 'analyzer'];
		
		$name = strtolower($this->name ?? '');
		$description = strtolower($this->description ?? '');
		
		foreach ($electronicKeywords as $keyword) {
			if (str_contains($name, $keyword) || str_contains($description, $keyword)) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Get disposal history summary
	 *
	 * @return array
	 */
	public function getDisposalHistory(): array
	{
		return $this->disposals()
			->orderBy('created_at', 'desc')
			->get()
			->map(function ($disposal) {
				return [
					'id' => $disposal->id,
					'date' => $disposal->created_at->format('Y-m-d'),
					'status' => $disposal->status,
					'method' => $disposal->proposed_method,
					'requester' => $disposal->requester->name ?? '-',
				];
			})
			->toArray();
	}
}
