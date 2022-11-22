<?php

namespace App;

use App\Models\System\SystemConfiguration;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Session;

class User extends Authenticatable
{
	use Notifiable;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name', 'email', 'password','veriify_code','verify_code_expires'
	];

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

	public function audit_logs()
	{
		return \OwenIt\Auditing\Models\Audit::where('user_id', $this->id)->orderBy('created_at', 'desc')->get();
	}

	public function roles(){
		return $this->hasMany('App\UserRole');
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
		$role = Role::join('user_roles as ur', 'ur.role_id', 'roles.id')->where('ur.user_id', $this->id);

		$role = $isAnID ? $role->where('roles.id', $role_id)->get() : $role->where('roles.name', $role_id)->get();
		// echo json_encode($role).$this->id;
		return $role->count() > 0;
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
		
		$permissions = Session::get('permissions');
		// return response()->json($permissions,200);
		$data1 = json_encode($permissions);
		$data = json_decode($data1,true);

		if(sizeof($role) > 2){

			if(isset($data[$role[0]][$role[1]][$role[2]][$role[3]])){
				if($data[$role[0]][$role[1]][$role[2]][$role[3]] == "true"){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}elseif(sizeof($role)==2){
			if(isset($data[$role[0]][$role[1]])){
				if($data[$role[0]][$role[1]] == "true"){
					return true;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
		// if ($permissions->$role == "true"){
		// 	return true;
		// }else{
		// 	return false;
		// }
	}
	public function checkApproveLabSampleRole(){
		$approve_role_id =SystemConfiguration::where('key','approve_lab_sample_role_id')->first();
		if(isset($approve_role_id->id)){
			if(isset(UserRole::where('user_id',$this->id)->where('role_id',$approve_role_id->value)->first()->id)){
				return True;
			}
			return False;
		}
		return False;
	}
	public function checkVerifyLabSampleRole(){
		$approve_role_id =SystemConfiguration::where('key','verify_lab_samples_role_id')->first();
		if(isset($approve_role_id->id)){
			if(isset(UserRole::where('user_id',$this->id)->where('role_id',$approve_role_id->value)->first()->id)){
				return True;
			}
			return False;
		}
		return False;
	}
}