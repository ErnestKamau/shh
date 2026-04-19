<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\TicketPermission;
use App\Models\System\SystemConfiguration;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements Auditable
{
    use \OwenIt\Auditing\Auditable;

	use Notifiable, HasFactory;
	use HasRoles {
		hasRole as private spatieHasRole;
		hasPermissionTo as private spatieHasPermissionTo;
	}

	protected string $guard_name = 'web';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password','veriify_code','verify_code_expires'
	];
	protected $appends = ['labsectionname','labsectionids'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	protected $casts = [
		'email_verified_at' => 'datetime',
	];

	protected function getLabSectionNameAttribute(){
		return implode(', ',SampleAnalysisStage::whereIn('id',explode(',',$this->lab_section_id))->pluck('name')->toArray()) ?? '';
	}
	protected function getLabSectionIdsAttribute(){
		return SampleAnalysisStage::whereIn('id',explode(',',$this->lab_section_id))->pluck('id')->toArray() ?? [];
	}

	public function audit_logs()
	{
		return \OwenIt\Auditing\Models\Audit::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();
	}

	/**
	 * Get the user associated with the User
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasOne
	 */
	public function positionGR()
	{
		return $this->hasOne(ModulePreConfigs::class, 'id', 'position');
	}
	
	public function position_name()
	{
		return ModulePreConfigs::find($this->position)->name ?? 'n/a';
	}
	
	public function available_license(){
		$company = \App\Company::find(\Auth::user()->company_id);
		$license = $company->license_key;


		$ivLen = \substr($license, -2, 1);
		$cipher = 'AES-256-CBC';

		$ivLen = intval(textBetween($license, '$/', '/$'));
		$license = substr_replace($license, '', -(4+strlen($ivLen)+1), (4+strlen($ivLen)));
		$ivStr = \substr($license, -($ivLen+1), $ivLen);

		$license = substr_replace($license, '', -($ivLen+1), ($ivLen));

		$iv = \base64_decode($ivStr);

		$key = strrev(\substr($license, -26, 24));

		$license = substr_replace($license, '', -26, 24);

		$licenseArr = \openssl_decrypt($license, $cipher, $key, $options=0, $iv);

		Log::info($licenseArr);

		return json_decode($licenseArr, true);
	}

	public function work_history(){
		$history = \App\PersonnelWorkHistory::join('users as u', 'u.id', '=', 'personnel_work_histories.user_id')
			->join('inventory_departments as d', 'd.id', '=', 'personnel_work_histories.department_id')
			->leftJoin('module_pre_configs as p', function($join){
				$tp = "Job Description";
				$join->on('p.id', '=', 'personnel_work_histories.job_id');
				$join->where('p.type', '=', $tp);
			})
			->selectRaw('d.name as department_name, p.name as position, personnel_work_histories.created_at, personnel_work_histories.end_date')
			->where('personnel_work_histories.user_id', $this->id)->orderBy('personnel_work_histories.created_at', 'desc')->get();

		return $history;
	}

	public function hasRole($role_id, $isAnID=false){
		try {
			$roleName = null;

			if (is_object($role_id)) {
				if (isset($role_id->name) && is_string($role_id->name)) {
					$roleName = trim($role_id->name);
				} elseif (isset($role_id->id) && is_numeric($role_id->id)) {
					$roleName = SpatieRole::query()
						->where('guard_name', $this->guard_name)
						->where('id', (int) $role_id->id)
						->value('name');
				}
			} elseif ($isAnID === true || is_numeric($role_id)) {
				$roleName = SpatieRole::query()
					->where('guard_name', $this->guard_name)
					->where('id', (int) $role_id)
					->value('name');
			} elseif (is_string($role_id)) {
				$roleName = trim($role_id);
			}

			if (!is_string($roleName) || $roleName === '') {
				return false;
			}

			$canonicalRoleName = SpatieRole::query()
				->where('guard_name', $this->guard_name)
				->whereRaw('LOWER(name) = ?', [strtolower($roleName)])
				->value('name');

			if (is_string($canonicalRoleName) && $canonicalRoleName !== '') {
				$roleName = $canonicalRoleName;
			}

			return $this->spatieHasRole($roleName, $this->guard_name);
		} catch (\Throwable $exception) {
			return false;
		}
	}

	public function isSystemAdmin(): bool
	{
		try {
			$adminRoleNames = ['admin', 'super admin', 'super-admin', 'system admin', 'system-admin'];
			$userRoleNames = $this->roles
				->pluck('name')
				->filter(fn ($name) => is_string($name) && $name !== '')
				->map(fn ($name) => strtolower(trim($name)))
				->toArray();

			foreach ($adminRoleNames as $adminRoleName) {
				if (in_array($adminRoleName, $userRoleNames, true)) {
					return true;
				}
			}

			return $this->hasLegacyAdminRole();
		} catch (\Throwable $exception) {
			return $this->hasLegacyAdminRole();
		}
	}

	public function hasLegacyAdminRole(): bool
	{
		try {
			$adminRoleNames = ['admin', 'super admin', 'super-admin', 'system admin', 'system-admin'];

			return DB::table('user_roles')
				->join('roles', 'roles.id', '=', 'user_roles.role_id')
				->where('user_roles.user_id', $this->id)
				->whereIn(DB::raw('LOWER(roles.name)'), $adminRoleNames)
				->exists();
		} catch (\Throwable $exception) {

			return false;
		}
	}

	/**
	 * Get a flat array of all permission names for the user.
	 * Includes wildcard '*' if the user is a system admin.
	 *
	 * @return array<string>
	 */
	public function getFlatPermissions(): array
	{
		if ($this->isSystemAdmin()) {
			return ['*'];
		}

		try {
			// Ensure we are using names, and include a baseline General.View
			$permissions = $this->getAllPermissions()->pluck('name')->toArray();

			if (!in_array('General.View', $permissions)) {
				$permissions[] = 'General.View';
			}

			return $permissions;
		} catch (\Throwable $e) {
			Log::error('getFlatPermissions failed', ['error' => $e->getMessage()]);
			return ['General.View'];
		}
	}

	public function department(){
		return InventoryDepartment::find($this->department_id);
	}

	public function location(){
		return InventoryLocation::find($this->location_id);
	}

	public function generateTwoFactorCode(){
		$this->timestamps = false;
		$this->verify_code = rand(100000,999999);
		$this->verify_code_expires = now()->addMinutes(value(30));
		$this->save();
	}
	public function resetTwoFactor(){
		$this->timestamps = false;
		$this->verify_code = null;
		$this->verify_code_expires = null;
		$this->save();
	}
	public function check_permission($role){
		if (!is_array($role) || count($role) < 2) {
			return false;
		}

		$permissionName = implode('.', $role);

		try {
			return $this->spatieHasPermissionTo($permissionName, $this->guard_name);
		} catch (\Throwable $exception) {
			return false;
		}
	}
	public function checkApproveLabSampleRole(){
		$approve_role_id =SystemConfiguration::where('key','approve_lab_sample_role_id')->first();
		if(isset($approve_role_id->id)){
			if($this->hasRole((int) $approve_role_id->value, true)){
				return true;
			}
			return false;
		}
		return false;
	}
	public function checkVerifyLabSampleRole(){
		$approve_role_id =SystemConfiguration::where('key','verify_lab_samples_role_id')->first();
		if(isset($approve_role_id->id)){
			if($this->hasRole((int) $approve_role_id->value, true)){
				return true;
			}
			return false;
		}
		return false;
	}
	public function checkApproveMethodsRole(){
		try {
			$roleId = SpatieRole::query()
				->where('guard_name', $this->guard_name)
				->whereRaw('LOWER(name) = ?', ['can approvemethods'])
				->value('id');

			if ($roleId) {
				return $this->hasRole((int) $roleId, true);
			}
		} catch (\Throwable $exception) {
			return false;
		}

		return false;
	}
	public function CheckViewQcSample(){
		$view_qc = SystemConfiguration::where('key','can_view_qc')->first();
		if(isset($view_qc->id)){
			if($this->hasRole((int) $view_qc->value, true)){
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
	public function CheckDeactivatePersonnel(){
		$deactivate_config = SystemConfiguration::where('key','can_deactivate_personnel')->first();
		if(isset($deactivate_config->id)){
			if($this->hasRole((int) $deactivate_config->value, true)){
				return true;
			}else{
				return false;
			}
		}
		return false;
	}

	/**
	 * @return HasOne<TicketPermission, $this>
	 */
	public function ticketPermission(): HasOne
	{
		return $this->hasOne(TicketPermission::class);
	}

	/**
	 * Whether the user has a granular help-desk permission stored in ticket_permissions.permissions (JSON).
	 * When no row exists, view_tickets defaults to true so existing support staff retain full visibility until configured.
	 */
	public function hasTicketPermission(string $permission): bool
	{
		$record = $this->ticketPermission;

		if ($record === null) {
			return $permission === 'view_tickets';
		}

		$permissions = $record->permissions;

		if (! is_array($permissions)) {
			return false;
		}

		if (array_is_list($permissions)) {
			return in_array($permission, $permissions, true);
		}

		$value = $permissions[$permission] ?? false;

		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	/**
	 * Deactivate portal (CRM client) users when portal access is removed from a contact.
	 *
	 * Matches rows linked by {@see User::$crm_contact_id} or {@see User::$crmcontact_id}, and legacy
	 * rows scoped by email + client when FK columns were not set.
	 */
	public static function deactivatePortalUsersForCustomerContact(
		CustomerContact $contact,
		int $crmCustomerId,
		?string $previousContactEmail = null
	): void {
		$emails = array_values(array_unique(array_filter([
			$contact->email,
			$previousContactEmail,
		])));

		self::query()
			->where('is_client', 1)
			->where('client_id', $crmCustomerId)
			->where(function ($query) use ($contact, $emails): void {
				$query->where('crm_contact_id', $contact->id)
					->orWhere('crmcontact_id', $contact->id);
				if ($emails !== []) {
					$query->orWhereIn('email', $emails);
				}
			})
			->update(['active' => 0]);
	}
}
