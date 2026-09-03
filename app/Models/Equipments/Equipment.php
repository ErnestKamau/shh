<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Carbon\Carbon;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class Equipment extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'equipment';

	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'name',
		'equipment_number',
		'thermometer_id',
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
		'preventive_maintainance_period',
		'preventive_maintainance_notification_days',
		'asset_type_id',
		'asset_location_id',
		'lab_id',
		'active',
		'picture',
		'company_id',
		'employee_dispose_id',
		'dispose_date',
		'comment',
		'is_disposal',
		'requires_daily_log',
		'has_logbook_tracking',
		'daily_log_value_types',
		'daily_log_value_type',
		'daily_log_nature',
		'daily_log_tolerance',
		'daily_log_expected_value',
		'daily_log_expected_min',
		'daily_log_expected_max',
		'daily_log_reporting_unit',
		'daily_log_frequency',
		'daily_log_monitored_by_another_equipment',
		'daily_log_monitored_equipment_id',
		'purchase_price',
		'installation_date',
		'commissioning_date',
		'detection_limit',
		'tolerance_limit',
		'supplier_name',
		'warranty',
		'environment',
		'end_of_life',
		'end_of_service',
	];

	protected $casts = [
		'requires_daily_log' => 'boolean',
		'has_logbook_tracking' => 'boolean',
		'daily_log_value_types' => 'array',
		'daily_log_tolerance' => 'integer',
		'daily_log_expected_min' => 'float',
		'daily_log_expected_max' => 'float',
		'daily_log_frequency' => 'integer',
		'daily_log_monitored_by_another_equipment' => 'boolean',
		'installation_date' => 'date',
		'commissioning_date' => 'date',
		'end_of_life' => 'date',
		'end_of_service' => 'date',
		'purchase_price' => 'decimal:2',
	];

	public static function defaultPicturePath(): string
	{
		return '/images/lab-item.png';
	}

	public static function isValidPicturePath(?string $value): bool
	{
		$value = trim((string) ($value ?? ''));

		if ($value === '') {
			return false;
		}

		if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
			return true;
		}

		if (str_starts_with($value, '/storage/') || str_starts_with($value, '/images/')) {
			return file_exists(public_path($value));
		}

		return (bool) preg_match('/\.(jpe?g|png|gif|webp|svg)$/i', $value);
	}

	public static function sanitizePictureValue(?string $value): string
	{
		return self::isValidPicturePath($value)
			? trim((string) $value)
			: self::defaultPicturePath();
	}

	public function hasValidPicture(): bool
	{
		return self::isValidPicturePath($this->picture);
	}

	public function pictureUrl(): string
	{
		return self::sanitizePictureValue($this->picture);
	}

	/**
	 * First configured value type, used as the primary daily-log definition.
	 *
	 * @return array<string, mixed>|null
	 */
	public function primaryDailyLogValueType(): ?array
	{
		$types = $this->daily_log_value_types;

		if (! is_array($types) || $types === []) {
			return null;
		}

		return $types[0];
	}

	public function getDailyLogValueTypeAttribute($value)
	{
		return filled($value) ? $value : ($this->primaryDailyLogValueType()['value_type'] ?? null);
	}

	public function getDailyLogNatureAttribute($value)
	{
		return filled($value) ? $value : ($this->primaryDailyLogValueType()['nature'] ?? null);
	}

	public function getDailyLogExpectedValueAttribute($value)
	{
		return filled($value) ? $value : ($this->primaryDailyLogValueType()['expected_value'] ?? null);
	}

	public function getDailyLogExpectedMinAttribute($value)
	{
		if ($value !== null && $value !== '') {
			return $value;
		}

		$min = $this->primaryDailyLogValueType()['expected_min'] ?? null;

		return $min === null || $min === '' ? null : $min;
	}

	public function getDailyLogExpectedMaxAttribute($value)
	{
		if ($value !== null && $value !== '') {
			return $value;
		}

		$max = $this->primaryDailyLogValueType()['expected_max'] ?? null;

		return $max === null || $max === '' ? null : $max;
	}

	public function getDailyLogReportingUnitAttribute($value)
	{
		return filled($value) ? $value : ($this->primaryDailyLogValueType()['reporting_unit'] ?? null);
	}

	public function getDailyLogToleranceAttribute($value)
	{
		if ($value !== null && $value !== '') {
			return $value;
		}

		$tolerance = $this->primaryDailyLogValueType()['tolerance'] ?? null;

		return $tolerance === null || $tolerance === '' ? null : $tolerance;
	}

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

		// Calculate days remaining (positive if date is in future, negative if past)
		$diff = $now->diffInDays($date, false);

		$result['remaining_days'] = $diff;

		// Status logic: 
		// - badge-danger if past due (diff < 0)
		// - badge-warning if within notification period (diff <= notification_days and diff > 0)
		// - badge-success otherwise
		if ($diff < 0) {
			$result['status'] = 'badge-danger';
		} elseif ($this->calibration_notification_in_days && $diff <= $this->calibration_notification_in_days) {
			$result['status'] = 'badge-warning';
		} else {
			$result['status'] = 'badge-success';
		}

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

		// Calculate days remaining (positive if date is in future, negative if past)
		$diff = $now->diffInDays($date, false);

		$result['remaining_days'] = $diff;

		// Status logic: 
		// - badge-danger if past due (diff < 0)
		// - badge-warning if within notification period (diff <= notification_days and diff > 0)
		// - badge-success otherwise
		if ($diff < 0) {
			$result['status'] = 'badge-danger';
		} elseif ($this->maintainance_notification_in_days && $diff <= $this->maintainance_notification_in_days) {
			$result['status'] = 'badge-warning';
		} else {
			$result['status'] = 'badge-success';
		}

		return $result;
	}

  public function maintainance_Calibration_logs(){
    return $this->hasMany('\App\Models\Equipments\MaintainanceCalibrationLog');
	}

	public function latestCalibration()
	{
		return $this->hasOne(MaintainanceCalibrationLog::class, 'equipment_id')
			->whereIn('type', ['Calibration', 'calibration'])
			->orderByDesc('date')
			->orderByDesc('created_at');
	}

	public function latestMaintenance()
	{
		return $this->hasOne(MaintainanceCalibrationLog::class, 'equipment_id')
			->whereIn('type', ['Maintainance', 'maintainance', 'maintenance', 'Maintenance'])
			->orderByDesc('date')
			->orderByDesc('created_at');
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

	public function assetType(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Assets\AssetType::class, 'asset_type_id');
	}

	public function assetLocation(): BelongsTo
	{
		return $this->belongsTo(\App\Models\Assets\AssetLocation::class, 'asset_location_id');
	}

	public function assetTypeLabel(): string
	{
		$assetType = $this->relationLoaded('assetType')
			? $this->assetType
			: ($this->asset_type_id ? $this->assetType()->first() : null);

		if ($assetType) {
			$code = trim((string) ($assetType->asset_code ?? ''));
			$description = trim((string) ($assetType->descripton ?? ''));

			if ($code !== '' && $description !== '') {
				return $code.' ('.$description.')';
			}

			return $description !== '' ? $description : ($code !== '' ? $code : '-');
		}

		$fallback = trim((string) ($this->asset_description ?? $this->asset_code ?? ''));

		return $fallback !== '' ? $fallback : '-';
	}

	public function logbookColumns(): \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(\App\Models\Equipments\Logbook\EquipmentLogbookColumn::class, 'equipment_id')->orderBy('order');
	}

	public function logbookEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(\App\Models\Equipments\Logbook\EquipmentLogbookEntry::class, 'equipment_id');
	}

	public function depreciationConfig(): \Illuminate\Database\Eloquent\Relations\HasOne
	{
		return $this->hasOne(\App\Models\Equipments\Depreciation\EquipmentDepreciationConfig::class, 'equipment_id');
	}

	public function appraisals(): \Illuminate\Database\Eloquent\Relations\HasMany
	{
		return $this->hasMany(\App\Models\Equipments\Depreciation\EquipmentAppraisal::class, 'equipment_id');
	}

	public function lab(): BelongsTo
	{
		return $this->belongsTo(\App\Lab::class, 'lab_id');
	}

	/**
	 * @param  array<int, string>  $labIds
	 */
	public function scopeInLabs(Builder $query, array $labIds): Builder
	{
		if ($labIds === []) {
			return $query->whereRaw('1 = 0');
		}

		return $query->where(function (Builder $q) use ($labIds): void {
			$q->whereIn('lab_id', $labIds)
				->orWhereHas('assetLocation', fn (Builder $location) => $location->whereIn('lab_id', $labIds));
		});
	}

	/**
	 * @param  array<int, string>  $zoneIds
	 */
	public function scopeInZones(Builder $query, array $zoneIds): Builder
	{
		if ($zoneIds === []) {
			return $query->whereRaw('1 = 0');
		}

		return $query->where(function (Builder $q) use ($zoneIds): void {
			$q->whereHas('lab', fn (Builder $lab) => $lab->whereIn('zone_id', $zoneIds))
				->orWhereHas('assetLocation.lab', fn (Builder $lab) => $lab->whereIn('zone_id', $zoneIds));
		});
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

	/**
	 * Get accessories for this equipment.
	 */
	public function accessories()
	{
		return $this->hasMany(EquipmentAccessory::class, 'equipment_id');
	}

	/**
	 * Get spare parts for this equipment.
	 */
	public function spareParts()
	{
		return $this->hasMany(EquipmentSparePart::class, 'equipment_id');
	}

	/**
	 * Get annual maintenance records.
	 */
	public function annualMaintenances()
	{
		return $this->hasMany(EquipmentAnnualMaintenance::class, 'equipment_id');
	}

	/**
	 * Get preventive maintenance records.
	 */
	public function preventiveMaintenances()
	{
		return $this->hasMany(EquipmentPreventiveMaintenance::class, 'equipment_id');
	}

	/**
	 * Get maintenance register records.
	 */
	public function maintenanceRegisters()
	{
		return $this->hasMany(EquipmentMaintenanceRegister::class, 'equipment_id');
	}

	/**
	 * Get the zone name of this equipment.
	 */
	public function getZoneNameAttribute(): string
	{
		$location = $this->assetLocation;
		if ($location && $location->lab && $location->lab->zone) {
			return $location->lab->zone->name ?? '—';
		}
		return '—';
	}

	/**
	 * Get replacement plan items for this equipment.
	 */
	public function replacementPlanItems()
	{
		return $this->hasMany(EquipmentReplacementPlanItem::class, 'equipment_id');
	}
}
