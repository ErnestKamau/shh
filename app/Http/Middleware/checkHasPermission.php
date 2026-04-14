<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class checkHasPermission
{
		protected function hasLegacyAdminRole(int $userId): bool
		{
			$adminRoleNames = ['admin', 'super admin', 'super-admin', 'system admin', 'system-admin'];

			return DB::table('user_roles')
				->join('roles', 'roles.id', '=', 'user_roles.role_id')
				->where('user_roles.user_id', $userId)
				->whereIn(DB::raw('LOWER(roles.name)'), $adminRoleNames)
				->exists();
		}

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
		public function getValueFromKey($param,$k){
			$obj = [];
			foreach($param as $key => $value){
				$obj[$key] = $value;
			}

			return $obj[$k] ?? 'none';
		}

    public function handle($request, Closure $next,$permissions)
    {
			$user = auth()->user();

			if (
				$user &&
				(
					(method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) ||
					$this->hasLegacyAdminRole((int) $user->id)
				)
			) {
				return $next($request);
			}

			$perm = explode('.',$permissions);
			$permissionName = $permissions;

			if(sizeof($perm) > 2){
				$params = $request->route()->parameters();
				$altVar = $this->getValueFromKey($params, $perm[2]);

				Log::info(json_encode($params));

				$perms = permissionInModule($perm, $altVar);

				if($perms == false){
					return redirect()->back()->with('error','Access Denied!');
				}

				if($perm[2] == 'All Samples'){
					return $next($request);
				}

				$permissionName = implode('.', $perms);
			}

			if($user->can($permissionName)){
				return $next($request);
			}

			return redirect()->back()->with('error','You have no permission to perform the designated task!');
	}
}