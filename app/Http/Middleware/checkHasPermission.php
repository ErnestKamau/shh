<?php

namespace App\Http\Middleware;

use App\Services\Auth\LegacyPermissionSyncService;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\PermissionRegistrar;

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

		protected function syncLegacyRolesIfNeeded($user): void
		{
			if (! $user) {
				return;
			}

			try {
				$hasLegacyRoles = DB::table('user_roles')
					->where('user_id', $user->id)
					->exists();

				if (! $hasLegacyRoles) {
					return;
				}

				$hasSpatieRoles = $user->roles()->exists();

				if (! $hasSpatieRoles) {
					app(LegacyPermissionSyncService::class)->syncUser($user);
					app(PermissionRegistrar::class)->forgetCachedPermissions();
					$user->unsetRelation('roles');
					$user->load('roles.permissions');
				}
			} catch (\Throwable $exception) {
				Log::warning('Failed to auto-sync legacy roles during permission check.', [
					'user_id' => $user->id ?? null,
					'error' => $exception->getMessage(),
				]);
			}
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
			$user = Auth::user();

			if (! $user) {
				return redirect()->route('login');
			}

			$this->syncLegacyRolesIfNeeded($user);

			if (
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

			// Use Spatie permission check - works with HasRoles trait
			if($this->hasPermission($user, $permissionName)){
				return $next($request);
			}

			// Sync roles if first check failed, then retry
			$this->syncLegacyRolesIfNeeded($user);
			app(PermissionRegistrar::class)->forgetCachedPermissions();
			$user->unsetRelation('roles');
			$user->load('roles.permissions');

			if($this->hasPermission($user, $permissionName)){
				return $next($request);
			}

			return redirect()->back()->with('error','You have no permission to perform the designated task!');
	}

	/**
	 * Check if user has the given permission using Spatie/Laravel-Permission.
	 * Works around the User model's private hasPermissionTo alias.
	 */
	private function hasPermission($user, $permissionName)
	{
		try {
			// Check against all roles' permissions
			foreach($user->roles as $role) {
				if($role->hasPermissionTo($permissionName)) {
					return true;
				}
			}
			
			// Also check direct user permissions
			return \Spatie\Permission\Models\Permission::where('name', $permissionName)
				->whereIn('id', DB::table('spatie_model_has_permissions')
					->where('model_id', $user->id)
					->pluck('permission_id'))
				->exists();
		} catch(\Throwable $e) {
			Log::warning('Permission check failed', [
				'user_id' => $user->id,
				'permission' => $permissionName,
				'error' => $e->getMessage()
			]);
			return false;
		}
	}
}