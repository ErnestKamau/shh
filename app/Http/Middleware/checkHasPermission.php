<?php

namespace App\Http\Middleware;

use App\User;
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

			if (! $user instanceof User) {
				return redirect()->route('login');
			}

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

			app(LegacyPermissionSyncService::class)->syncUser($user);
			app(PermissionRegistrar::class)->forgetCachedPermissions();
			$user->load('roles.permissions');

			if($user->can($permissionName)){
				return $next($request);
			}

			if ($this->hasLegacyPermission((int) $user->id, $permissionName)) {
				return $next($request);
			}

			return redirect()->back()->with('error','You have no permission to perform the designated task!');
	}

		protected function hasLegacyPermission(int $userId, string $permissionName): bool
		{
			$permissionsJson = DB::table('user_roles')
				->join('roles', 'roles.id', '=', 'user_roles.role_id')
				->where('user_roles.user_id', $userId)
				->whereNotNull('roles.permissions')
				->pluck('roles.permissions');

			foreach ($permissionsJson as $json) {
				$decoded = json_decode((string) $json, true);

				if (! is_array($decoded)) {
					continue;
				}

				if ($this->legacyPermissionMatches($decoded, $permissionName)) {
					return true;
				}
			}

			return false;
		}

		protected function legacyPermissionMatches(array $decodedPermissions, string $permissionName): bool
		{
			$segments = explode('.', $permissionName);
			$module = trim((string) ($segments[0] ?? ''));

			if ($module === '') {
				return false;
			}

			$modulePayload = $this->findArrayValueCaseInsensitive($decodedPermissions, $module);
			if (! is_array($modulePayload)) {
				return false;
			}

			$modulePermission = $this->findArrayValueCaseInsensitive($modulePayload, 'permission');
			if ($this->isTruthyPermissionValue($modulePermission)) {
				return true;
			}

			$cursor = $decodedPermissions;
			foreach ($segments as $segment) {
				$cursor = $this->findArrayValueCaseInsensitive((array) $cursor, (string) $segment);
				if ($cursor === null) {
					return false;
				}
			}

			return $this->isTruthyPermissionValue($cursor);
		}

		/**
		 * @param mixed $value
		 */
		protected function isTruthyPermissionValue($value): bool
		{
			if (is_bool($value)) {
				return $value;
			}

			if (is_int($value)) {
				return $value === 1;
			}

			if (is_string($value)) {
				$normalized = strtolower(trim($value));

				return in_array($normalized, ['1', 'true', 'yes', 'on'], true);
			}

			return false;
		}

		/**
		 * @return mixed
		 */
		protected function findArrayValueCaseInsensitive(array $array, string $key)
		{
			if (array_key_exists($key, $array)) {
				return $array[$key];
			}

			$needle = strtolower(trim($key));

			foreach ($array as $candidateKey => $candidateValue) {
				if (strtolower(trim((string) $candidateKey)) === $needle) {
					return $candidateValue;
				}
			}

			return null;
		}
}