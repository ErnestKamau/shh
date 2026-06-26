<?php

namespace App;

use OwenIt\Auditing\Contracts\Auditable;

use App\Directorate;
use App\Lab;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\TicketPermission;
use App\Models\System\SystemConfiguration;
use App\UserDirectorateRelation;
use App\UserLabRelation;
use App\UserZoneRelation;
use App\Zone;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Auth\Role as SpatieRole;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Casts\SafeEncrypted;

class User extends Authenticatable implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

	use Notifiable, HasFactory, HasApiTokens;
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
		'name', 'first_name', 'middle_name', 'last_name', 'email', 'password', 'zone_id', 'verify_code', 'verify_code_expires',
		'company_id', 'department_id', 'location_id', 'active', 'position',
		'failed_login_attempts', 'login_locked_by_admin_reset', 'is_technical'
	];
	protected $appends = ['labsectionname','labsectionids'];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password', 'remember_token', 'phone', 'gender', 'designation', 'date_of_birth', 'id_number', 'first_name', 'middle_name', 'last_name', 'two_factor_secret', 'two_factor_recovery_codes',
	];

	/**
	 * The attributes that should be cast to native types.
	 *
	 * @var array
	 */
	const PASSWORD_EXPIRY_DAYS = 90;

	protected $casts = [
		'email_verified_at' => 'datetime',
		'password_changed_at' => 'datetime',
		'analyst_is_gazzetted' => 'boolean',
		'date_of_gazzette' => 'date',
		'gazzette_no' => 'string',
		'start_of_career' => 'datetime',
		'phone' => SafeEncrypted::class,
		'gender' => SafeEncrypted::class,
		'designation' => SafeEncrypted::class,
		'date_of_birth' => SafeEncrypted::class,
		'id_number' => SafeEncrypted::class,
		'first_name' => SafeEncrypted::class,
		'middle_name' => SafeEncrypted::class,
		'last_name' => SafeEncrypted::class,
		'two_factor_secret' => SafeEncrypted::class,
		'two_factor_recovery_codes' => SafeEncrypted::class,
		'client_id' => 'string',
		'crm_contact_id' => 'string',
		'crmcontact_id' => 'string',
		'failed_login_attempts' => 'integer',
		'login_locked_by_admin_reset' => 'boolean',
		'location_id' => 'string',
		'department_id' => 'string',
		'position' => 'string',
		'lab_section_id' => 'string',
        'is_technical' => 'boolean',
	];

    /** Returns days remaining until password expires. Null means never changed (expired immediately). */
    public function passwordDaysRemaining(): int
    {
        if ($this->password_changed_at === null) {
            return 0;
        }
        $expiry = $this->password_changed_at->copy()->addDays(self::PASSWORD_EXPIRY_DAYS);
        $remaining = (int) now()->diffInDays($expiry, false);
        return max(0, $remaining);
    }

    public function isPasswordExpired(): bool
    {
        return $this->passwordDaysRemaining() === 0;
    }

	protected function getLabSectionNameAttribute(){
		$ids = collect(explode(',', (string) $this->lab_section_id))
			->map(fn ($id) => trim($id))
			->filter()
			->values()
			->all();

		if ($ids === []) {
			return '';
		}

		$validIds = array_filter($ids, fn($id) => \Illuminate\Support\Str::isUuid($id));
		if (empty($validIds)) {
			return '';
		}

		return implode(', ', SampleAnalysisStage::whereIn('id', $validIds)->pluck('name')->toArray());
	}
	protected function getLabSectionIdsAttribute(){
		$ids = collect(explode(',', (string) $this->lab_section_id))
			->map(fn ($id) => trim($id))
			->filter()
			->values()
			->all();

		if ($ids === []) {
			return [];
		}

		$validIds = array_filter($ids, fn($id) => \Illuminate\Support\Str::isUuid($id));
		if (empty($validIds)) {
			return [];
		}

		return SampleAnalysisStage::whereIn('id', $validIds)->pluck('id')->toArray();
	}

	public function audit_logs()
	{
		return \App\Models\Audit::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();
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
                if (\is_iterable($role_id) || ($role_id instanceof \Illuminate\Support\Collection)) {
                        return $this->spatieHasRole($role_id, $this->guard_name);
                }
		try {
			$roleName = null;

			if (is_object($role_id)) {
				if (isset($role_id->name) && is_string($role_id->name)) {
					$roleName = trim($role_id->name);
				} elseif (isset($role_id->id)) {
					$roleName = SpatieRole::query()
						->where('guard_name', $this->guard_name)
						->where('id', (string) $role_id->id)
						->value('name');
				}
			} elseif ($isAnID === true || is_numeric($role_id)) {
				$roleName = SpatieRole::query()
					->where('guard_name', $this->guard_name)
					->where('id', (string) $role_id)
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

			return false;
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
			// Ensure we are using names, and include baseline general access keys.
			$permissions = $this->getAllPermissions()->pluck('name')->toArray();

			if (!in_array('general.view', $permissions, true)) {
				$permissions[] = 'general.view';
			}

			// Backward-compatibility for legacy AI required_permission values.
			if (!in_array('General.View', $permissions, true)) {
				$permissions[] = 'General.View';
			}

			return $permissions;
		} catch (\Throwable $e) {
			Log::error('getFlatPermissions failed', ['error' => $e->getMessage()]);
			return ['general.view', 'General.View'];
		}
	}

	public function department(){
		return InventoryDepartment::find($this->department_id);
	}

	public function location(){
		return InventoryLocation::find($this->location_id);
	}

	public function zone()
	{
		return $this->belongsTo(Zone::class, 'zone_id');
	}

	public function zoneRelation(): HasOne
	{
		return $this->hasOne(UserZoneRelation::class, 'user_id', 'id');
	}

	public function directorateRelation(): HasOne
	{
		return $this->hasOne(UserDirectorateRelation::class, 'user_id', 'id');
	}

	public function labRelation(): HasOne
	{
		return $this->hasOne(UserLabRelation::class, 'user_id', 'id');
	}

	public function assignedZones()
	{
		return $this->belongsToMany(Zone::class, 'user_zone_relation', 'user_id', 'zone_id')
			->withTimestamps();
	}

	public function assignedDirectorates()
	{
		return $this->belongsToMany(Directorate::class, 'user_directorate_relation', 'user_id', 'directorate_id')
			->withTimestamps();
	}

	public function assignedLabs()
	{
		return $this->belongsToMany(Lab::class, 'user_lab_relation', 'user_id', 'lab_id')
			->withTimestamps();
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

	/**
	 * Decrypt the given encrypted string, falling back to raw if invalid or decryption fails.
	 *
	 * @param  string  $value
	 * @return mixed
	 */
	public function fromEncryptedString($value)
	{
		try {
			return parent::fromEncryptedString($value);
		} catch (\Throwable $e) {
			return $value;
		}
	}
	public function checkApproveLabSampleRole(){
		return $this->hasRole('Can Approve Samples');
	}
	public function checkVerifyLabSampleRole(){
		return $this->hasRole('Can Verify Samples');
	}
	public function checkApproveMethodsRole(){
		try {
			$roleName = SpatieRole::query()
				->where('guard_name', $this->guard_name)
				->whereRaw('LOWER(name) = ?', ['can approvemethods'])
				->value('name');

			if (is_string($roleName) && $roleName !== '') {
				return $this->hasRole($roleName);
			}
		} catch (\Throwable $exception) {
			return false;
		}

		return false;
	}
	public function CheckViewQcSample(){
		return $this->hasRole('Can View Qc Samples');
	}
	public function CheckDeactivatePersonnel(){
		return $this->hasRole('Deactivate Personnel');
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
		string $crmCustomerId,
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

	public function check_permission($role)
	{
		// 1. Spatie Standard Permission & Admin Fallback
		try {
			if ($this->isSystemAdmin()) {
				return true;
			}
			$permissionString = is_array($role) ? implode('.', $role) : (string)$role;
			if ($this->spatieHasPermissionTo($permissionString)) {
				return true;
			}
		} catch (\Throwable $e) {
			// Ignore spatie exceptions if permission or guard doesn't exist
		}

		if (!is_array($role)) {
			return false;
		}

		// 2. Session-based Nested Permission Check (with Case-Insensitivity & implicit 'components' fallback)
		$permissions = Session::get('permissions');
		if (is_array($permissions)) {
			// Helper to get nested value case-insensitively
			$getNested = function ($array, array $keys) use (&$getNested) {
				$current = $array;
				foreach ($keys as $key) {
					if (!is_array($current)) {
						return null;
					}
					$found = false;
					foreach ($current as $k => $val) {
						if (strtolower((string)$k) === strtolower((string)$key)) {
							$current = $val;
							$found = true;
							break;
						}
					}
					if (!$found) {
						return null;
					}
				}
				return $current;
			};

			// Try direct case-insensitive lookup
			$val = $getNested($permissions, $role);
			if ($val !== null && strtolower(trim((string)$val)) === 'true') {
				return true;
			}

			// Try with implicit 'components' inserted after the category (e.g. ['crm', 'components', 'complaints', 'view'])
			if (count($role) >= 2) {
				$insertedRole = $role;
				array_splice($insertedRole, 1, 0, ['components']);
				$val = $getNested($permissions, $insertedRole);
				if ($val !== null && strtolower(trim((string)$val)) === 'true') {
					return true;
				}
			}

			// Try mapping new ISO-compliant section names to legacy names for backward compatibility (Aliases)
			// --- Permission Aliasing for Complaint Workflow Redesign ---
			$aliases = [
				'Open Complaint'                => ['Open Complaints', 'Open Complaint'],
				'Open Complaints'               => ['Open Complaint', 'Open Complaints'],

				'Complaint Investigation'       => ['Complaints Approval', 'Complaints Resolution', 'Complaint Resolution', 'Complaint Investigation'],
				'Complaints Approval'           => ['Complaint Investigation', 'Complaints Resolution', 'Complaint Resolution', 'Complaints Approval'],
				'Complaints Resolution'         => ['Complaint Investigation', 'Complaints Approval', 'Complaint Resolution', 'Complaints Resolution'],
				'Complaint Resolution'          => ['Complaint Investigation', 'Complaints Approval', 'Complaint Resolution', 'Complaint Resolution'],

				'Complaint Verification'        => ['Resolution Approval', 'Resolution Approval', 'Complaint Pending Closure', 'Complaint Verification'],
				'Complaint Pending Closure'     => ['Resolution Approval', 'Resolution Approval', 'Complaint Verification', 'Complaint Pending Closure'],
				'Resolution Approval'           => ['Complaint Verification', 'Complaint Pending Closure', 'Resolution Approval', 'Resolution Approval'],

				'Closed Complaint'              => ['Closed Complaints', 'Closed Complaint'],
				'Closed Complaints'             => ['Closed Complaint', 'Closed Complaints'],

				'Cancelled'                     => ['Cancelled Complaints', 'Cancelled'],
				'Cancelled Complaints'          => ['Cancelled', 'Cancelled Complaints'],
			];

			$category = $role[0] ?? null;
			$component = null;
			$action = null;

			if (count($role) === 3) {
				$component = $role[1];
				$action = $role[2];
			} elseif (count($role) >= 4) {
				$component = $role[2];
				$action = $role[3];
			}

			if ($category && strtolower($category) === 'crm' && $component && $action && isset($aliases[$component])) {
				$aliasTargets = (array) $aliases[$component];
				foreach ($aliasTargets as $target) {
					// Check target with components group
					$testRoleWithGroup = [$category, 'components', $target, $action];
					$val = $getNested($permissions, $testRoleWithGroup);
					if ($val !== null && strtolower(trim((string)$val)) === 'true') {
						Log::info("Permission matched on CRM alias target with group: " . $target);
						return true;
					}

					// Check target without components group
					$testRoleWithoutGroup = [$category, $target, $action];
					$val = $getNested($permissions, $testRoleWithoutGroup);
					if ($val !== null && strtolower(trim((string)$val)) === 'true') {
						Log::info("Permission matched on CRM alias target: " . $target);
						return true;
					}
				}
			}
		}

		return false;
	}

	public function getSignaturePath(): ?string
	{
		if (empty($this->electronic_sig)) {
			return null;
		}
		return getCoaApproverSignature($this->electronic_sig);
	}
}
