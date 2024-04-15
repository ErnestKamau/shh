<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class checkHasPermission
{
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
			$parameter = $request->route('status');
			$user = auth()->user();
			$perm = explode('.',$permissions);
			refreshPermissions(true);

			if(sizeof($perm) == 2){
				if($user->check_permission($perm)){
					return $next($request);
				}else{
					return redirect()->back()->with('error','You have no permission to perform the designated task!');
				}
			}
			else{
				$params = $request->route()->parameters();
				$altVar = $this->getValueFromKey($params, $perm[2]);

				Log::info(json_encode($params));

				$perms = permissionInModule($perm, $altVar);

				if($perms == false){
					return redirect()->back()->with('error','Access Denied!');
				}
			}

		if($perm[2] == 'All Samples'){
			return $next($request);
		}
		if($user->check_permission($perms)){
			return $next($request);
		}else{
			return redirect()->back()->with('error','You have no permission to perform the designated task!');
		}

	}
}